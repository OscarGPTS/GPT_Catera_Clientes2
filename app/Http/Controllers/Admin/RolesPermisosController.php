<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesPermisosController extends Controller
{
    public function index()
    {
        $roles = Role::with('permissions')->orderBy('name')->get();
        $permissions = Permission::orderBy('name')->get();

        $matrix = [];
        foreach ($roles as $role) {
            $matrix[$role->name] = $permissions->mapWithKeys(function ($perm) use ($role) {
                return [$perm->name => $role->hasPermissionTo($perm->name)];
            })->toArray();
        }

        $groupedPermissions = $permissions->groupBy(function ($perm) {
            $parts = explode(' ', $perm->name, 2);
            return $parts[0];
        });

        return view('admin.roles-permisos', compact('roles', 'permissions', 'matrix', 'groupedPermissions'));
    }
}