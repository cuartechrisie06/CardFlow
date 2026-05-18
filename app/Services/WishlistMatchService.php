<?php

namespace App\Services;

use App\Models\MarketplaceListing;
use App\Models\User;
use App\Models\WishlistItem;
use Illuminate\Support\Collection;

class WishlistMatchService
{
    public function __construct(private ActivityLogger $activityLogger)
    {
    }

    public function markMatchesForListing(MarketplaceListing $listing): int
    {
        $listing->loadMissing(['card', 'user', 'userCard']);

        if (! $listing->user || ! $listing->card || ! $listing->userCard) {
            return 0;
        }

        if ($listing->status !== 'active' || ! $listing->is_visible) {
            return 0;
        }

        $matchedCount = 0;

        WishlistItem::query()
            ->with(['card', 'user'])
            ->where('user_id', '!=', $listing->user_id)
            ->chunkById(200, function (Collection $wishlistItems) use ($listing, &$matchedCount) {
                foreach ($wishlistItems as $wishlistItem) {
                    if ($this->scoreListingAgainstWishlist($wishlistItem, $listing) <= 0) {
                        continue;
                    }

                    $isFreshMatch = $wishlistItem->matched_at === null;

                    if ($isFreshMatch) {
                        $wishlistItem->forceFill(['matched_at' => now()])->save();

                        if ($wishlistItem->user) {
                            $this->activityLogger->record(
                                $wishlistItem->user,
                                'wishlist_match',
                                sprintf('Match found: %s is listed', $listing->card->title),
                                sprintf('%s has an active marketplace listing.', $listing->card->artist),
                                [
                                    'listing_id' => $listing->id,
                                    'card_id' => $listing->card_id,
                                    'wishlist_item_id' => $wishlistItem->id,
                                ]
                            );
                        }
                    }

                    $matchedCount++;
                }
            });

        return $matchedCount;
    }

    public function markMatchesForWishlistItem(WishlistItem $wishlistItem): int
    {
        $wishlistItem->loadMissing('card');

        if (! $wishlistItem->card) {
            return 0;
        }

        $matchedCount = 0;

        MarketplaceListing::query()
            ->with(['card', 'user', 'userCard'])
            ->activeVisible()
            ->where('user_id', '!=', $wishlistItem->user_id)
            ->chunkById(200, function (Collection $listings) use ($wishlistItem, &$matchedCount) {
                foreach ($listings as $listing) {
                    if ($this->scoreListingAgainstWishlist($wishlistItem, $listing) <= 0) {
                        continue;
                    }

                    $isFreshMatch = $wishlistItem->matched_at === null;

                    if ($isFreshMatch) {
                        $wishlistItem->forceFill(['matched_at' => now()])->save();

                        $wishlistItem->loadMissing('user');

                        if ($wishlistItem->user) {
                            $this->activityLogger->record(
                                $wishlistItem->user,
                                'wishlist_match',
                                sprintf('Match found: %s is listed', $listing->card->title),
                                sprintf('%s has an active marketplace listing.', $listing->card->artist),
                                [
                                    'listing_id' => $listing->id,
                                    'card_id' => $listing->card_id,
                                    'wishlist_item_id' => $wishlistItem->id,
                                ]
                            );
                        }
                    }

                    $matchedCount++;
                }
            });

        return $matchedCount;
    }

    public function buildMatchesForUser(User $user, Collection $wishlistItems, int $limitPerItem = 3): Collection
    {
        if ($wishlistItems->isEmpty()) {
            return collect();
        }

        $candidateListings = MarketplaceListing::query()
            ->with(['card', 'user', 'userCard'])
            ->activeVisible()
            ->where('user_id', '!=', $user->id)
            ->get();

        return $wishlistItems->mapWithKeys(function (WishlistItem $wishlistItem) use ($candidateListings, $limitPerItem) {
            $matches = $candidateListings
                ->map(function (MarketplaceListing $listing) use ($wishlistItem) {
                    $score = $this->scoreListingAgainstWishlist($wishlistItem, $listing);

                    if ($score <= 0) {
                        return null;
                    }

                    return [
                        'listing' => $listing,
                        'score' => $score,
                    ];
                })
                ->filter()
                ->sortByDesc(fn (array $match) => sprintf('%08d-%012d', $match['score'], $match['listing']->updated_at?->timestamp ?? 0))
                ->take($limitPerItem)
                ->values();

            return [$wishlistItem->id => $matches];
        });
    }

    private function scoreListingAgainstWishlist(WishlistItem $wishlistItem, MarketplaceListing $listing): int
    {
        $wishlistCard = $wishlistItem->card;
        $listedCard = $listing->card;

        if (! $wishlistCard || ! $listedCard || ! $listing->user || ! $listing->userCard) {
            return 0;
        }

        $score = 0;

        $wishlistArtist = $this->normalize($wishlistCard->artist);
        $listedArtist = $this->normalize($listedCard->artist);
        $wishlistTitle = $this->normalize($wishlistCard->title);
        $listedTitle = $this->normalize($listedCard->title);
        $wishlistAlbum = $this->normalize($wishlistCard->album);
        $listedAlbum = $this->normalize($listedCard->album);
        $wishlistEdition = $this->normalize($wishlistCard->edition);
        $listedEdition = $this->normalize($listedCard->edition);
        $wishlistRarity = $this->normalize($wishlistCard->rarity);
        $listedRarity = $this->normalize($listedCard->rarity);

        $sameCard = $wishlistCard->id === $listedCard->id;
        $titleMatches = $wishlistTitle !== ''
            && ($wishlistTitle === $listedTitle || $this->isPhraseMatch($wishlistTitle, $listedTitle));

        if (! $sameCard && ! $titleMatches) {
            return 0;
        }

        if ($wishlistArtist !== '' && $wishlistArtist === $listedArtist) {
            $score += 50;
        } elseif ($this->isPartialMatch($wishlistArtist, $listedArtist)) {
            $score += 20;
        }

        if ($wishlistTitle !== '' && $wishlistTitle === $listedTitle) {
            $score += 80;
        } elseif ($this->isPartialMatch($wishlistTitle, $listedTitle)) {
            $score += 30;
        }

        if ($wishlistAlbum !== '' && $wishlistAlbum === $listedAlbum) {
            $score += 35;
        } elseif ($this->isPartialMatch($wishlistAlbum, $listedAlbum)) {
            $score += 12;
        }

        if ($wishlistEdition !== '' && $wishlistEdition === $listedEdition) {
            $score += 25;
        } elseif ($this->isPartialMatch($wishlistEdition, $listedEdition)) {
            $score += 10;
        }

        if ($wishlistRarity !== '' && $wishlistRarity === $listedRarity) {
            $score += 15;
        }

        $wishlistTokens = $this->tokens($wishlistTitle.' '.$wishlistArtist.' '.$wishlistAlbum.' '.$wishlistEdition);
        $listedTokens = $this->tokens($listedTitle.' '.$listedArtist.' '.$listedAlbum.' '.$listedEdition);
        $score += count(array_intersect($wishlistTokens, $listedTokens)) * 8;

        return $score;
    }

    private function normalize(?string $value): string
    {
        $value = strtolower(trim((string) $value));
        $value = preg_replace('/[^a-z0-9\s]+/', ' ', $value) ?? '';
        $value = preg_replace('/\s+/', ' ', $value) ?? '';

        return trim($value);
    }

    private function tokens(?string $value): array
    {
        $normalized = $this->normalize($value);

        return $normalized === ''
            ? []
            : array_values(array_filter(explode(' ', $normalized)));
    }

    private function isPartialMatch(string $left, string $right): bool
    {
        if ($left === '' || $right === '') {
            return false;
        }

        return str_contains($left, $right)
            || str_contains($right, $left)
            || count(array_intersect($this->tokens($left), $this->tokens($right))) > 0;
    }

    private function isPhraseMatch(string $left, string $right): bool
    {
        if ($left === '' || $right === '') {
            return false;
        }

        return str_contains($left, $right)
            || str_contains($right, $left);
    }
}
