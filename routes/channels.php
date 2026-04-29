<?php

use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('conversation.{conversationId}', function (User $user, int $conversationId) {
    $conversation = \App\Models\Conversation::query()
        ->withValidParticipants()
        ->find($conversationId);

    if (! $conversation) {
        return false;
    }

    if (! in_array($user->id, [$conversation->user_one_id, $conversation->user_two_id], true)) {
        return false;
    }

    return [
        'id' => $user->id,
        'name' => $user->name,
        'username' => $user->username,
    ];
});

Broadcast::channel('users.{id}.inbox', function (User $user, int $id) {
    return $user->id === $id;
});
