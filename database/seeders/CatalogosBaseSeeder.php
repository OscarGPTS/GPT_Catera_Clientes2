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
            ['razon_social' => 'Cotemar',                    'alias' => 'COT', 'sector' => 'Oil & Gas', 'segmento' => 'Privado'],
            ['razon_social' => 'DIAVAZ',                     'alias' => 'DVZ', 'sector' => 'Oil & Gas', 'segmento' => 'Privado'],
            ['razon_social' => 'Propetrol',                  'alias' => 'PRP', 'sector' => 'Oil & Gas', 'segmento' => 'Privado'],
            ['razon_social' => 'Pemex TRI',                  'alias' => 'PMT', 'sector' => 'Oil & Gas', 'segmento' => 'Gobierno'],
            ['razon_social' => 'PEMEX PEP',                  'alias' => 'PEP', 'sector' => 'Oil & Gas', 'segmento' => 'Gobierno'],
            ['razon_social' => 'Permaducto',                 'alias' => 'PER', 'sector' => 'Oil & Gas', 'segmento' => 'Privado'],
            ['razon_social' => 'Protexa',                    'alias' => 'PTX', 'sector' => 'Oil & Gas', 'segmento' => 'Privado'],
            ['razon_social' => 'ENI México',                 'alias' => 'ENI', 'sector' => 'Oil & Gas', 'segmento' => 'Privado'],
            ['razon_social' => 'Nuvoil',                     'alias' => 'NUV', 'sector' => 'Oil & Gas', 'segmento' => 'Privado'],
            ['razon_social' => 'Techint',                    'alias' => 'TCT', 'sector' => 'Oil & Gas', 'segmento' => 'Privado'],
            ['razon_social' => 'ICA Fluor',                  'alias' => 'ICF', 'sector' => 'Oil & Gas', 'segmento' => 'Privado'],
            ['razon_social' => 'ESEASA Offshore',            'alias' => 'EAO', 'sector' => 'Oil & Gas', 'segmento' => 'Privado'],
            // Gasoductos / Transmisión
            ['razon_social' => 'TC Energy',                  'alias' => 'TCE', 'sector' => 'Gasoductos', 'segmento' => 'Privado'],
            ['razon_social' => 'Engie',                      'alias' => 'ENG', 'sector' => 'Gasoductos', 'segmento' => 'Privado'],
            ['razon_social' => 'Gas Natural del Noroeste',   'alias' => 'GNN', 'sector' => 'Gasoductos', 'segmento' => 'Privado'],
            ['razon_social' => 'Gas Natural Uruapan',        'alias' => 'GNU', 'sector' => 'Gasoductos', 'segmento' => 'Privado'],
            ['razon_social' => 'Igasamex',                   'alias' => 'IGA', 'sector' => 'Gasoductos', 'segmento' => 'Privado'],
            ['razon_social' => 'Naturgy',                    'alias' => 'NAT', 'sector' => 'Gasoductos', 'segmento' => 'Privado'],
            ['razon_social' => 'Fermaca',                    'alias' => 'FER', 'sector' => 'Gasoductos', 'segmento' => 'Privado'],
            ['razon_social' => 'CENAGAS',                    'alias' => 'CEN', 'sector' => 'Gasoductos', 'segmento' => 'Gobierno'],
            ['razon_social' => 'Kiewit Energy de Mexico',    'alias' => 'KEM', 'sector' => 'Gasoductos', 'segmento' => 'Privado'],
            // Refinación / Downstream
            ['razon_social' => 'Petróleos Mexicanos',        'alias' => 'PMX', 'sector' => 'Refinación', 'segmento' => 'Gobierno'],
            ['razon_social' => 'Walworth Oilfield Services', 'alias' => 'WOS', 'sector' => 'Oil & Gas',  'segmento' => 'Privado'],
            // Clientes reales 2026 — Status_Ofertas_2026
            ['razon_social' => 'PIR System',                           'alias' => 'PIR', 'sector' => 'Oil & Gas',     'segmento' => 'Privado'],
            ['razon_social' => 'EuroInova',                            'alias' => 'EIN', 'sector' => 'Gasoductos',    'segmento' => 'Privado'],
            ['razon_social' => 'Empresarial Molper / Grupo Biomedical', 'alias' => 'MOL', 'sector' => 'Gasoductos',   'segmento' => 'Privado'],
            ['razon_social' => 'Serport',                              'alias' => 'SRP', 'sector' => 'Oil & Gas',     'segmento' => 'Privado'],
            ['razon_social' => 'GCI',                                  'alias' => 'GCI', 'sector' => 'Refinación',    'segmento' => 'Privado'],
            ['razon_social' => 'ICA',                                  'alias' => 'ICA', 'sector' => 'Infraestructura','segmento' => 'Privado'],
            ['razon_social' => 'Constructora Sicim',                   'alias' => 'SIC', 'sector' => 'Gasoductos',    'segmento' => 'Privado'],
            ['razon_social' => 'COPC',                                 'alias' => 'COP', 'sector' => 'Industrial',    'segmento' => 'Privado'],
            ['razon_social' => 'INDHECA Grupo Constructor',            'alias' => 'IGC', 'sector' => 'Oil & Gas',     'segmento' => 'Privado'],
            ['razon_social' => 'Sarreal S.A. de C.V.',                 'alias' => 'SAR', 'sector' => 'Oil & Gas',     'segmento' => 'Privado'],
            ['razon_social' => 'Arseal',                               'alias' => 'ARS', 'sector' => 'Gasoductos',    'segmento' => 'Privado'],
            ['razon_social' => 'Esentia Energy',                       'alias' => 'ESE', 'sector' => 'Gasoductos',    'segmento' => 'Privado'],
            ['razon_social' => 'SEDENA',                               'alias' => 'SDN', 'sector' => 'Infraestructura','segmento' => 'Gobierno'],
            // Clientes 2026 adicionales del CSV
            ['razon_social' => 'Grupo 3VTA',                           'alias' => 'G3V', 'sector' => 'Oil & Gas',     'segmento' => 'Privado'],
            ['razon_social' => 'Geolis',                               'alias' => 'GSO', 'sector' => 'Oil & Gas',     'segmento' => 'Privado'],
            ['razon_social' => 'PIFUSA',                               'alias' => 'PIF', 'sector' => 'Gasoductos',    'segmento' => 'Privado'],
            ['razon_social' => 'Cocomex',                              'alias' => 'COC', 'sector' => 'Oil & Gas',     'segmento' => 'Privado'],
            ['razon_social' => 'Marabis Energy',                       'alias' => 'ME',  'sector' => 'Gasoductos',    'segmento' => 'Privado'],
        ];

        foreach ($clientes as $cliente) {
            DB::table('clientes')->insert(array_merge($cliente, [
                'activo' => true, 'created_at' => now(), 'updated_at' => now(),
            ]));
        }
    }
}
