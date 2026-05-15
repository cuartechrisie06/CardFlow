<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserOnboarding extends Model
{
    protected $fillable = [
        'user_id',
        'added_first_card',
        'added_wishlist_item',
        'browsed_marketplace',
    ];

    protected function casts(): array
    {
        return [
            'added_first_card' => 'boolean',
            'added_wishlist_item' => 'boolean',
            'browsed_marketplace' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isComplete(): bool
    {
        return $this->added_first_card
            && $this->added_wishlist_item
            && $this->browsed_marketplace;
    }
}
