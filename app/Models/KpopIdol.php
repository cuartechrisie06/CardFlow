<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KpopIdol extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'debut_date' => 'date',
            'birth_date' => 'date',
            'height' => 'integer',
        ];
    }
}
