<?php

namespace App\Policies;

use App\Models\Chat\ChatCanal;
use App\Models\User;

class ChatCanalPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, ChatCanal $chatCanal): bool
    {
        if ($user->esAdmin()) {
            return true;
        }

        return $chatCanal->miembros()->where('user_id', $user->id)->exists();
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, ChatCanal $chatCanal): bool
    {
        if ($user->esAdmin()) {
            return true;
        }

        return $chatCanal->creado_por_id === $user->id;
    }

    public function delete(User $user, ChatCanal $chatCanal): bool
    {
        return $user->esAdmin();
    }

    public function manageMembers(User $user, ChatCanal $chatCanal): bool
    {
        if ($user->esAdmin()) {
            return true;
        }

        return $chatCanal->creado_por_id === $user->id;
    }
}
