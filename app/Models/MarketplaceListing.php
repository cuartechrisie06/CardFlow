<?php

namespace App\Models;

use Database\Factories\MarketplaceListingFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

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
        'proof_photo',
        'proof_verified',
        'proof_status',
        'proof_score',
    ];

    protected function casts(): array
    {
        return [
            'is_visible' => 'boolean',
            'proof_verified' => 'boolean',
            'proof_score' => 'integer',
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

    public function tradeRequests(): HasMany
    {
        return $this->hasMany(TradeRequest::class, 'listing_id');
    }

    public function getProofStoragePathAttribute(): ?string
    {
        if (! $this->proof_photo) {
            return null;
        }

        if (str_starts_with($this->proof_photo, 'http://') || str_starts_with($this->proof_photo, 'https://')) {
            return null;
        }

        $path = str_replace('\\', '/', $this->proof_photo);
        $path = preg_replace('#^.*storage/app/public/#', '', $path) ?: $path;
        $path = preg_replace('#^.*public/storage/#', '', $path) ?: $path;
        $path = preg_replace('#^/?storage/#', '', $path) ?: $path;
        $path = ltrim($path, '/');

        return str_starts_with($path, 'proofs/')
            ? $path
            : 'proofs/'.$path;
    }

    public function getProofPhotoUrlAttribute(): ?string
    {
        if (! $this->proof_photo) {
            return null;
        }

        if (str_starts_with($this->proof_photo, 'http://') || str_starts_with($this->proof_photo, 'https://')) {
            return $this->proof_photo;
        }

        return $this->proof_storage_path
            ? Storage::url($this->proof_storage_path)
            : null;
    }
}
