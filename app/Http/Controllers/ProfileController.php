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
        $this->loadProfileStats($user);

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

        // Recommendation: Move this to a query-level sum for better performance
        $totalCollectionValue = UserCard::where('user_id', $user->id)
            ->selectRaw('SUM(COALESCE(estimated_value, 0)) as total')
            ->value('total');

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
        $this->loadProfileStats($user);

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
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'bio' => ['nullable', 'string', 'max:1000'],
            'location' => ['nullable', 'string', 'max:255'],
            'website' => ['nullable', 'url', 'max:255'],
        ]);

        if ($request->input('remove_avatar') === '1') {
            if ($user->avatar) {
                $oldPath = storage_path('app/public/avatars/' . basename($user->avatar));
                if (file_exists($oldPath)) {
                    unlink($oldPath);
                }
                $user->avatar = null;
            }
        } elseif ($request->hasFile('avatar') && $request->file('avatar')->isValid()) {
            if ($user->avatar) {
                $oldPath = storage_path('app/public/avatars/' . basename($user->avatar));
                if (file_exists($oldPath)) {
                    unlink($oldPath);
                }
            }

            $avatarFile = $request->file('avatar');
            $filename = time() . '_' . $user->id . '.' . $avatarFile->getClientOriginalExtension();
            $avatarFile->move(storage_path('app/public/avatars'), $filename);
            $user->avatar = $filename;
        }

        $validated['avatar'] = $user->avatar;

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

    private function loadProfileStats(User $user): void
    {
        $user->loadCount([
            'marketplaceListings as active_listings_count' => fn ($query) => $query->activeVisible(),
            'trades as completed_trades_count' => fn ($query) => $query->where(function ($nested) {
                $nested->whereNotNull('completed_at')
                    ->orWhere('status', 'completed');
            }),
        ]);
    }
}
