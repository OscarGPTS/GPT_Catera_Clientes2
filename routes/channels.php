<?php

use App\Models\Chat\ChatCanal;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('chat.canal.{id}', function ($user, $id) {
    if ($user->esAdmin()) {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'avatar' => strtoupper(substr($user->name ?? 'U', 0, 2)),
        ];
    }

    $canal = ChatCanal::find($id);
    if ($canal && $canal->miembros()->where('user_id', $user->id)->exists()) {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'avatar' => strtoupper(substr($user->name ?? 'U', 0, 2)),
        ];
    }

    return false;
});
