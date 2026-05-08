<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesPermisosMatrix extends Component
{
    public function getRolesProperty()
    {
        return Role::with('permissions')->orderBy('name')->get();
    }

    public function getPermissionsProperty()
    {
        return Permission::orderBy('name')->get();
    }

    public function getGroupedPermissionsProperty()
    {
        return Permission::orderBy('name')->get()->groupBy(function ($perm) {
            $parts = explode(' ', $perm->name, 2);
            return $parts[0];
        });
    }

    public function hasPermission(int $roleId, string $permissionName): bool
    {
        static $cache = [];

        $key = "{$roleId}:{$permissionName}";
        if (isset($cache[$key])) {
            return $cache[$key];
        }

        $role = Role::find($roleId);
        $result = $role && $role->hasPermissionTo($permissionName);
        $cache[$key] = $result;

        return $result;
    }

    public function render()
    {
        return view('livewire.admin.roles-permisos-matrix', [
            'roles' => $this->roles,
            'permissions' => $this->permissions,
            'groupedPermissions' => $this->groupedPermissions,
        ]);
    }
}