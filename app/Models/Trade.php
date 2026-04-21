<?php

namespace App\Models;

use Database\Factories\TradeFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Trade extends Model
{
    /** @use HasFactory<TradeFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'card_id',
        'partner_name',
        'partner_handle',
        'status',
        'offered_value',
        'replied_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'offered_value' => 'decimal:2',
            'replied_at' => 'datetime',
            'completed_at' => 'datetime',
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
