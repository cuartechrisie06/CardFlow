<?php

namespace App\Http\Controllers;

use App\Models\Card;
use App\Models\Conversation;
use App\Models\MarketplaceListing;
use App\Models\Trade;
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



class CollectionCardController extends Controller
{
    public function __construct(
        private WishlistMatchService $wishlistMatchService,
        private ActivityLogger $activityLogger
    ) {
    }

    public function create(): View
    {
        return view('collection.create');
    }

    public function show(UserCard $userCard)
    {
        $this->authorize('view', $userCard);

        $userCard->load('card');

        $card = $userCard->card;
        $imagePath = $this->publicStorageUrl($userCard->photo_path)
            ?: asset('images/placeholder-card.png');

        $rarityLabel = match ($card->rarity) {
            'R' => 'Rare',
            'SR' => 'Super Rare',
            'UR' => 'Ultra Rare',
            default => $card->rarity,
        };

        $marketValue = (float) ($card->market_value ?? 0);
        $purchasePrice = (float) ($userCard->purchase_price ?? 0);
        $estimatedValue = (float) ($userCard->estimated_value ?? $marketValue);
        $valueDelta = $estimatedValue - $purchasePrice;
        $isPositiveDelta = $valueDelta >= 0;

        return view('collection.show', compact(
            'userCard',
            'card',
            'imagePath',
            'rarityLabel',
            'marketValue',
            'purchasePrice',
            'estimatedValue',
            'valueDelta',
            'isPositiveDelta'
        ));
    }

    

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'artist' => ['required', 'string', 'max:255'],
            'title' => ['required', 'string', 'max:255'],
            'album' => ['nullable', 'string', 'max:255'],
            'edition' => ['nullable', 'string', 'max:255'],
            'rarity' => ['required', 'string', 'max:255'],
            'market_value' => ['required', 'numeric', 'min:0'],
            'condition' => ['required', 'string', 'max:255'],
            'estimated_value' => ['nullable', 'numeric', 'min:0'],
            'purchase_price' => ['nullable', 'numeric', 'min:0'],
            'acquired_at' => ['nullable', 'date'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'list_on_marketplace' => ['nullable', 'boolean'],
            'is_public' => ['nullable', 'boolean'],
            'is_for_trade' => ['nullable', 'boolean'],
            'is_for_sale' => ['nullable', 'boolean'],
            'listing_type' => ['nullable', 'in:sale,trade,both'],
            'listing_price' => ['nullable', 'numeric', 'min:0'],
            'listing_description' => ['nullable', 'string', 'max:1000'],
            'listing_status' => ['nullable', 'in:active,draft'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $photoPath = $request->hasFile('photo')
            ? $request->file('photo')->store('user-cards', 'public')
            : null;
        $legacyMarketplaceFlags = $request->boolean('is_public')
            || $request->boolean('is_for_trade')
            || $request->boolean('is_for_sale');
        $usesNewMarketplaceFlow = $request->has('list_on_marketplace') || $request->has('listing_type');
        $listOnMarketplace = $request->boolean('list_on_marketplace') || $legacyMarketplaceFlags;
        $listingType = $validated['listing_type']
            ?? ($request->boolean('is_for_trade') && $request->boolean('is_for_sale')
                ? 'both'
                : ($request->boolean('is_for_trade') ? 'trade' : 'sale'));
        $listingStatus = $validated['listing_status'] ?? 'active';
        $listForSale = false;
        $listForTrade = false;
        $listPrice = null;

        if ($listOnMarketplace) {
            $listForSale = $usesNewMarketplaceFlow
                ? in_array($listingType, ['sale', 'both'], true)
                : $request->boolean('is_for_sale');
            $listForTrade = $usesNewMarketplaceFlow
                ? in_array($listingType, ['trade', 'both'], true)
                : $request->boolean('is_for_trade');
            $listPrice = $listForSale
                ? ($validated['listing_price'] ?? $validated['estimated_value'] ?? $validated['market_value'])
                : null;
        }

        $listingState = [
            'is_listed' => $listOnMarketplace,
            'marketplace_status' => $listOnMarketplace ? $listingStatus : 'draft',
        ];

        $storedNotes = $validated['notes'] ?? null;
        $listingDescription = trim((string) ($validated['listing_description'] ?? ''));

        if ($listingDescription !== '') {
            $storedNotes = $storedNotes
                ? trim($storedNotes . "\n\nMarketplace: " . $listingDescription)
                : $listingDescription;
        }

        $card = null;
        $userCard = null;

        DB::transaction(function () use ($request, $validated, $photoPath, $listingState, $listOnMarketplace, $listForSale, $listForTrade, $listPrice, $storedNotes, &$card, &$userCard) {
            $card = Card::query()->firstOrCreate(
                [
                    'artist' => $validated['artist'],
                    'title' => $validated['title'],
                    'album' => $validated['album'] ?? null,
                    'edition' => $validated['edition'] ?? null,
                ],
                [
                    'rarity' => $validated['rarity'],
                    'market_value' => $validated['market_value'],
                    'thumbnail_style' => 'market-thumb-one',
                    'trend_score' => 65,
                ]
            );

            if ($photoPath && Schema::hasColumn('cards', 'photo')) {
                $card->forceFill(['photo' => $photoPath])->save();
            }

            $userCard = UserCard::query()->create([
                'user_id' => $request->user()->id,
                'card_id' => $card->id,
                'condition' => $validated['condition'],
                'purchase_price' => $validated['purchase_price'] ?? null,
                'estimated_value' => $validated['estimated_value'] ?? $validated['market_value'],
                'acquired_at' => $validated['acquired_at'] ?? now(),
                'is_listed' => $listingState['is_listed'],
                'marketplace_status' => $listingState['marketplace_status'],
                'is_public' => $request->boolean('is_public') || ($listOnMarketplace && $listingState['marketplace_status'] === 'active'),
                'is_for_trade' => $listForTrade,
                'is_for_sale' => $listForSale,
                'listing_price' => $listPrice,
                'photo_path' => $photoPath,
                'notes' => $storedNotes,
            ]);

            $this->syncMarketplaceListing($userCard, $listingState['marketplace_status']);
        });

        UserOnboarding::query()->updateOrCreate(
            ['user_id' => $request->user()->id],
            ['added_first_card' => true],
        );

        $this->activityLogger->record(
            $request->user(),
            'card_added',
            'Added a new card',
            $listOnMarketplace
                ? 'Added a photocard and listed it on the marketplace.'
                : 'Added a photocard to the personal collection.',
            [
                'card_id' => $card->id,
                'user_card_id' => $userCard->id,
                'marketplace_status' => $userCard->marketplace_status,
            ]
        );

        return redirect()->route('collection.index')
            ->with('status', 'Card added to your collection.');
    }

    public function edit(Request $request, UserCard $userCard): View
    {
        $this->authorize('update', $userCard);

        $userCard->load('card');

        return view('collection.edit', [
            'userCard' => $userCard,
        ]);
    }

    public function update(Request $request, UserCard $userCard): RedirectResponse
    {
        $this->authorize('update', $userCard);

        $validated = $request->validate([
            'artist' => ['required', 'string', 'max:255'],
            'title' => ['required', 'string', 'max:255'],
            'album' => ['nullable', 'string', 'max:255'],
            'edition' => ['nullable', 'string', 'max:255'],
            'rarity' => ['required', 'string', 'max:255'],
            'market_value' => ['required', 'numeric', 'min:0'],
            'condition' => ['required', 'string', 'max:255'],
            'estimated_value' => ['nullable', 'numeric', 'min:0'],
            'purchase_price' => ['nullable', 'numeric', 'min:0'],
            'acquired_at' => ['nullable', 'date'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'is_public' => ['nullable', 'boolean'],
            'is_for_trade' => ['nullable', 'boolean'],
            'is_for_sale' => ['nullable', 'boolean'],
            'listing_price' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $newPhotoPath = $request->hasFile('photo')
            ? $request->file('photo')->store('user-cards', 'public')
            : null;
        $listingState = UserCard::deriveListingState(
            $request->boolean('is_public'),
            $request->boolean('is_for_trade'),
            $request->boolean('is_for_sale'),
        );

        DB::transaction(function () use ($request, $validated, $userCard, $newPhotoPath, $listingState) {
            $cardAttributes = [
                'artist' => $validated['artist'],
                'title' => $validated['title'],
                'album' => $validated['album'] ?? null,
                'edition' => $validated['edition'] ?? null,
                'rarity' => $validated['rarity'],
                'market_value' => $validated['market_value'],
            ];

            $currentCard = $userCard->card;

            if ($currentCard->userCards()->whereKeyNot($userCard->id)->exists()) {
                $replacementCard = Card::query()->create(array_merge($cardAttributes, [
                    'thumbnail_style' => $currentCard->thumbnail_style,
                    'trend_score' => $currentCard->trend_score,
                    'released_on' => $currentCard->released_on,
                ]));

                if ($newPhotoPath && Schema::hasColumn('cards', 'photo')) {
                    $replacementCard->forceFill(['photo' => $newPhotoPath])->save();
                }

                $userCard->card()->associate($replacementCard);
            } else {
                $currentCard->update($cardAttributes);

                if ($newPhotoPath && Schema::hasColumn('cards', 'photo')) {
                    $currentCard->forceFill(['photo' => $newPhotoPath])->save();
                }
            }

            if ($newPhotoPath && $userCard->photo_path) {
                Storage::disk('public')->delete($userCard->photo_path);
            }

            $userCard->update([
                'card_id' => $userCard->card->id,
                'condition' => $validated['condition'],
                'purchase_price' => $validated['purchase_price'] ?? null,
                'estimated_value' => $validated['estimated_value'] ?? $validated['market_value'],
                'acquired_at' => $validated['acquired_at'] ?? $userCard->acquired_at,
                'is_listed' => $listingState['is_listed'],
                'marketplace_status' => $listingState['marketplace_status'],
                'is_public' => $request->boolean('is_public'),
                'is_for_trade' => $request->boolean('is_for_trade'),
                'is_for_sale' => $request->boolean('is_for_sale'),
                'listing_price' => $validated['listing_price'] ?? null,
                'photo_path' => $newPhotoPath ?: $userCard->photo_path,
                'notes' => $validated['notes'] ?? null,
            ]);

            $currentListingStatus = $userCard->marketplace_status ?? 'active';

            $userCard->refresh();
            $this->syncMarketplaceListing($userCard, $currentListingStatus);
        });

        $this->activityLogger->record(
            $request->user(),
            'card_updated',
            'Updated a card',
            'Updated collection details and marketplace flags.',
            [
                'card_id' => $userCard->card_id,
                'user_card_id' => $userCard->id,
                'marketplace_status' => $userCard->fresh()->marketplace_status,
            ]
        );

        return redirect()->route('collection.index')
            ->with('status', 'Card updated successfully.');
    }

    private function syncMarketplaceListing(UserCard $userCard, string $listingStatus = 'active'): void
    {
        $shouldBeListed = $userCard->is_public || $userCard->is_for_trade || $userCard->is_for_sale;

        if (! $shouldBeListed) {
            $userCard->marketplaceListing()?->delete();

            return;
        }

        $listing = MarketplaceListing::query()->updateOrCreate(
            ['user_card_id' => $userCard->id],
            [
                'user_id' => $userCard->user_id,
                'card_id' => $userCard->card_id,
                'status' => $listingStatus,
                'is_visible' => $listingStatus === 'active',
            ],
        );

        $this->wishlistMatchService->markMatchesForListing($listing);
    }
public function destroy(\App\Models\UserCard $userCard)
{
    $this->authorize('delete', $userCard);

    $marketplaceListing = $userCard->marketplaceListing()->first();

    if ($marketplaceListing?->status === 'active' && $marketplaceListing->is_visible) {
        return redirect()
            ->route('collection.index')
            ->withErrors([
                'card' => 'Archive or remove the active marketplace listing before deleting this card.',
            ]);
    }

    if ($marketplaceListing && Conversation::query()->where('marketplace_listing_id', $marketplaceListing->id)->exists()) {
        return redirect()
            ->route('collection.index')
            ->withErrors([
                'card' => 'This card is linked to a message thread. Keep the card or clear the listing conversation first.',
            ]);
    }

    if (Trade::query()->where('user_id', auth()->id())->where('card_id', $userCard->card_id)->exists()) {
        return redirect()
            ->route('collection.index')
            ->withErrors([
                'card' => 'This card is part of your trade history and cannot be deleted safely.',
            ]);
    }

    \Illuminate\Support\Facades\DB::transaction(function () use ($userCard) {
        \App\Models\MarketplaceListing::query()
            ->where('user_card_id', $userCard->id)
            ->where('user_id', auth()->id())
            ->update([
                'status' => 'archived',
                'is_visible' => false,
            ]);

        if (! empty($userCard->photo_path)) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($userCard->photo_path);
        }

        $userCard->delete();
    });

    $this->activityLogger->record(
        request()->user(),
        'card_deleted',
        'Deleted a card',
        'Removed a card from the collection.',
        [
            'card_id' => $userCard->card_id,
            'user_card_id' => $userCard->id,
        ]
    );

    return redirect()
        ->route('collection.index')
        ->with('status', 'Card deleted successfully.');
}

    public function markAsTraded(UserCard $userCard): RedirectResponse
    {
        $this->authorize('update', $userCard);

        DB::transaction(function () use ($userCard) {
            if ($marketplaceListing = $userCard->marketplaceListing) {
                $marketplaceListing->forceFill([
                    'status' => 'traded',
                    'is_visible' => false,
                ])->save();
            }

            $userCard->forceFill([
                'is_public' => false,
                'is_for_trade' => false,
                'is_for_sale' => false,
                'is_listed' => false,
                'marketplace_status' => 'traded',
            ])->save();
        });

        $this->activityLogger->record(
            request()->user(),
            'card_traded',
            'Marked a card as traded',
            'Updated the collection card and cleared marketplace availability.',
            [
                'card_id' => $userCard->card_id,
                'user_card_id' => $userCard->id,
            ]
        );

        return redirect()
            ->route('collection.show', $userCard)
            ->with('status', 'Card marked as traded.');
    }

    public function markAsSold(UserCard $userCard): RedirectResponse
    {
        $this->authorize('update', $userCard);

        DB::transaction(function () use ($userCard) {
            if ($marketplaceListing = $userCard->marketplaceListing) {
                $marketplaceListing->forceFill([
                    'status' => 'sold',
                    'is_visible' => false,
                ])->save();
            }

            $userCard->forceFill([
                'is_public' => false,
                'is_for_trade' => false,
                'is_for_sale' => false,
                'is_listed' => false,
                'marketplace_status' => 'sold',
            ])->save();
        });

        $this->activityLogger->record(
            request()->user(),
            'card_sold',
            'Marked a card as sold',
            'Updated the collection card and cleared marketplace availability.',
            [
                'card_id' => $userCard->card_id,
                'user_card_id' => $userCard->id,
            ]
        );

        return redirect()
            ->route('collection.show', $userCard)
            ->with('status', 'Card marked as sold.');
    }

    private function publicStorageUrl(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        $normalizedPath = str_replace('\\', '/', $path);
        $normalizedPath = preg_replace('#^.*storage/app/public/#', '', $normalizedPath) ?: $normalizedPath;
        $normalizedPath = preg_replace('#^.*public/storage/#', '', $normalizedPath) ?: $normalizedPath;
        $normalizedPath = preg_replace('#^/?storage/#', '', $normalizedPath) ?: $normalizedPath;
        $normalizedPath = ltrim($normalizedPath, '/');

        return Storage::disk('public')->exists($normalizedPath)
            ? Storage::url($normalizedPath)
            : null;
    }
    
}
