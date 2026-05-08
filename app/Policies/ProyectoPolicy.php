<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Proyectos\Proyecto;

class ProyectoPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('ver proyectos');
    }

    public function view(User $user, Proyecto $proyecto): bool
    {
        if ($user->can('ver proyectos')) {
            return true;
        }

        return $user->can('ver oportunidades')
            && in_array($proyecto->estado, ['en_revision', 'cotizando', 'cotizado', 'presentado']);
    }

    public function create(User $user): bool
    {
        return $user->can('crear oportunidad');
    }

    public function update(User $user, Proyecto $proyecto): bool
    {
        return $user->can('editar proyecto');
    }

    public function delete(User $user, Proyecto $proyecto): bool
    {
        return $user->can('eliminar proyecto');
    }

    public function aprobarCp(User $user, Proyecto $proyecto): bool
    {
        return $user->can('aprobar cp');
    }

    public function asignarEquipo(User $user, Proyecto $proyecto): bool
    {
        return $user->can('asignar cp');
    }

    public function adjudicar(User $user, Proyecto $proyecto): bool
    {
        return $user->can('adjudicar proyecto');
    }

    public function emitirDn(User $user, Proyecto $proyecto): bool
    {
        return $user->can('emitir dn');
    }

    public function verCotizacion(User $user, Proyecto $proyecto): bool
    {
        return $user->can('ver cotizaciones');
    }

    public function crearCotizacion(User $user, Proyecto $proyecto): bool
    {
        return $user->can('crear cotizacion');
    }

    public function crearMinuta(User $user, Proyecto $proyecto): bool
    {
        return $user->can('crear minuta');
    }

    public function firmarMinuta(User $user, Proyecto $proyecto): bool
    {
        return $user->can('firmar minuta');
    }

    public function verLibro(User $user, Proyecto $proyecto): bool
    {
        return $user->can('ver libro proyecto');
    }
}