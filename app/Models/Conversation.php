<?php

namespace App\Models;

use Database\Factories\ConversationFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Conversation extends Model
{
    /** @use HasFactory<ConversationFactory> */
    use HasFactory;

    protected $fillable = [
        'user_one_id',
        'user_two_id',
        'marketplace_listing_id',
    ];

    public function scopeBetweenParticipants(Builder $query, int $firstUserId, int $secondUserId): Builder
    {
        return $query
            ->where('user_one_id', $firstUserId)
            ->where('user_two_id', $secondUserId);
    }

    public function scopeForUser(Builder $query, User $user): Builder
    {
        return $query->where(function (Builder $nested) use ($user) {
            $nested->where('user_one_id', $user->id)
                ->orWhere('user_two_id', $user->id);
        });
    }

    public function scopeWithValidParticipants(Builder $query): Builder
    {
        return $query
            ->whereNotNull('user_one_id')
            ->whereNotNull('user_two_id')
            ->whereHas('userOne')
            ->whereHas('userTwo');
    }

    public function userOne(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_one_id');
    }

    public function userTwo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_two_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    public function latestMessage(): HasOne
    {
        return $this->hasOne(Message::class)->latestOfMany();
    }

    public function marketplaceListing(): BelongsTo
    {
        return $this->belongsTo(MarketplaceListing::class);
    }

    public function unreadMessagesFor(User $user): HasMany
    {
        return $this->messages()
            ->where('receiver_id', $user->id)
            ->whereNull('read_at');
    }

    public function otherParticipant(User $user): ?User
    {
        if ($this->user_one_id === $user->id) {
            return $this->userTwo;
        }

        if ($this->user_two_id === $user->id) {
            return $this->userOne;
        }

        return null;
    }
}
