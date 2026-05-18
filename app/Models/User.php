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

#[Fillable(['name', 'username', 'email', 'password', 'avatar', 'bio', 'location', 'website', 'is_admin', 'onboarding_completed', 'onboarding_step', 'suspended_at', 'last_login_at'])]
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
            'is_admin' => 'boolean',
            'onboarding_completed' => 'boolean',
            'onboarding_step' => 'integer',
            'suspended_at' => 'datetime',
            'last_login_at' => 'datetime',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->is_admin === true;
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

    public function sentTradeRequests(): HasMany
    {
        return $this->hasMany(TradeRequest::class, 'sender_id');
    }

    public function receivedTradeRequests(): HasMany
    {
        return $this->hasMany(TradeRequest::class, 'receiver_id');
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

        $legacyCompleted = (int) $this->trades()
            ->where('status', 'completed')
            ->count();

        $requestCompleted = TradeRequest::query()
            ->where(function ($query) {
                $query->where('sender_id', $this->id)
                    ->orWhere('receiver_id', $this->id);
            })
            ->where('status', 'completed')
            ->count();

        return $legacyCompleted + $requestCompleted;
    }

    public function getSellerBadgeAttribute(): ?string
    {
        $completed = $this->completed_trades_count;

        if ($completed <= 0) {
            return null;
        }

        $total = TradeRequest::query()
            ->where(function ($query) {
                $query->where('sender_id', $this->id)
                    ->orWhere('receiver_id', $this->id);
            })
            ->count() + $this->trades()->count();

        $completionRate = $total > 0 ? round(($completed / $total) * 100) : 0;

        return match (true) {
            $completionRate >= 90 => 'Top Trader',
            $completionRate >= 70 => 'Trusted',
            $completionRate >= 50 => 'Active Trader',
            $completed >= 3 => 'Verified Seller',
            default => null,
        };
    }

    public function getAvatarUrlAttribute(): ?string
    {
        $path = $this->avatar && str_starts_with($this->avatar, 'avatars/')
            ? $this->avatar
            : ($this->avatar ? 'avatars/'.$this->avatar : null);

        return $path && Storage::disk('public')->exists($path)
            ? Storage::url($path)
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
