<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('ver admin usuarios');
    }

    public function view(User $user, User $targetUser): bool
    {
        if ($user->id === $targetUser->id) {
            return true;
        }

        return $user->can('ver admin usuarios');
    }

    public function create(User $user): bool
    {
        return $user->can('gestionar usuarios');
    }

    public function update(User $user, User $targetUser): bool
    {
        return $user->can('gestionar usuarios');
    }

    public function delete(User $user, User $targetUser): bool
    {
        return $user->can('gestionar usuarios') && $user->id !== $targetUser->id;
    }

    public function manageRoles(User $user): bool
    {
        return $user->can('gestionar permisos');
    }

    public function inviteExternal(User $user): bool
    {
        return $user->can('invitar usuario externo');
    }

    public function suspend(User $user): bool
    {
        return $user->can('gestionar usuarios');
    }
}