<?php

namespace App\Models;

use Database\Factories\CardFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Card extends Model
{
    /** @use HasFactory<CardFactory> */
    use HasFactory;

    protected $fillable = [
        'artist',
        'artist_id',
        'title',
        'edition',
        'album',
        'album_id',
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

    public function artist(): BelongsTo
    {
        return $this->belongsTo(Artist::class);
    }

    public function album(): BelongsTo
    {
        return $this->belongsTo(Album::class);
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
