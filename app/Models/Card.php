<?php

namespace App\Models;

use Database\Factories\CardFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Card extends Model
{
    /** @use HasFactory<CardFactory> */
    use HasFactory;

    protected $fillable = [
        'artist',
        'artist_id',
        'title',
        'slug',
        'member_name',
        'edition',
        'album',
        'album_id',
        'rarity',
        'variant_type',
        'finish',
        'official_image_url',
        'catalog_code',
        'photo',
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

    public function getPhotoUrlAttribute(): ?string
    {
        $photoValue = array_key_exists('photo', $this->attributes)
            ? $this->attributes['photo']
            : null;
        $photoUrl = $this->normalizePhotoValue($photoValue ?: $this->official_image_url);

        if ($photoUrl) {
            return $photoUrl;
        }

        $fallbackPath = $this->userCards()
            ->whereNotNull('photo_path')
            ->orderBy('id')
            ->value('photo_path');

        return $this->normalizePhotoValue($fallbackPath) ?: asset('images/placeholder-card.png');
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

    public function aliases(): HasMany
    {
        return $this->hasMany(CardAlias::class);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(CardVariant::class);
    }

    private function normalizePhotoValue(?string $value): ?string
    {
        if (! $this->isUsablePhotoValue($value)) {
            return null;
        }

        if (str_starts_with($value, 'http://') || str_starts_with($value, 'https://')) {
            return $value;
        }

        $normalizedPath = str_replace('\\', '/', $value);
        $normalizedPath = preg_replace('#^.*storage/app/public/#', '', $normalizedPath) ?: $normalizedPath;
        $normalizedPath = preg_replace('#^.*public/storage/#', '', $normalizedPath) ?: $normalizedPath;
        $normalizedPath = preg_replace('#^/?storage/#', '', $normalizedPath) ?: $normalizedPath;
        $normalizedPath = ltrim($normalizedPath, '/');

        return Storage::disk('public')->exists($normalizedPath)
            ? Storage::url($normalizedPath)
            : null;
    }

    private function isUsablePhotoValue(?string $value): bool
    {
        return is_string($value)
            && trim($value) !== ''
            && $value !== 'photo';
    }
}
