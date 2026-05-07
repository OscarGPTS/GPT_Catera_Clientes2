<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'ver dashboard', 'ver oportunidades', 'crear oportunidad', 'editar oportunidad',
            'aprobar cp', 'asignar cp', 'ver proyectos', 'editar proyecto', 'eliminar proyecto',
            'ver cotizaciones', 'crear cotizacion', 'editar cotizacion', 'aprobar cotizacion',
            'ver ficha proyecto', 'crear ficha proyecto',
            'adjudicar proyecto', 'emitir dn', 'crear minuta', 'firmar minuta',
            'ver libro proyecto', 'editar libro proyecto', 'cerrar libro proyecto',
            'ver kom', 'programar kom', 'ver cronograma', 'editar cronograma',
            'ver bom boe', 'editar bom boe', 'ver suministros', 'editar suministros',
            'ver bitacora', 'crear bitacora', 'firmar bitacora',
            'ver reporte semanal', 'crear reporte semanal', 'enviar reporte semanal',
            'solicitar viaticos', 'aprobar viaticos servicios generales', 'aprobar viaticos direccion',
            'crear carta finiquito', 'firmar carta finiquito',
            'crear post mortem', 'ver post mortem',
            'ver asignaciones', 'generar snapshot asignaciones',
            'ver finanzas', 'editar finanzas', 'cargar estado cuenta', 'conciliar movimientos',
            'ver cierres', 'generar cierre sat', 'generar cierre gerencial', 'aprobar cierre',
            'ver vista ejecutiva',
            'ver admin usuarios', 'gestionar usuarios', 'invitar usuario externo',
            'ver admin socios', 'gestionar socios', 'gestionar rh mapping',
            'ver roles permisos', 'gestionar permisos',
            'ver chat', 'enviar mensaje', 'crear canal',
        ];

        foreach ($permissions as $perm) {
            Permission::create(['name' => $perm, 'guard_name' => 'web']);
        }

        $roles = [
            'super_admin' => $permissions,

            'direccion_general' => $permissions,

            'socio' => [
                'ver dashboard', 'ver vista ejecutiva', 'ver finanzas',
                'ver cierres', 'ver proyectos', 'ver asignaciones',
                'ver chat', 'enviar mensaje',
            ],

            'comite_socios' => [
                'ver dashboard', 'ver vista ejecutiva', 'ver finanzas',
                'ver cierres', 'aprobar cierre', 'ver proyectos',
                'ver chat', 'enviar mensaje',
            ],

            'director_dn' => [
                'ver dashboard', 'ver oportunidades', 'crear oportunidad', 'editar oportunidad',
                'aprobar cp', 'ver proyectos', 'ver cotizaciones', 'aprobar cotizacion',
                'adjudicar proyecto', 'emitir dn', 'crear minuta', 'firmar minuta',
                'ver vista ejecutiva', 'ver chat', 'enviar mensaje',
            ],

            'comercial' => [
                'ver dashboard', 'ver oportunidades', 'crear oportunidad', 'editar oportunidad',
                'ver proyectos', 'ver cotizaciones', 'ver chat', 'enviar mensaje',
            ],

            'gerente_proyectos' => [
                'ver dashboard', 'ver oportunidades', 'ver proyectos', 'editar proyecto',
                'aprobar cp', 'asignar cp', 'ver cotizaciones', 'aprobar cotizacion',
                'ver ficha proyecto', 'crear ficha proyecto',
                'adjudicar proyecto', 'emitir dn',
                'ver libro proyecto', 'editar libro proyecto', 'cerrar libro proyecto',
                'ver kom', 'programar kom', 'ver cronograma', 'editar cronograma',
                'ver bom boe', 'editar bom boe', 'ver suministros',
                'ver bitacora', 'ver reporte semanal', 'enviar reporte semanal',
                'crear carta finiquito', 'firmar carta finiquito',
                'crear post mortem', 'ver post mortem',
                'ver asignaciones', 'generar snapshot asignaciones',
                'ver vista ejecutiva', 'ver chat', 'enviar mensaje', 'crear canal',
                'ver finanzas',
            ],

            'ingeniero_costos' => [
                'ver dashboard', 'ver oportunidades', 'ver proyectos',
                'ver cotizaciones', 'crear cotizacion', 'editar cotizacion',
                'ver ficha proyecto', 'crear ficha proyecto',
                'ver asignaciones', 'ver chat', 'enviar mensaje',
            ],

            'ingeniero_proyectos' => [
                'ver dashboard', 'ver oportunidades', 'ver proyectos',
                'ver cotizaciones', 'crear cotizacion',
                'ver libro proyecto', 'editar libro proyecto',
                'ver kom', 'programar kom', 'ver cronograma',
                'ver bom boe', 'ver suministros',
                'ver bitacora', 'crear bitacora',
                'ver reporte semanal', 'crear reporte semanal',
                'solicitar viaticos',
                'crear post mortem', 'ver post mortem',
                'ver asignaciones', 'ver chat', 'enviar mensaje',
            ],

            'trainee_proyectos' => [
                'ver dashboard', 'ver oportunidades', 'ver proyectos',
                'ver cotizaciones', 'ver ficha proyecto',
                'ver libro proyecto', 'ver kom', 'ver cronograma',
                'ver bom boe', 'ver suministros',
                'ver bitacora', 'ver reporte semanal',
                'ver chat', 'enviar mensaje',
            ],

            'gerente_operaciones' => [
                'ver dashboard', 'ver oportunidades', 'ver proyectos', 'editar proyecto',
                'ver cotizaciones', 'ver ficha proyecto',
                'ver libro proyecto', 'editar libro proyecto',
                'ver kom', 'programar kom', 'ver cronograma', 'editar cronograma',
                'ver bom boe', 'editar bom boe', 'ver suministros', 'editar suministros',
                'aprobar viaticos servicios generales',
                'ver asignaciones', 'ver chat', 'enviar mensaje',
            ],

            'serv_tecnicos' => [
                'ver dashboard', 'ver proyectos', 'ver cronograma',
                'ver bom boe', 'ver suministros', 'ver bitacora',
                'ver chat', 'enviar mensaje',
            ],

            'soldadura' => [
                'ver dashboard', 'ver proyectos', 'ver cronograma',
                'ver bom boe', 'ver suministros', 'ver bitacora',
                'ver chat', 'enviar mensaje',
            ],

            'serv_generales' => [
                'ver dashboard', 'ver proyectos',
                'solicitar viaticos', 'aprobar viaticos servicios generales',
                'ver chat', 'enviar mensaje',
            ],

            'qhse' => [
                'ver dashboard', 'ver proyectos', 'ver libro proyecto', 'editar libro proyecto',
                'ver bitacora', 'ver chat', 'enviar mensaje',
            ],

            'almacen' => [
                'ver dashboard', 'ver proyectos', 'ver bom boe', 'editar bom boe',
                'ver suministros', 'ver chat', 'enviar mensaje',
            ],

            'manufactura' => [
                'ver dashboard', 'ver proyectos', 'ver bom boe', 'editar bom boe',
                'ver chat', 'enviar mensaje',
            ],

            'compras' => [
                'ver dashboard', 'ver proyectos', 'ver bom boe', 'editar bom boe',
                'ver suministros', 'editar suministros', 'ver chat', 'enviar mensaje',
            ],

            'ingenieria_diseño' => [
                'ver dashboard', 'ver proyectos', 'ver libro proyecto', 'editar libro proyecto',
                'ver bom boe', 'editar bom boe', 'ver chat', 'enviar mensaje',
            ],

            'cfo' => [
                'ver dashboard', 'ver finanzas', 'editar finanzas',
                'cargar estado cuenta', 'conciliar movimientos',
                'ver cierres', 'generar cierre sat', 'generar cierre gerencial', 'aprobar cierre',
                'ver proyectos', 'ver vista ejecutiva', 'ver chat', 'enviar mensaje',
            ],

            'analista_financiero' => [
                'ver dashboard', 'ver finanzas', 'cargar estado cuenta', 'conciliar movimientos',
                'ver cierres', 'ver proyectos', 'ver chat', 'enviar mensaje',
            ],

            'finanzas_general' => [
                'ver dashboard', 'ver finanzas', 'ver cierres', 'ver proyectos',
                'ver chat', 'enviar mensaje',
            ],

            'cliente_externo' => [
                'ver proyectos', 'ver chat', 'enviar mensaje',
            ],

            'auditor_externo' => [
                'ver proyectos', 'ver libro proyecto', 'ver bitacora',
                'ver cierres', 'ver finanzas',
            ],
        ];

        foreach ($roles as $roleName => $perms) {
            $role = Role::create(['name' => $roleName, 'guard_name' => 'web']);
            $role->syncPermissions($perms);
        }
    }
}
