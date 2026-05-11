<?php

namespace Database\Seeders;

use App\Models\AuthProvider;
use App\Models\Comercial\Cliente;
use App\Models\Proyectos\Proyecto;
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
            ['name' => 'Super Admin', 'email' => 'admin@gptservices.com', 'role' => 'super_admin'],
            ['name' => 'Fernando Basave', 'email' => 'fernando.basave@gptservices.com', 'role' => 'gerente_proyectos', 'puesto' => 'Gerente de Proyectos', 'departamento' => 'Proyectos'],
            ['name' => 'Director DN', 'email' => 'director.dn@gptservices.com', 'role' => 'director_dn', 'puesto' => 'Director de Desarrollo de Negocios', 'departamento' => 'Comercial'],
            ['name' => 'Denisse Ramirez', 'email' => 'denisse@gptservices.com', 'role' => 'cfo', 'puesto' => 'CFO', 'departamento' => 'Finanzas'],
            ['name' => 'Ingeniero Proyectos', 'email' => 'ingeniero@gptservices.com', 'role' => 'ingeniero_proyectos', 'puesto' => 'Ingeniero de Proyectos', 'departamento' => 'Proyectos'],
            ['name' => 'Socio GPT', 'email' => 'socio@gptservices.com', 'role' => 'socio', 'puesto' => 'Socio', 'departamento' => 'Dirección', 'es_socio' => true],
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

        // Sample proyectos
        $iga = Cliente::where('alias_3letras', 'IGA')->first();
        $sdn = Cliente::where('alias_3letras', 'SDN')->first();
        $pmx = Cliente::where('alias_3letras', 'PMX')->first();
        $fernando = $created['fernando.basave@gptservices.com'] ?? null;
        $director = $created['director.dn@gptservices.com'] ?? null;
        $ingeniero = $created['ingeniero@gptservices.com'] ?? null;

        if ($iga && $sdn && $pmx) {
            Proyecto::create([
                'cp_numero' => 'CP-003/25', 'tech_reference' => '250121-0-IGA-HTP x _HT 30x10 Texmelucan',
                'anio' => 2025, 'cliente_id' => $iga->id, 'sublinea_id' => 1,
                'usuario_final' => 'CENAGAS', 'sector' => 'Oil & Gas',
                'estado' => 'en_ejecucion',
                'gerente_proyectos_id' => $fernando?->id,
                'ingeniero_proyectos_id' => $ingeniero?->id,
                'notas' => 'Hot Tap 30x10 Texmelucan. Caso de validación.',
            ]);

            Proyecto::create([
                'cp_numero' => 'CP-005/25', 'anio' => 2025,
                'cliente_id' => $sdn->id, 'sublinea_id' => 2,
                'usuario_final' => 'SEDENA', 'sector' => 'Defensa',
                'estado' => 'cotizando',
                'director_dn_id' => $director?->id,
                'gerente_proyectos_id' => $fernando?->id,
                'notas' => 'Line Stopping SEDENA. Pendiente cotización.',
            ]);

            Proyecto::create([
                'cp_numero' => 'CP-007/25', 'anio' => 2025,
                'cliente_id' => $pmx->id, 'sublinea_id' => 3,
                'usuario_final' => 'PEMEX Refinación', 'sector' => 'Oil & Gas',
                'estado' => 'presentado',
                'director_dn_id' => $director?->id,
                'notas' => 'Servicio de válvulas PEMEX. Cotización presentada.',
            ]);

            Proyecto::create([
                'cp_numero' => 'CP-010/25', 'anio' => 2025,
                'cliente_id' => $iga->id, 'sublinea_id' => 1,
                'usuario_final' => 'CENAGAS', 'sector' => 'Oil & Gas',
                'estado' => 'en_revision',
                'director_dn_id' => $director?->id,
                'notas' => 'Hot Tap. En revisión Comité Comercial.',
            ]);

            Proyecto::create([
                'cp_numero' => 'CP-012/25', 'anio' => 2025,
                'cliente_id' => $sdn->id, 'sublinea_id' => 4,
                'usuario_final' => 'SEDENA', 'sector' => 'Defensa',
                'estado' => 'adjudicado_firmado', 'dn_numero' => 'DN-001/25',
                'director_dn_id' => $director?->id,
                'gerente_proyectos_id' => $fernando?->id,
                'ingeniero_proyectos_id' => $ingeniero?->id,
                'notas' => 'Soldadura SEDENA adjudicado.',
            ]);
        }
    }
}
