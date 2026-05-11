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

        // 20 ofertas reales 2026 — fuente: Status_Ofertas_2026.md (montos en USD)
        $fernando = $created['fernando.basave@gptservices.com'] ?? null;
        $director  = $created['director.dn@gptservices.com'] ?? null;

        $cli = fn($alias) => \App\Models\Comercial\Cliente::where('alias_3letras', $alias)->first();
        $sub = fn($codigo) => \Illuminate\Support\Facades\DB::table('sublineas')->where('codigo', $codigo)->value('id');

        // ponderacion: 10=REMOTO, 25=POSIBLE, 50=PROBABLE, 75=CASI PROBABLE, 100=CONTRATADO
        $ofertas = [
            // [cp,        tech_reference,                                                                    anio, cliente, sublinea, usuario_final,                             sector,            estado,                fecha,        monto_usd,   pond]
            ['CP-152/25', '251006-0-IGA-HTS 30x4 _Interconexion 30x4 600# Oleofinos',                      2025, 'IGA', 'HTS',  'CENAGAS / IGASAMEX Veracruz',             'Gasoductos',     'adjudicado_pendiente','2025-11-18', 36566.30,    75],
            ['CP-157/25', '251027-1-ENG-HTP 42x24 _VDR',                                                    2025, 'ENG', 'HTP',  'ENGIE / Gasoducto VDR San Luis Potosí',   'Gasoductos',     'presentado',          '2026-02-23', 933232.00,   25],
            ['CP-001/26', '260106-0-AEP-HTS 30x20 _HT 30x20 150# Cactus',                                  2026, 'PIR', 'HTS',  'PIR System / Campo Cactus-Reforma',       'Oil & Gas',      'presentado',          '2026-01-14', 103000.00,   10],
            ['CP-002/26', '260126-0-NAT-VRS _Suministro anillos separadores Naturgy',                       2026, 'NAT', 'OTH',  'Naturgy México',                          'Gasoductos',     'presentado',          '2026-01-26', 63500.00,    50],
            ['CP-003/26', '260122-0-IGA-VLV VRSx _Suministro de VCP Dif. Diametros',                       2026, 'IGA', 'VLV',  'IGASAMEX / CENAGAS',                      'Gasoductos',     'presentado',          '2026-01-22', 29153.80,    10],
            ['CP-004/26', '260205-0-PTX-VLV x _Suministro Valvulas Proyecto Cluster 2-SEJKAN',              2026, 'PTX', 'VLV',  'Protexa / Pemex Exploración Cluster 2',   'Oil & Gas',      'presentado',          '2026-02-05', 1721420.00,  10],
            ['CP-005/26', '260127-0-EIN-DLSS 6x6 _Servicio DLS 6 600#',                                    2026, 'EIN', 'DLSS', 'Euroinova / Gasoducto Hidalgo',            'Gasoductos',     'presentado',          '2026-01-28', 121162.00,   10],
            ['CP-016/26', '260127-1-MOL-DLSS 6x6 _Servicio DLS 6 Rev1 Hidalgo',                            2026, 'MOL', 'DLSS', 'Molper / Grupo Biomedical Hidalgo',        'Gasoductos',     'presentado',          '2026-01-28', 2976740.00,  25],
            ['CP-006/26', '250128-0-SER-HTP 24x16 _HTP 24x16 SUBMARINO',                                   2025, 'SRP', 'HTP',  'Serport / Colombia Offshore',              'Oil & Gas',      'presentado',          '2026-01-29', 351900.00,   10],
            ['CP-007/26', '260122-0-GCI-HTS 8x8 _TIE IN TP550-0091 Hot Tap 8 Nafta',                       2026, 'GCI', 'HTS',  'GCI / Refinería Hidalgo',                  'Refinación',     'presentado',          '2026-01-28', 9004.00,     25],
            ['CP-008/26', '260122-0-ICA-HTSF 24x24 _Macro libramiento 16 de sep Naucalpan',                 2026, 'ICA', 'HTSF', 'ICA / Macro Libramiento Naucalpan',        'Infraestructura','presentado',          '2026-01-28', 1044900.00,  25],
            ['CP-009/26', '260203-0-SIC-HTSF 30x20 _Servicios Hot Tap 30x20 600#',                         2026, 'SIC', 'HTSF', 'Sicim / EMRyC Aguascalientes',              'Gasoductos',     'presentado',          '2026-02-04', 256170.00,   10],
            ['CP-010/26', '260130-0-COP-OTH x _Suministro Juntas Dielectricas',                             2026, 'COP', 'OTH',  'COPC / Estado de México',                  'Industrial',     'presentado',          '2026-01-30', 5000.00,     10],
            ['CP-011/26', '260205-0-IGC-PC x _Separador Horizontal Gas de Desfogue BAKTE',                  2026, 'IGC', 'P&C',  'INDHECA / Campo BAKTE Tabasco',             'Oil & Gas',      'presentado',          '2026-02-05', 375793.00,   25],
            ['CP-012/26', '260209-0-SAR-HTP 2x2 _Drillings 2 Niple COSASCO Sarreal',                       2026, 'SAR', 'HTP',  'Sarreal / Tabasco',                        'Oil & Gas',      'presentado',          '2026-02-11', 44615.40,    25],
            ['CP-013/26', '260211-0-ARS-VLV 10x8 _Suministro Valvulas bola Trunnion 8 y10 600#',           2026, 'ARS', 'VLV',  'Arseal / Guanajuato',                      'Gasoductos',     'presentado',          '2026-02-11', 68542.00,    10],
            ['CP-014/26', '260216-0-FER-DLSS 36x _DLS 36 600# Gasoducto Villa de Reyes Guadalajara',       2026, 'ESE', 'DLSS', 'Esentia / Gasoducto Villa de Reyes GDL',   'Gasoductos',     'presentado',          '2026-02-18', 1260000.00,  25],
            ['CP-015/26', '260219-0-FER-HTP 36x8 _Hot Tap 8 y 2 GCC-Samalayuca',                           2026, 'ESE', 'HTP',  'Esentia / GCC-Samalayuca Chihuahua',        'Gasoductos',     'presentado',          '2026-02-19', 63191.20,    25],
            ['CP-017/26', '260216-SDN-HTSF 24x _Desvio Ducto Frente 10 Tren Mexico-Queretaro',              2026, 'SDN', 'HTSF', 'SEDENA / Frente 10 Tren México-Querétaro', 'Infraestructura','presentado',          '2026-02-16', 8891800.00,  25],
            ['CP-018/26', '260220-SDN-HTSF 24x _Desvio Ducto Frente 11 Tren Mexico-Queretaro',              2026, 'SDN', 'HTSF', 'SEDENA / Frente 11 Tren México-Querétaro', 'Infraestructura','presentado',          '2026-02-20', 36375400.00, 25],
        ];

        foreach ($ofertas as [$cp, $techRef, $anio, $clienteAlias, $sublCodigo, $usuarioFinal, $sector, $estado, $fechaEmision, $monto, $ponderacion]) {
            $cliente    = $cli($clienteAlias);
            $sublineaId = $sub($sublCodigo);
            if (!$cliente || !$sublineaId) continue;

            $proyecto = \App\Models\Proyectos\Proyecto::create([
                'cp_numero'      => $cp,
                'tech_reference' => $techRef,
                'anio'           => $anio,
                'cliente_id'     => $cliente->id,
                'sublinea_id'    => $sublineaId,
                'usuario_final'  => $usuarioFinal,
                'sector'         => $sector,
                'estado'         => $estado,
                'ponderacion'    => $ponderacion,
                'director_dn_id' => $director?->id,
                'notas'          => "Oferta enviada — Status: ENVIADO",
            ]);

            \App\Models\Proyectos\Cotizacion::create([
                'proyecto_id'        => $proyecto->id,
                'version'            => 1,
                'precio_venta_final' => $monto,
                'moneda'             => 'USD',
                'status'             => 'presentado',
                'generado_por'       => $fernando?->id,
                'fecha_emision'      => $fechaEmision,
            ]);
        }
    }
}
