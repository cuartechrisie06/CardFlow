<?php

namespace App\Http\Controllers;

use App\Models\Card;
use App\Models\MarketplaceListing;
use App\Models\TradeRequest;
use App\Models\UserOnboarding;
use App\Models\UserCard;
use App\Services\ActivityLogger;
use App\Services\WishlistMatchService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class MarketplaceController extends Controller
{
    public function __construct(
        private WishlistMatchService $wishlistMatchService,
        private ActivityLogger $activityLogger
    ) {
    }

    public function __invoke(Request $request): View
    {
        $user = $request->user();
        UserOnboarding::query()->updateOrCreate(
            ['user_id' => $user->id],
            ['browsed_marketplace' => true],
        );

        $search = trim((string) $request->string('q'));
        $filter = (string) $request->string('filter', 'all');
        $artist = trim((string) $request->string('artist'));
        $rarity = trim((string) $request->string('rarity'));
        $type = (string) $request->string('type', 'all');
        $minPrice = $request->input('min_price');
        $maxPrice = $request->input('max_price');

        $listingsQuery = MarketplaceListing::query()
            ->with(['card', 'user', 'userCard'])
            ->when($filter === 'my_listings', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            }, function ($query) use ($user) {
                $query->activeVisible();
            })
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($nested) use ($search) {
                    $nested->whereHas('card', function ($cardQuery) use ($search) {
                        $cardQuery->where('title', 'like', "%{$search}%")
                            ->orWhere('artist', 'like', "%{$search}%")
                            ->orWhere('album', 'like', "%{$search}%")
                            ->orWhere('edition', 'like', "%{$search}%")
                            ->orWhere('rarity', 'like', "%{$search}%");
                    })->orWhereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('username', 'like', "%{$search}%");
                    });
                });
            })
            ->when($artist !== '', function ($query) use ($artist) {
                $query->whereHas('card', fn ($cardQuery) => $cardQuery->where('artist', 'like', "%{$artist}%"));
            })
            ->when($rarity !== '', function ($query) use ($rarity) {
                $query->whereHas('card', fn ($cardQuery) => $cardQuery->where('rarity', $rarity));
            })
            ->when($type === 'sale', function ($query) {
                $query->whereHas('userCard', fn ($userCardQuery) => $userCardQuery->where('is_for_sale', true));
            })
            ->when($type === 'trade', function ($query) {
                $query->whereHas('userCard', fn ($userCardQuery) => $userCardQuery->where('is_for_trade', true));
            })
            ->when(is_numeric($minPrice), function ($query) use ($minPrice) {
                $query->whereHas('userCard', fn ($userCardQuery) => $userCardQuery->where('listing_price', '>=', (float) $minPrice));
            })
            ->when(is_numeric($maxPrice), function ($query) use ($maxPrice) {
                $query->whereHas('userCard', fn ($userCardQuery) => $userCardQuery->where('listing_price', '<=', (float) $maxPrice));
            });

        $featuredListings = $listingsQuery->latest('updated_at')->paginate(4)->withQueryString();

        $wishlistPairs = $request->user()
            ? $request->user()
                ->wishlistItems()
                ->with('card:id,artist,album')
                ->get()
                ->map(fn ($item) => [
                    'artist' => trim((string) $item->card?->artist),
                    'album' => trim((string) ($item->card?->album ?? '')),
                ])
                ->filter(fn (array $pair) => $pair['artist'] !== '')
                ->unique(fn (array $pair) => mb_strtolower($pair['artist'] . '|' . $pair['album']))
                ->values()
            : collect();

        $wishlistMatchedListingIds = $featuredListings->getCollection()
            ->filter(function ($listing) use ($wishlistPairs) {
                $artist = trim((string) $listing->card?->artist);
                $album = trim((string) ($listing->card?->album ?? ''));

                return $wishlistPairs->contains(fn (array $pair) => (
                    mb_strtolower($pair['artist']) === mb_strtolower($artist)
                    && mb_strtolower($pair['album']) === mb_strtolower($album)
                ));
            })
            ->pluck('id')
            ->all();

        $publicListingsBase = MarketplaceListing::query()
            ->where('user_id', '!=', $user->id)
            ->activeVisible();

        $myActiveListingsBase = MarketplaceListing::query()
            ->where('user_id', $user->id)
            ->activeVisible();

        $marketMetrics = [
            'open_listings' => (clone $myActiveListingsBase)->count(),
            'open_trades' => (clone $myActiveListingsBase)->whereHas('userCard', fn ($query) => $query->where('is_for_trade', true))->count(),
            'sale_offers' => (clone $myActiveListingsBase)->whereHas('userCard', fn ($query) => $query->where('is_for_sale', true))->count(),
            'quick_actions' => (int) DB::table('users')
                ->join('marketplace_listings', 'users.id', '=', 'marketplace_listings.user_id')
                ->where('users.id', '!=', $user->id)
                ->where('marketplace_listings.status', 'active')
                ->where('marketplace_listings.is_visible', true)
                ->distinct('users.id')
                ->count('users.id'),
        ];

        $rarityOptions = Card::query()
            ->whereNotNull('rarity')
            ->distinct()
            ->orderBy('rarity')
            ->pluck('rarity')
            ->filter()
            ->values();

        return view('marketplace.index', [
            'marketMetrics' => $marketMetrics,
            'featuredListings' => $featuredListings,
            'wishlistMatchedListingIds' => $wishlistMatchedListingIds,
            'rarityOptions' => $rarityOptions,
            'filters' => [
                'search' => $search,
                'active' => $filter,
                'artist' => $artist,
                'rarity' => $rarity,
                'type' => $type,
                'min_price' => $minPrice,
                'max_price' => $maxPrice,
                'items' => [
                    'all' => 'All listings',
                    'my_listings' => 'My listings',
                ],
            ],
        ]);
    }

    public function create(Request $request): View
    {
        $userCards = $request->user()
            ->userCards()
            ->with('card')
            ->latest()
            ->get();
        $selectedUserCard = $userCards->firstWhere('id', (int) $request->query('user_card_id'));

        return view('marketplace.create', [
            'listing' => null,
            'userCards' => $userCards,
            'selectedUserCard' => $selectedUserCard,
            'formAction' => route('marketplace.store'),
            'formMethod' => 'POST',
            'submitLabel' => 'Save listing',
            'statusOptions' => $this->statusOptions(),
            'typeOptions' => $this->typeOptions(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateListing($request, true);

        DB::transaction(function () use ($request, $validated) {
            $userCard = UserCard::query()
                ->with('card')
                ->where('user_id', $request->user()->id)
                ->findOrFail($validated['user_card_id']);

            $alreadyListed = MarketplaceListing::query()
                ->where('user_id', $request->user()->id)
                ->where('user_card_id', $userCard->id)
                ->whereIn('status', ['active', 'draft'])
                ->exists();

            if ($alreadyListed) {
                throw ValidationException::withMessages([
                    'user_card_id' => 'This card is already listed on the marketplace.',
                ]);
            }

            $this->persistListingData($userCard, $validated, $request);

            $listing = MarketplaceListing::query()->updateOrCreate(
                [
                    'user_id' => $request->user()->id,
                    'user_card_id' => $userCard->id,
                ],
                [
                    'card_id' => $userCard->card_id,
                    'status' => $validated['status'] ?? 'draft',
                    'is_visible' => ($validated['status'] ?? 'draft') === 'active',
                ]
            );

            $this->persistProofData($listing, $request);
            $this->wishlistMatchService->markMatchesForListing($listing);
        });

        $this->activityLogger->record(
            $request->user(),
            ($validated['status'] ?? 'draft') === 'active' ? 'listing_published' : 'listing_created',
            ($validated['status'] ?? 'draft') === 'active' ? 'Listed a card on the marketplace' : 'Created a draft marketplace listing',
            ($validated['status'] ?? 'draft') === 'active'
                ? 'Published a card to the marketplace.'
                : 'Saved a marketplace listing as a draft.',
            [
                'user_card_id' => $validated['user_card_id'],
            ]
        );

        return redirect()
            ->route('marketplace.index', ['filter' => 'my_listings'])
            ->with('status', ($validated['status'] ?? 'draft') === 'active'
                ? 'Listing published successfully.'
                : 'Listing saved as draft.');
    }

    public function edit(Request $request, MarketplaceListing $marketplaceListing): View
    {
        abort_unless($marketplaceListing->user_id === $request->user()->id, 403);

        $marketplaceListing->load(['userCard.card']);

        return view('marketplace.edit', [
            'listing' => $marketplaceListing,
            'userCards' => collect(),
            'selectedUserCard' => $marketplaceListing->userCard,
            'formAction' => route('marketplace.update', $marketplaceListing),
            'formMethod' => 'PUT',
            'submitLabel' => 'Save changes',
            'statusOptions' => $this->statusOptions(),
            'typeOptions' => $this->typeOptions(),
        ]);
    }

    public function update(Request $request, MarketplaceListing $marketplaceListing): RedirectResponse
    {
        abort_unless($marketplaceListing->user_id === $request->user()->id, 403);

        $validated = $this->validateListing($request, false);

        DB::transaction(function () use ($request, $marketplaceListing, $validated) {
            $marketplaceListing->load('userCard.card');
            $userCard = $marketplaceListing->userCard;

            $this->persistListingData($userCard, $validated, $request);

            $newStatus = $validated['status'] ?? 'draft';

            $marketplaceListing->forceFill([
                'card_id' => $userCard->card_id,
                'status' => $newStatus,
                'is_visible' => $newStatus === 'active',
            ])->save();

            $this->persistProofData($marketplaceListing, $request);
            $this->wishlistMatchService->markMatchesForListing($marketplaceListing);

            if (! in_array($newStatus, ['active', 'draft'], true)) {
                $this->cancelPendingTradeRequests($marketplaceListing);
            }
        });

        $this->activityLogger->record(
            $request->user(),
            'listing_updated',
            'Updated a marketplace listing',
            'Adjusted listing details or proof photos.',
            [
                'listing_id' => $marketplaceListing->id,
                'card_id' => $marketplaceListing->card_id,
            ]
        );

        return redirect()
            ->route('marketplace.index', ['filter' => 'my_listings'])
            ->with('status', 'Listing updated successfully.');
    }

    public function markAsSold(Request $request, MarketplaceListing $marketplaceListing): RedirectResponse
    {
        abort_unless($marketplaceListing->user_id === $request->user()->id, 403);

        DB::transaction(function () use ($marketplaceListing) {
            $marketplaceListing->load('userCard');

            if ($marketplaceListing->userCard) {
                $marketplaceListing->userCard->forceFill([
                    'is_public' => false,
                    'is_listed' => false,
                    'marketplace_status' => 'sold',
                    'is_for_trade' => false,
                    'is_for_sale' => false,
                ])->save();
            }

            $marketplaceListing->forceFill([
                'status' => 'sold',
                'is_visible' => false,
            ])->save();

            $this->cancelPendingTradeRequests($marketplaceListing);
        });

        $this->activityLogger->record(
            $request->user(),
            'listing_sold',
            'Marked a listing as sold',
            'Closed a marketplace listing after a sale or trade.',
            [
                'listing_id' => $marketplaceListing->id,
                'card_id' => $marketplaceListing->card_id,
            ]
        );

        return redirect()
            ->route('marketplace.index', ['filter' => 'my_listings'])
            ->with('status', 'Listing marked as sold.');
    }

    public function archive(Request $request, MarketplaceListing $marketplaceListing): RedirectResponse
    {
        abort_unless($marketplaceListing->user_id === $request->user()->id, 403);

        DB::transaction(function () use ($marketplaceListing) {
            $marketplaceListing->load('userCard');

            if ($marketplaceListing->userCard) {
                $marketplaceListing->userCard->forceFill([
                    'is_public' => false,
                    'is_listed' => false,
                    'marketplace_status' => 'archived',
                ])->save();
            }

            $marketplaceListing->forceFill([
                'status' => 'archived',
                'is_visible' => false,
            ])->save();

            $this->cancelPendingTradeRequests($marketplaceListing);
        });

        $this->activityLogger->record(
            $request->user(),
            'listing_archived',
            'Archived a marketplace listing',
            'Removed a listing from public view.',
            [
                'listing_id' => $marketplaceListing->id,
                'card_id' => $marketplaceListing->card_id,
            ]
        );

        return redirect()
            ->route('marketplace.index', ['filter' => 'my_listings'])
            ->with('status', 'Listing archived.');
    }

    public function destroy(Request $request, MarketplaceListing $marketplaceListing): RedirectResponse
    {
        abort_unless($marketplaceListing->user_id === $request->user()->id, 403);

        DB::transaction(function () use ($marketplaceListing) {
            $userCard = $marketplaceListing->userCard;

            if ($userCard) {
                $userCard->forceFill([
                    'is_for_trade' => false,
                    'is_for_sale' => false,
                    'listing_price' => null,
                    'is_public' => false,
                    'is_listed' => false,
                    'marketplace_status' => 'archived',
                ])->save();
            }

            $marketplaceListing->forceFill([
                'status' => 'archived',
                'is_visible' => false,
            ])->save();

            $this->cancelPendingTradeRequests($marketplaceListing);
        });

        $this->activityLogger->record(
            $request->user(),
            'listing_deleted',
            'Removed a marketplace listing',
            'Deleted a listing from the marketplace.',
            [
                'listing_id' => $marketplaceListing->id,
                'card_id' => $marketplaceListing->card_id,
            ]
        );

        return redirect()
            ->route('marketplace.index', ['filter' => 'my_listings'])
            ->with('status', 'Listing removed from marketplace.');
    }

    public function verifyProof(Request $request, MarketplaceListing $marketplaceListing): RedirectResponse
    {
        abort_unless($marketplaceListing->user_id === $request->user()->id, 403);

        if (! $marketplaceListing->proof_photo) {
            return back()->withErrors([
                'proof_photo' => 'Upload a proof photo before marking it as verified.',
            ]);
        }

        $marketplaceListing->forceFill([
            'proof_verified' => true,
            'proof_status' => 'verified',
            'proof_score' => 100,
        ])->save();

        return back()->with('status', 'Proof marked as verified.');
    }

    private function validateListing(Request $request, bool $isCreate): array
    {
        $rules = [
            'title' => ['required', 'string', 'max:255'],
            'artist' => ['required', 'string', 'max:255'],
            'rarity' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:sale,trade'],
            'listing_price' => ['nullable', 'numeric', 'min:0'],
            'description' => ['nullable', 'string', 'max:1000'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'proof_photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'status' => ['required', 'in:draft,active'],
        ];

        if ($isCreate) {
            $rules['user_card_id'] = [
                'required',
                Rule::exists('user_cards', 'id')->where(function ($query) use ($request) {
                    $query->where('user_id', $request->user()->id);
                }),
            ];
        }

        $validated = $request->validate($rules);

        if ($validated['type'] === 'sale' && blank($validated['listing_price'])) {
            throw ValidationException::withMessages([
                'listing_price' => 'Please enter a price for sale listings.',
            ]);
        }

        return $validated;
    }

    private function persistListingData(UserCard $userCard, array $validated, Request $request): void
    {
        $card = $userCard->card;
        $status = $validated['status'] ?? 'draft';
        $isSale = $validated['type'] === 'sale';
        $isTrade = $validated['type'] === 'trade';
        $isActive = $status === 'active';
        $suggestedPrice = $userCard->estimated_value ?? $card->market_value ?? null;

        $card->forceFill([
            'title' => $validated['title'],
            'artist' => $validated['artist'],
            'rarity' => $validated['rarity'],
        ])->save();

        if ($request->hasFile('photo')) {
            if ($userCard->photo_path) {
                Storage::disk('public')->delete($userCard->photo_path);
            }

            $userCard->photo_path = $request->file('photo')->store('user-cards', 'public');
            if (Schema::hasColumn('cards', 'photo')) {
                $card->forceFill(['photo' => $userCard->photo_path])->save();
            }
        }
        $userCard->forceFill([
            'notes' => $validated['description'] ?? null,
            'is_for_sale' => $isSale,
            'is_for_trade' => $isTrade,
            'listing_price' => $isSale ? ($validated['listing_price'] ?? $suggestedPrice) : null,
            'is_public' => $isActive,
            'is_listed' => in_array($status, ['draft', 'active'], true),
            'marketplace_status' => $status,
        ])->save();
    }

    private function persistProofData(MarketplaceListing $listing, Request $request): void
    {
        if (! $request->hasFile('proof_photo') || ! $request->file('proof_photo')->isValid()) {
            return;
        }

        if ($listing->proof_storage_path) {
            Storage::disk('public')->delete($listing->proof_storage_path);
        }

        $file = $request->file('proof_photo');
        $filename = time().'_proof_'.$request->user()->id.'.'.$file->getClientOriginalExtension();
        $file->storeAs('proofs', $filename, 'public');

        $listing->forceFill([
            'proof_photo' => $filename,
            'proof_verified' => false,
            'proof_status' => 'pending',
            'proof_score' => null,
        ])->save();
    }

    private function cancelPendingTradeRequests(MarketplaceListing $listing): void
    {
        TradeRequest::query()
            ->where('listing_id', $listing->id)
            ->where('status', 'pending')
            ->with('sender')
            ->get()
            ->each(function (TradeRequest $tradeRequest) use ($listing) {
                $tradeRequest->forceFill(['status' => 'cancelled'])->save();

                if ($tradeRequest->sender) {
                    $this->activityLogger->record(
                        $tradeRequest->sender,
                        'trade_cancelled',
                        'Trade request cancelled',
                        'The listing is no longer available.',
                        [
                            'trade_request_id' => $tradeRequest->id,
                            'listing_id' => $listing->id,
                        ]
                    );
                }
            });
    }

    private function statusOptions(): array
    {
        return [
            'active' => 'Active',
            'draft' => 'Draft',
        ];
    }

    private function typeOptions(): array
    {
        return [
            'sale' => 'Sale',
            'trade' => 'Trade',
        ];
    }

    
}
