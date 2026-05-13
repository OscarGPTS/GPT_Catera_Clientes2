<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CatalogosBaseSeeder extends Seeder
{
    public function run(): void
    {
        // ── Lugares geográficos (estados, ciudades, países) ──────────────────────
        $lugares = [
            // México - Estados
            ['nombre' => 'Aguascalientes',   'tipo' => 'estado',    'pais' => 'México'],
            ['nombre' => 'Campeche',         'tipo' => 'estado',    'pais' => 'México'],
            ['nombre' => 'Chiapas',          'tipo' => 'estado',    'pais' => 'México'],
            ['nombre' => 'Chihuahua',        'tipo' => 'estado',    'pais' => 'México'],
            ['nombre' => 'CDMX',             'tipo' => 'estado',    'pais' => 'México'],
            ['nombre' => 'Edo. Mexico',      'tipo' => 'estado',    'pais' => 'México'],
            ['nombre' => 'Guanajuato',       'tipo' => 'estado',    'pais' => 'México'],
            ['nombre' => 'Hidalgo',          'tipo' => 'estado',    'pais' => 'México'],
            ['nombre' => 'Jalisco',          'tipo' => 'estado',    'pais' => 'México'],
            ['nombre' => 'Mexico',           'tipo' => 'estado',    'pais' => 'México'],
            ['nombre' => 'Queretaro',        'tipo' => 'estado',    'pais' => 'México'],
            ['nombre' => 'San Luis Potosi',  'tipo' => 'estado',    'pais' => 'México'],
            ['nombre' => 'Tabasco',          'tipo' => 'estado',    'pais' => 'México'],
            ['nombre' => 'Veracruz',         'tipo' => 'estado',    'pais' => 'México'],
            // México - Ciudades / Municipios
            ['nombre' => 'Cd. Del Carmen',   'tipo' => 'ciudad',    'pais' => 'México'],
            ['nombre' => 'Guadalajara',      'tipo' => 'ciudad',    'pais' => 'México'],
            // Países extranjeros
            ['nombre' => 'Colombia',         'tipo' => 'pais',      'pais' => 'Colombia'],
        ];

        foreach ($lugares as $lugar) {
            DB::table('lugares')->insertOrIgnore(array_merge($lugar, [
                'created_at' => now(), 'updated_at' => now(),
            ]));
        }


        $sublineas = [
            ['codigo' => 'HTS',  'nombre' => 'Hot Tapping Service',               'descripcion' => 'Servicio de Hot Tapping (Perforación en línea viva)'],
            ['codigo' => 'HTF',  'nombre' => 'Hot Tapping Fitting',               'descripcion' => 'Accesorios para Hot Tapping (Tees, conexiones)'],
            ['codigo' => 'HTSF', 'nombre' => 'Hot Tapping Service & Fitting',     'descripcion' => 'Servicio de Hot Tapping + Accesorios'],
            ['codigo' => 'HTP',  'nombre' => 'Hot Tapping Project',               'descripcion' => 'Proyecto integral Hot Tapping (Accesorio + Servicio + Civil/PNDs)'],
            ['codigo' => 'LSS',  'nombre' => 'Line Stopping Service',             'descripcion' => 'Servicio de Line Stopping (Obturación de línea)'],
            ['codigo' => 'LSF',  'nombre' => 'Line Stopping Fitting',             'descripcion' => 'Accesorios para Line Stopping'],
            ['codigo' => 'LSSF', 'nombre' => 'Line Stopping Service & Fitting',   'descripcion' => 'Servicio de Line Stopping + Accesorio'],
            ['codigo' => 'LSP',  'nombre' => 'Line Stopping Project',             'descripcion' => 'Proyecto integral Line Stopping'],
            ['codigo' => 'DLSS', 'nombre' => 'Double Line Stopping',              'descripcion' => 'Servicio de Doble Line Stopping'],
            ['codigo' => 'VBT',  'nombre' => 'Trunnion Ball Valve',               'descripcion' => 'Válvula de Bola Trunnion (GO, Lever Operator, Bare Stem)'],
            ['codigo' => 'VLV',  'nombre' => 'Válvulas Mecánicas',               'descripcion' => 'Válvulas de compuerta, globo, retención (GO, Level Operator)'],
            ['codigo' => 'AVL',  'nombre' => 'Actuated Valves',                   'descripcion' => 'Válvulas con Actuador'],
            ['codigo' => 'SDV',  'nombre' => 'Shut Down Valves',                  'descripcion' => 'Válvulas de cierre de emergencia con actuador'],
            ['codigo' => 'PIP',  'nombre' => 'Pigging Products',                  'descripcion' => 'Diablos, indicadores de paso y trampas de diablos'],
            ['codigo' => 'P&C',  'nombre' => 'Pipeline & Connections',            'descripcion' => 'Tubería y conexiones (codos, tees, reducciones)'],
            ['codigo' => 'RHP',  'nombre' => 'Rehab Products',                    'descripcion' => 'Productos para rehabilitación de líneas (clamps, composite wrap)'],
            ['codigo' => 'OTH',  'nombre' => 'PNDs / Otros',                      'descripcion' => 'Pruebas no destructivas, obra civil y otros servicios'],
        ];

        foreach ($sublineas as $sublinea) {
            DB::table('sublineas')->insert(array_merge($sublinea, [
                'created_at' => now(), 'updated_at' => now(),
            ]));
        }

        // Clientes reales de GPT Services según Tech_Codification_v1.md
        $clientes = [
            // Upstream / Offshore
            ['razon_social' => 'Cotemar',                    'alias_3letras' => 'COT', 'sector' => 'Oil & Gas', 'segmento' => 'Privado'],
            ['razon_social' => 'DIAVAZ',                     'alias_3letras' => 'DVZ', 'sector' => 'Oil & Gas', 'segmento' => 'Privado'],
            ['razon_social' => 'Propetrol',                  'alias_3letras' => 'PRP', 'sector' => 'Oil & Gas', 'segmento' => 'Privado'],
            ['razon_social' => 'Pemex TRI',                  'alias_3letras' => 'PMT', 'sector' => 'Oil & Gas', 'segmento' => 'Gobierno'],
            ['razon_social' => 'PEMEX PEP',                  'alias_3letras' => 'PEP', 'sector' => 'Oil & Gas', 'segmento' => 'Gobierno'],
            ['razon_social' => 'Permaducto',                 'alias_3letras' => 'PER', 'sector' => 'Oil & Gas', 'segmento' => 'Privado'],
            ['razon_social' => 'Protexa',                    'alias_3letras' => 'PTX', 'sector' => 'Oil & Gas', 'segmento' => 'Privado'],
            ['razon_social' => 'ENI México',                 'alias_3letras' => 'ENI', 'sector' => 'Oil & Gas', 'segmento' => 'Privado'],
            ['razon_social' => 'Nuvoil',                     'alias_3letras' => 'NUV', 'sector' => 'Oil & Gas', 'segmento' => 'Privado'],
            ['razon_social' => 'Techint',                    'alias_3letras' => 'TCT', 'sector' => 'Oil & Gas', 'segmento' => 'Privado'],
            ['razon_social' => 'ICA Fluor',                  'alias_3letras' => 'ICF', 'sector' => 'Oil & Gas', 'segmento' => 'Privado'],
            ['razon_social' => 'ESEASA Offshore',            'alias_3letras' => 'EAO', 'sector' => 'Oil & Gas', 'segmento' => 'Privado'],
            // Gasoductos / Transmisión
            ['razon_social' => 'TC Energy',                  'alias_3letras' => 'TCE', 'sector' => 'Gasoductos', 'segmento' => 'Privado'],
            ['razon_social' => 'Engie',                      'alias_3letras' => 'ENG', 'sector' => 'Gasoductos', 'segmento' => 'Privado'],
            ['razon_social' => 'Gas Natural del Noroeste',   'alias_3letras' => 'GNN', 'sector' => 'Gasoductos', 'segmento' => 'Privado'],
            ['razon_social' => 'Gas Natural Uruapan',        'alias_3letras' => 'GNU', 'sector' => 'Gasoductos', 'segmento' => 'Privado'],
            ['razon_social' => 'Igasamex',                   'alias_3letras' => 'IGA', 'sector' => 'Gasoductos', 'segmento' => 'Privado'],
            ['razon_social' => 'Naturgy',                    'alias_3letras' => 'NAT', 'sector' => 'Gasoductos', 'segmento' => 'Privado'],
            ['razon_social' => 'Fermaca',                    'alias_3letras' => 'FER', 'sector' => 'Gasoductos', 'segmento' => 'Privado'],
            ['razon_social' => 'CENAGAS',                    'alias_3letras' => 'CEN', 'sector' => 'Gasoductos', 'segmento' => 'Gobierno'],
            ['razon_social' => 'Kiewit Energy de Mexico',    'alias_3letras' => 'KEM', 'sector' => 'Gasoductos', 'segmento' => 'Privado'],
            // Refinación / Downstream
            ['razon_social' => 'Petróleos Mexicanos',        'alias_3letras' => 'PMX', 'sector' => 'Refinación', 'segmento' => 'Gobierno'],
            ['razon_social' => 'Walworth Oilfield Services', 'alias_3letras' => 'WOS', 'sector' => 'Oil & Gas',  'segmento' => 'Privado'],
            // Clientes reales 2026 — Status_Ofertas_2026
            ['razon_social' => 'PIR System',                           'alias_3letras' => 'PIR', 'sector' => 'Oil & Gas',     'segmento' => 'Privado'],
            ['razon_social' => 'EuroInova',                            'alias_3letras' => 'EIN', 'sector' => 'Gasoductos',    'segmento' => 'Privado'],
            ['razon_social' => 'Empresarial Molper / Grupo Biomedical', 'alias_3letras' => 'MOL', 'sector' => 'Gasoductos',   'segmento' => 'Privado'],
            ['razon_social' => 'Serport',                              'alias_3letras' => 'SRP', 'sector' => 'Oil & Gas',     'segmento' => 'Privado'],
            ['razon_social' => 'GCI',                                  'alias_3letras' => 'GCI', 'sector' => 'Refinación',    'segmento' => 'Privado'],
            ['razon_social' => 'ICA',                                  'alias_3letras' => 'ICA', 'sector' => 'Infraestructura','segmento' => 'Privado'],
            ['razon_social' => 'Constructora Sicim',                   'alias_3letras' => 'SIC', 'sector' => 'Gasoductos',    'segmento' => 'Privado'],
            ['razon_social' => 'COPC',                                 'alias_3letras' => 'COP', 'sector' => 'Industrial',    'segmento' => 'Privado'],
            ['razon_social' => 'INDHECA Grupo Constructor',            'alias_3letras' => 'IGC', 'sector' => 'Oil & Gas',     'segmento' => 'Privado'],
            ['razon_social' => 'Sarreal S.A. de C.V.',                 'alias_3letras' => 'SAR', 'sector' => 'Oil & Gas',     'segmento' => 'Privado'],
            ['razon_social' => 'Arseal',                               'alias_3letras' => 'ARS', 'sector' => 'Gasoductos',    'segmento' => 'Privado'],
            ['razon_social' => 'Esentia Energy',                       'alias_3letras' => 'ESE', 'sector' => 'Gasoductos',    'segmento' => 'Privado'],
            ['razon_social' => 'SEDENA',                               'alias_3letras' => 'SDN', 'sector' => 'Infraestructura','segmento' => 'Gobierno'],
            // Clientes 2026 adicionales del CSV
            ['razon_social' => 'Grupo 3VTA',                           'alias_3letras' => 'G3V', 'sector' => 'Oil & Gas',     'segmento' => 'Privado'],
            ['razon_social' => 'Geolis',                               'alias_3letras' => 'GSO', 'sector' => 'Oil & Gas',     'segmento' => 'Privado'],
            ['razon_social' => 'PIFUSA',                               'alias_3letras' => 'PIF', 'sector' => 'Gasoductos',    'segmento' => 'Privado'],
            ['razon_social' => 'Cocomex',                              'alias_3letras' => 'COC', 'sector' => 'Oil & Gas',     'segmento' => 'Privado'],
            ['razon_social' => 'Marabis Energy',                       'alias_3letras' => 'ME',  'sector' => 'Gasoductos',    'segmento' => 'Privado'],
        ];

        foreach ($clientes as $cliente) {
            DB::table('clientes')->insert(array_merge($cliente, [
                'activo' => true, 'created_at' => now(), 'updated_at' => now(),
            ]));
        }
    }
}
