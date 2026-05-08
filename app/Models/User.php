<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;

#[Fillable(['name', 'username', 'email', 'password', 'avatar', 'bio', 'location', 'website'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function userCards(): HasMany
    {
        return $this->hasMany(UserCard::class);
    }

    public function publicMarketplaceCards(): HasMany
    {
        return $this->userCards()->visibleInMarketplace();
    }

    public function marketplaceListings(): HasMany
    {
        return $this->hasMany(MarketplaceListing::class);
    }

    public function trades(): HasMany
    {
        return $this->hasMany(Trade::class);
    }

    public function wishlistItems(): HasMany
    {
        return $this->hasMany(WishlistItem::class);
    }

    public function activities(): HasMany
    {
        return $this->hasMany(Activity::class);
    }

    public function conversationsStarted(): HasMany
    {
        return $this->hasMany(Conversation::class, 'user_one_id');
    }

    public function conversationsReceived(): HasMany
    {
        return $this->hasMany(Conversation::class, 'user_two_id');
    }

    public function sentMessages(): HasMany
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    public function receivedMessages(): HasMany
    {
        return $this->hasMany(Message::class, 'receiver_id');
    }

    public function savedViews(): HasMany
    {
        return $this->hasMany(SavedView::class);
    }

    public function getCompletedTradesCountAttribute(): int
    {
        if (array_key_exists('completed_trades_count', $this->attributes)) {
            return (int) $this->attributes['completed_trades_count'];
        }

        return (int) $this->trades()
            ->where('status', 'completed')
            ->count();
    }

    public function getSellerBadgeAttribute(): ?string
    {
        return $this->completed_trades_count >= 3
            ? 'Verified Seller'
            : null;
    }

    public function getAvatarUrlAttribute(): ?string
    {
        return $this->avatar && Storage::disk('public')->exists($this->avatar)
            ? Storage::url($this->avatar)
            : null;
    }

    public function getInitialsAttribute(): string
    {
        return collect(preg_split('/\s+/', trim($this->name)))
            ->filter()
            ->take(2)
            ->map(fn ($part) => strtoupper(mb_substr($part, 0, 1)))
            ->implode('');
    }
}
