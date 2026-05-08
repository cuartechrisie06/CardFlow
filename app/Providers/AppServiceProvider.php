<?php

namespace App\Providers;

use App\Models\Conversation;
use App\Models\MarketplaceListing;
use App\Models\UserCard;
use App\Models\WishlistItem;
use App\Policies\UserCardPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(UserCard::class, UserCardPolicy::class);

        View::share('formatMoney', fn (
            $v
        ) => 'PHP ' . number_format((float) $v, 2));

        View::share('rarityLabel', fn ($r) => match ($r) {
            'R' => 'Rare',
            'SR' => 'Super Rare',
            'UR' => 'Ultra Rare',
            'C' => 'Common',
            default => $r ?? 'Unknown',
        });

        View::share('storagePhotoUrl', fn (?string $path) => $path && Storage::disk('public')->exists($path)
            ? Storage::url($path)
            : null);

        View::composer('*', function ($view) {
            $unreadCount = 0;
            $listingInboxCount = 0;
            $wishlistMatchCount = 0;

            if (auth()->check()) {
                $user = auth()->user();

                $unreadCount = Conversation::query()
                    ->forUser($user)
                    ->whereHas('messages', function ($query) use ($user) {
                        $query->whereNull('read_at')
                            ->where('receiver_id', $user->id);
                    })
                    ->count();

                $listingInboxCount = Conversation::query()
                    ->forUser($user)
                    ->whereHas('marketplaceListing', function ($query) use ($user) {
                        $query->where('user_id', $user->id);
                    })
                    ->whereHas('messages', function ($query) use ($user) {
                        $query->whereNull('read_at')
                            ->where('receiver_id', $user->id);
                    })
                    ->count();

                $wishlistPairs = WishlistItem::query()
                    ->with('card:id,artist,album')
                    ->where('user_id', $user->id)
                    ->get()
                    ->map(fn ($item) => [
                        'artist' => trim((string) $item->card?->artist),
                        'album' => trim((string) ($item->card?->album ?? '')),
                    ])
                    ->filter(fn (array $pair) => $pair['artist'] !== '')
                    ->unique(fn (array $pair) => mb_strtolower($pair['artist'] . '|' . $pair['album']))
                    ->values();

                if ($wishlistPairs->isNotEmpty()) {
                    $wishlistMatchCount = MarketplaceListing::query()
                        ->activeVisible()
                        ->where('user_id', '!=', $user->id)
                        ->with('card:id,artist,album')
                        ->get()
                        ->filter(function ($listing) use ($wishlistPairs) {
                            $artist = trim((string) $listing->card?->artist);
                            $album = trim((string) ($listing->card?->album ?? ''));

                            return $wishlistPairs->contains(fn (array $pair) => (
                                mb_strtolower($pair['artist']) === mb_strtolower($artist)
                                && mb_strtolower($pair['album']) === mb_strtolower($album)
                            ));
                        })
                        ->count();
                }
            }

            $view->with([
                'unreadCount' => $unreadCount,
                'listingInboxCount' => $listingInboxCount,
                'wishlistMatchCount' => $wishlistMatchCount,
            ]);
        });
    }
}

