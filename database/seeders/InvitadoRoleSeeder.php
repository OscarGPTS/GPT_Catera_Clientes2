<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class InvitadoRoleSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Solo permisos de lectura: ningún crear/editar/eliminar/importar/exportar/aprobar.
        $permisosLectura = [
            'ver dashboard',
            'ver oportunidades',
            'ver proyectos',
            'ver cotizaciones',
            'ver ficha proyecto',
            'ver libro proyecto',
            'ver kom',
            'ver cronograma',
            'ver bom boe',
            'ver suministros',
            'ver bitacora',
            'ver reporte semanal',
            'ver post mortem',
            'ver asignaciones',
            'ver finanzas',
            'ver cierres',
            'ver vista ejecutiva',
            'ver chat',
        ];

        foreach ($permisosLectura as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        $role = Role::firstOrCreate(['name' => 'invitado', 'guard_name' => 'web']);
        $role->syncPermissions($permisosLectura);
    }
}
