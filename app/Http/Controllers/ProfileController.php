<?php

namespace App\Http\Controllers;

use App\Models\MarketplaceListing;
use App\Models\Trade;
use App\Models\User;
use App\Models\UserCard;
use App\Models\WishlistItem;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function show(Request $request, User $user): View
    {
        $user->loadCount([
            'marketplaceListings as active_listings_count' => fn ($query) => $query->activeVisible(),
            'trades as completed_trades_count' => fn ($query) => $query->where(function ($nested) {
                $nested->whereNotNull('completed_at')
                    ->orWhere('status', 'completed');
            }),
        ]);

        $viewer = $request->user();
        $isOwnProfile = $viewer?->is($user) ?? false;
        $activeTab = $request->string('tab')->value() ?: 'collection';
        $allowedTabs = ['collection', 'listings'];

        if ($isOwnProfile) {
            $allowedTabs[] = 'wishlist';
        }

        if (! in_array($activeTab, $allowedTabs, true)) {
            $activeTab = 'collection';
        }

        $collectionCards = UserCard::query()
            ->with(['card', 'marketplaceListing'])
            ->where('user_id', $user->id)
            ->when(! $isOwnProfile, fn ($query) => $query->where('is_public', true))
            ->latest('acquired_at')
            ->latest('id')
            ->get();

        $marketplaceListings = MarketplaceListing::query()
            ->with(['card', 'userCard'])
            ->where('user_id', $user->id)
            ->activeVisible()
            ->latest('updated_at')
            ->get();

        $wishlistItems = $isOwnProfile
            ? WishlistItem::query()
                ->with('card')
                ->where('user_id', $user->id)
                ->latest('id')
                ->get()
            : collect();

        $totalCollectionValue = $collectionCards->sum(function (UserCard $userCard) {
            return (float) ($userCard->estimated_value
                ?? $userCard->purchase_price
                ?? $userCard->card?->market_value
                ?? 0);
        });

        return view('profile.show', [
            'profileUser' => $user,
            'activeTab' => $activeTab,
            'collectionCards' => $collectionCards,
            'marketplaceListings' => $marketplaceListings,
            'wishlistItems' => $wishlistItems,
            'isOwnProfile' => $isOwnProfile,
            'canViewWishlist' => $isOwnProfile,
            'totalCards' => $collectionCards->count(),
            'totalCollectionValue' => $totalCollectionValue,
            'activeListingsCount' => $user->active_listings_count,
            'completedTradesCount' => $user->completed_trades_count,
        ]);
    }

    public function showcase(Request $request, User $user): View
    {
        $user->loadCount([
            'marketplaceListings as active_listings_count' => fn ($query) => $query->activeVisible(),
            'trades as completed_trades_count' => fn ($query) => $query->where(function ($nested) {
                $nested->whereNotNull('completed_at')
                    ->orWhere('status', 'completed');
            }),
        ]);

        $publicCards = UserCard::query()
            ->with('card')
            ->where('user_id', $user->id)
            ->where('is_public', true)
            ->latest('acquired_at')
            ->latest('id')
            ->get();

        $activeListings = MarketplaceListing::query()
            ->with(['card', 'userCard'])
            ->where('user_id', $user->id)
            ->activeVisible()
            ->latest('updated_at')
            ->get();

        $totalPublicCards = $publicCards->count();
        $viewer = $request->user();

        return view('profile.showcase', [
            'profileUser' => $user,
            'publicCards' => $publicCards,
            'activeListings' => $activeListings,
            'totalPublicCards' => $totalPublicCards,
            'completedTradesCount' => $user->completed_trades_count,
            'activeListingsCount' => $user->active_listings_count,
            'viewer' => $viewer,
        ]);
    }

    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => [
                'required',
                'string',
                'max:255',
                Rule::unique('users', 'username')->ignore($user->id),
            ],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'avatar' => ['nullable', 'image', 'max:5120'],
            'bio' => ['nullable', 'string', 'max:1000'],
            'location' => ['nullable', 'string', 'max:255'],
            'website' => ['nullable', 'url', 'max:255'],
        ]);

        if ($request->hasFile('avatar')) {
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }

            $validated['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        $validated['bio'] = $validated['bio'] ?? null;
        $validated['location'] = $validated['location'] ?? null;
        $validated['website'] = $validated['website'] ?? null;

        $user->update($validated);

        return redirect()
            ->route('profile.edit')
            ->with('status', 'Profile updated successfully.');
    }

    public function settings(Request $request): View
    {
        return view('profile.settings', [
            'user' => $request->user(),
        ]);
    }
}
