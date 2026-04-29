<?php

namespace App\Models;

use Database\Factories\UserCardFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasOne;

class UserCard extends Model
{
    /** @use HasFactory<UserCardFactory> */
    use HasFactory;

    protected $table = 'user_cards';

    protected $fillable = [
        'user_id',
        'card_id',
        'condition',
        'purchase_price',
        'estimated_value',
        'acquired_at',
        'is_listed',
        'marketplace_status',
        'is_public',
        'is_for_trade',
        'is_for_sale',
        'listing_price',
        'photo_path',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'purchase_price' => 'decimal:2',
            'estimated_value' => 'decimal:2',
            'acquired_at' => 'datetime',
            'is_listed' => 'boolean',
            'is_public' => 'boolean',
            'is_for_trade' => 'boolean',
            'is_for_sale' => 'boolean',
            'listing_price' => 'decimal:2',
        ];
    }

    public function scopeVisibleInMarketplace(Builder $query): Builder
    {
        return $query->whereHas('marketplaceListing', fn (Builder $query) => $query->activeVisible());
    }

    public function isVisibleTo(?User $viewer = null): bool
    {
        if ($viewer && $viewer->id === $this->user_id) {
            return true;
        }

        return $this->relationLoaded('marketplaceListing')
            ? $this->marketplaceListing !== null && $this->marketplaceListing->status === 'active' && $this->marketplaceListing->is_visible
            : $this->marketplaceListing()->activeVisible()->exists();
    }

    public static function deriveListingState(bool $isPublic, bool $isForTrade, bool $isForSale): array
    {
        $isListed = $isPublic || $isForTrade || $isForSale;

        return [
            'is_listed' => $isListed,
            'marketplace_status' => $isListed ? 'active' : 'draft',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function card(): BelongsTo
    {
        return $this->belongsTo(Card::class);
    }

    public function marketplaceListing(): HasOne
    {
        return $this->hasOne(MarketplaceListing::class);
    }
}
