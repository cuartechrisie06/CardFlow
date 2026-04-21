<?php

namespace App\Models;

use Database\Factories\UserCardFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
        'is_for_trade',
        'photo_path',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'purchase_price' => 'decimal:2',
            'estimated_value' => 'decimal:2',
            'acquired_at' => 'datetime',
            'is_for_trade' => 'boolean',
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
}
