<?php

namespace App\Policies;

use App\Models\User;
use App\Models\UserCard;

class UserCardPolicy
{
    public function view(?User $user, UserCard $userCard): bool
    {
        return $userCard->isVisibleTo($user);
    }

    public function update(User $user, UserCard $userCard): bool
    {
        return $user->id === $userCard->user_id;
    }

    public function delete(User $user, UserCard $userCard): bool
    {
        return $user->id === $userCard->user_id;
    }
}
