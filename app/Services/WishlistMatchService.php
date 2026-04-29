<?php

namespace App\Services;

use App\Models\MarketplaceListing;
use App\Models\User;
use App\Models\WishlistItem;
use Illuminate\Support\Collection;

class WishlistMatchService
{
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

        return $score >= 30 ? $score : 0;
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
}
