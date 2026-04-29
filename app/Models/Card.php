<?php

namespace App\Models;

use Database\Factories\CardFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Card extends Model
{
    /** @use HasFactory<CardFactory> */
    use HasFactory;

    protected $fillable = [
        'artist',
        'title',
        'edition',
        'album',
        'rarity',
        'market_value',
        'thumbnail_style',
        'trend_score',
        'released_on',
    ];

    protected function casts(): array
    {
        return [
            'market_value' => 'decimal:2',
            'released_on' => 'date',
        ];
    }

    public function userCards(): HasMany
    {
        return $this->hasMany(UserCard::class);
    }

    public function trades(): HasMany
    {
        return $this->hasMany(Trade::class);
    }

    public function wishlistItems(): HasMany
    {
        return $this->hasMany(WishlistItem::class);
    }

    public function marketplaceListings(): HasMany
    {
        return $this->hasMany(MarketplaceListing::class);
    }
}
