<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdminAction extends Model
{
    protected $fillable = [
        'admin_id',
        'action_type',
        'target_type',
        'target_id',
        'target_label',
        'description',
    ];

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function scopeByAdmin(Builder $query, int $adminId): Builder
    {
        return $query->where('admin_id', $adminId);
    }

    public function scopeRecent(Builder $query): Builder
    {
        return $query->latest()->take(10);
    }

    public static function log(string $type, ?Model $target = null, ?string $label = null, ?string $description = null, ?string $targetType = null): ?self
    {
        if (! auth()->id()) {
            return null;
        }

        $resolvedType = $targetType ?? match (true) {
            $target instanceof MarketplaceListing => 'listing',
            $target instanceof User => 'user',
            $target instanceof Setting => 'setting',
            $target instanceof Card => 'catalog_card',
            default => 'record',
        };

        $resolvedLabel = $label
            ?? ($target instanceof User ? '@'.$target->username : null)
            ?? ($target instanceof MarketplaceListing ? ($target->card?->title ?? 'Listing #'.$target->id) : null)
            ?? ($target instanceof Card ? $target->title : null)
            ?? 'Record';

        return static::query()->create([
            'admin_id' => auth()->id(),
            'action_type' => $type,
            'target_type' => $resolvedType,
            'target_id' => $target?->getKey(),
            'target_label' => $resolvedLabel,
            'description' => $description ?? str_replace('_', ' ', ucfirst($type)).' '.$resolvedLabel,
        ]);
    }
}
