<?php

namespace App\Models;

use Database\Factories\MarketplaceListingFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MarketplaceListing extends Model
{
    /** @use HasFactory<MarketplaceListingFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'user_card_id',
        'card_id',
        'status',
        'is_visible',
    ];

    protected function casts(): array
    {
        return [
            'is_visible' => 'boolean',
        ];
    }

    public function scopeActiveVisible(Builder $query): Builder
    {
        return $query
            ->whereNotNull('marketplace_listings.user_id')
            ->whereNotNull('marketplace_listings.user_card_id')
            ->whereNotNull('marketplace_listings.card_id')
            ->where('marketplace_listings.status', 'active')
            ->where('marketplace_listings.is_visible', true)
            ->whereHas('user')
            ->whereHas('card')
            ->whereHas('userCard', function (Builder $query) {
                $query->whereColumn('user_cards.user_id', 'marketplace_listings.user_id')
                    ->whereColumn('user_cards.card_id', 'marketplace_listings.card_id');
            });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function userCard(): BelongsTo
    {
        return $this->belongsTo(UserCard::class);
    }

    public function card(): BelongsTo
    {
        return $this->belongsTo(Card::class);
    }
}
