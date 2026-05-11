<?php

namespace App\Policies;

use App\Models\Chat\ChatMensaje;
use App\Models\User;

class ChatMensajePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, ChatMensaje $chatMensaje): bool
    {
        if ($user->esAdmin()) {
            return true;
        }

        return $chatMensaje->canal->miembros()->where('user_id', $user->id)->exists();
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, ChatMensaje $chatMensaje): bool
    {
        if ($user->esAdmin()) {
            return true;
        }

        return $chatMensaje->user_id === $user->id;
    }

    public function delete(User $user, ChatMensaje $chatMensaje): bool
    {
        if ($user->esAdmin()) {
            return true;
        }

        return $chatMensaje->user_id === $user->id;
    }
}
