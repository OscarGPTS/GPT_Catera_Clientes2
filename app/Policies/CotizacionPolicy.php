<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Proyectos\Cotizacion;

class CotizacionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('ver cotizaciones');
    }

    public function view(User $user, Cotizacion $cotizacion): bool
    {
        return $user->can('ver cotizaciones');
    }

    public function create(User $user): bool
    {
        return $user->can('crear cotizacion');
    }

    public function update(User $user, Cotizacion $cotizacion): bool
    {
        return $user->can('editar cotizacion');
    }

    public function aprobar(User $user, Cotizacion $cotizacion): bool
    {
        return $user->can('aprobar cotizacion');
    }
}