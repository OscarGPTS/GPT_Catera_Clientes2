<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Comercial\Cliente;

class ClientePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('ver proyectos')
            || $user->can('ver oportunidades')
            || $user->can('ver vista ejecutiva');
    }

    public function view(User $user, Cliente $cliente): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->can('editar proyecto')
            || $user->can('crear oportunidad')
            || $user->can('gestionar usuarios');
    }

    public function update(User $user, Cliente $cliente): bool
    {
        return $user->can('editar proyecto')
            || $user->can('crear oportunidad')
            || $user->can('gestionar usuarios');
    }

    public function delete(User $user, Cliente $cliente): bool
    {
        return $user->hasRole('super_admin') || $user->hasRole('direccion_general');
    }
}