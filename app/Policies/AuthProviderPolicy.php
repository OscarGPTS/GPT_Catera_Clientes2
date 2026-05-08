<?php

namespace App\Policies;

use App\Models\AuthProvider;
use App\Models\User;

class AuthProviderPolicy
{
    public function link(User $user): bool
    {
        return true;
    }

    public function unlink(User $user, AuthProvider $provider): bool
    {
        return $user->id === $provider->user_id;
    }
}