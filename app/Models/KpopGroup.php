<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KpopGroup extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'debut_date' => 'date',
            'member_count' => 'integer',
        ];
    }
}
