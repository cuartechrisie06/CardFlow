<?php

namespace App\Services;

use App\Models\Activity;
use App\Models\User;

class ActivityLogger
{
    public function record(
        User $user,
        string $type,
        string $title,
        ?string $body = null,
        array $meta = []
    ): Activity {
        return Activity::query()->create([
            'user_id' => $user->id,
            'type' => $type,
            'title' => $title,
            'body' => $body,
            'happened_at' => now(),
            'meta' => $meta ?: null,
        ]);
    }
}
