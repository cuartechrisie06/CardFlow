<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CardVariant extends Model
{
    use HasFactory;

    protected $fillable = [
        'card_id',
        'variant_name',
        'variant_type',
        'image_url',
        'community_owned_count',
        'community_listed_count',
        'average_trade_value',
        'average_sale_price',
        'last_synced_at',
    ];

    protected function casts(): array
    {
        return [
            'community_owned_count' => 'integer',
            'community_listed_count' => 'integer',
            'average_trade_value' => 'decimal:2',
            'average_sale_price' => 'decimal:2',
            'last_synced_at' => 'datetime',
        ];
    }

    public function card(): BelongsTo
    {
        return $this->belongsTo(Card::class);
    }
}
