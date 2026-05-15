<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Artist extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'name_original',
        'aliases',
        'group_type',
        'agency',
        'debut_date',
        'logo_url',
        'cover_url',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'aliases' => 'array',
            'debut_date' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public function albums(): HasMany
    {
        return $this->hasMany(Album::class);
    }

    public function cards(): HasMany
    {
        return $this->hasMany(Card::class);
    }

    public function cardAliases(): HasManyThrough
    {
        return $this->hasManyThrough(CardAlias::class, Card::class);
    }
}
