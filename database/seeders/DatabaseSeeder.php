<?php

namespace Database\Seeders;

use App\Models\AuthProvider;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolesPermissionsSeeder::class,
            RhRoleMappingSeeder::class,
            SystemSettingsSeeder::class,
            CatalogosBaseSeeder::class,
        ]);
        $defaultPassword = bcrypt('password');

        $users = [
            ['name' => 'Super Admin',       'email' => 'admin@gptservices.com',              'role' => 'super_admin'],
            ['name' => 'Fernando Basave',   'email' => 'fernando.basave@gptservices.com',    'role' => 'gerente_proyectos', 'puesto' => 'Gerente de Proyectos',          'departamento' => 'Proyectos'],
            ['name' => 'Director DN',       'email' => 'director.dn@gptservices.com',        'role' => 'director_dn',       'puesto' => 'Director de Desarrollo de Negocios', 'departamento' => 'Comercial'],
            ['name' => 'Denisse Ramirez',   'email' => 'denisse@gptservices.com',            'role' => 'cfo',               'puesto' => 'CFO',                           'departamento' => 'Finanzas'],
            ['name' => 'Ingeniero Proyectos', 'email' => 'ingeniero@gptservices.com',        'role' => 'ingeniero_proyectos','puesto' => 'Ingeniero de Proyectos',        'departamento' => 'Proyectos'],
            ['name' => 'Socio GPT',         'email' => 'socio@gptservices.com',              'role' => 'socio',             'puesto' => 'Socio',                         'departamento' => 'Dirección', 'es_socio' => true],
            // Responsables de ofertas del CSV
            ['name' => 'Kevin Pérez',       'email' => 'kevin.perez@gptservices.com',        'role' => 'comercial',         'puesto' => 'Ejecutivo Comercial',           'departamento' => 'Comercial'],
            ['name' => 'Aquiles Garcia',    'email' => 'aquiles.garcia@gptservices.com',     'role' => 'comercial',         'puesto' => 'Ejecutivo Comercial',           'departamento' => 'Comercial'],
            ['name' => 'Sergio Ordaz',      'email' => 'sergio.ordaz@gptservices.com',       'role' => 'comercial',         'puesto' => 'Ejecutivo Comercial',           'departamento' => 'Comercial'],
            ['name' => 'Diego Renato',      'email' => 'diego.renato@gptservices.com',       'role' => 'comercial',         'puesto' => 'Ejecutivo Comercial',           'departamento' => 'Comercial'],
            ['name' => 'Guadalupe Osorio',  'email' => 'guadalupe.osorio@gptservices.com',   'role' => 'comercial',         'puesto' => 'Ejecutivo Comercial',           'departamento' => 'Comercial'],
        ];

        $created = [];
        foreach ($users as $data) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $defaultPassword,
                'puesto' => $data['puesto'] ?? null,
                'departamento' => $data['departamento'] ?? null,
                'es_socio' => $data['es_socio'] ?? false,
                'status' => 'active',
            ]);
            $user->assignRole($data['role']);

            AuthProvider::create([
                'user_id' => $user->id,
                'provider' => 'email_password',
                'password_hash' => $defaultPassword,
                'is_primary' => true,
            ]);

            $created[$data['email']] = $user;
        }

        // Importar catálogo maestro (customers, core_businesses, personnel,
        // countries, tech_references) desde Tech_Codification_2_0_clean.json.
        // Debe ir ANTES de Ofertas2026Seeder porque agrega clientes y users
        // adicionales que las ofertas pueden referenciar.
        $this->call(TechCodificationSeeder::class);

        // Poblar ofertas 2026 desde Status_ofertas_GPT_Services_2026.json
        $this->call(Ofertas2026Seeder::class);
    }
}
