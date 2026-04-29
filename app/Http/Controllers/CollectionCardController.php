<?php

namespace App\Http\Controllers;

use App\Models\Card;
use App\Models\MarketplaceListing;
use App\Models\UserCard;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CollectionCardController extends Controller
{
    public function create(): View
    {
        return view('collection.create');
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
            'photo' => ['nullable', 'image', 'max:5120'],
            'is_public' => ['nullable', 'boolean'],
            'is_for_trade' => ['nullable', 'boolean'],
            'is_for_sale' => ['nullable', 'boolean'],
            'listing_price' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $photoPath = $request->hasFile('photo')
            ? $request->file('photo')->store('user-cards', 'public')
            : null;
        $listingState = UserCard::deriveListingState(
            $request->boolean('is_public'),
            $request->boolean('is_for_trade'),
            $request->boolean('is_for_sale'),
        );

        DB::transaction(function () use ($request, $validated, $photoPath, $listingState) {
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

            $userCard = UserCard::query()->create([
                'user_id' => $request->user()->id,
                'card_id' => $card->id,
                'condition' => $validated['condition'],
                'purchase_price' => $validated['purchase_price'] ?? null,
                'estimated_value' => $validated['estimated_value'] ?? $validated['market_value'],
                'acquired_at' => $validated['acquired_at'] ?? now(),
                'is_listed' => $listingState['is_listed'],
                'marketplace_status' => $listingState['marketplace_status'],
                'is_public' => $request->boolean('is_public'),
                'is_for_trade' => $request->boolean('is_for_trade'),
                'is_for_sale' => $request->boolean('is_for_sale'),
                'listing_price' => $validated['listing_price'] ?? null,
                'photo_path' => $photoPath,
                'notes' => $validated['notes'] ?? null,
            ]);

            $this->syncMarketplaceListing($userCard);
        });

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
            'photo' => ['nullable', 'image', 'max:5120'],
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

                $userCard->card()->associate($replacementCard);
            } else {
                $currentCard->update($cardAttributes);
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

            $userCard->refresh();
            $this->syncMarketplaceListing($userCard);
        });

        return redirect()->route('collection.index')
            ->with('status', 'Card updated successfully.');
    }

    private function syncMarketplaceListing(UserCard $userCard): void
    {
        $shouldBeListed = $userCard->is_public || $userCard->is_for_trade || $userCard->is_for_sale;

        if (! $shouldBeListed) {
            $userCard->marketplaceListing()?->delete();

            return;
        }

        MarketplaceListing::query()->updateOrCreate(
            ['user_card_id' => $userCard->id],
            [
                'user_id' => $userCard->user_id,
                'card_id' => $userCard->card_id,
                'status' => 'active',
                'is_visible' => true,
            ],
        );
    }
}
