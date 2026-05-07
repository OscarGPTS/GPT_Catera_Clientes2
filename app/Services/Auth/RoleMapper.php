<?php

namespace App\Services\Auth;

use App\Models\RhRoleMapping;
use App\Models\User;

class RoleMapper
{
    public function resolveRoleForUser(User $user): string
    {
        if (empty($user->puesto)) {
            return 'ingeniero_proyectos';
        }

        $rules = RhRoleMapping::activo()->porPrioridad()->get();

        foreach ($rules as $rule) {
            $pattern = str_replace('%', '', $rule->puesto_rh);
            if (empty($pattern)) {
                continue;
            }

            if (stripos($user->puesto, $pattern) !== false) {
                if ($rule->departamento_filter && $rule->departamento_filter !== $user->departamento) {
                    continue;
                }

                return $rule->rol_sistema;
            }
        }

        return 'ingeniero_proyectos';
    }
}
