<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RhRoleMappingSeeder extends Seeder
{
    public function run(): void
    {
        $rules = [
            ['puesto_rh' => '%Director General%', 'rol_sistema' => 'direccion_general', 'prioridad' => 100],
            ['puesto_rh' => '%Director%Desarrollo de Negocios%', 'rol_sistema' => 'director_dn', 'prioridad' => 90],
            ['puesto_rh' => '%Gerente de Proyectos%', 'rol_sistema' => 'gerente_proyectos', 'prioridad' => 80],
            ['puesto_rh' => '%Gerente de Operaciones%', 'rol_sistema' => 'gerente_operaciones', 'prioridad' => 80],
            ['puesto_rh' => '%Ingeniero de Costos%', 'rol_sistema' => 'ingeniero_costos', 'prioridad' => 70],
            ['puesto_rh' => '%Ingeniero de Proyectos%', 'rol_sistema' => 'ingeniero_proyectos', 'prioridad' => 60],
            ['puesto_rh' => '%Trainee%', 'rol_sistema' => 'trainee_proyectos', 'prioridad' => 50],
            ['puesto_rh' => '%Becario%', 'rol_sistema' => 'trainee_proyectos', 'prioridad' => 50],
            ['puesto_rh' => '%CFO%', 'rol_sistema' => 'cfo', 'prioridad' => 90],
            ['puesto_rh' => '%Comercial%', 'rol_sistema' => 'comercial', 'prioridad' => 60],
        ];

        foreach ($rules as $rule) {
            DB::table('rh_role_mapping')->insert(array_merge($rule, [
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }
}
