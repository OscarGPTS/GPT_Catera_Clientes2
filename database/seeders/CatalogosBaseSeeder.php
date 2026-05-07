<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CatalogosBaseSeeder extends Seeder
{
    public function run(): void
    {
        $sublineas = [
            ['codigo' => 'HTP', 'nombre' => 'Hot Tapping', 'descripcion' => 'Hot Tapping and Plugging Services'],
            ['codigo' => 'LSP', 'nombre' => 'Line Stopping', 'descripcion' => 'Line Stopping Services'],
            ['codigo' => 'VLV', 'nombre' => 'Válvulas', 'descripcion' => 'Valve Services'],
            ['codigo' => 'SOL', 'nombre' => 'Soldadura', 'descripcion' => 'Welding Services'],
            ['codigo' => 'SG', 'nombre' => 'Servicios Generales', 'descripcion' => 'General Services'],
        ];

        foreach ($sublineas as $sublinea) {
            DB::table('sublineas')->insert(array_merge($sublinea, [
                'created_at' => now(), 'updated_at' => now(),
            ]));
        }

        $clientes = [
            ['razon_social' => 'SEDENA', 'alias_3letras' => 'SDN', 'sector' => 'Defensa', 'segmento' => 'Gobierno'],
            ['razon_social' => 'IGASAMEX', 'alias_3letras' => 'IGA', 'sector' => 'Oil & Gas', 'segmento' => 'Privado'],
            ['razon_social' => 'PROTEXA', 'alias_3letras' => 'PTX', 'sector' => 'Oil & Gas', 'segmento' => 'Privado'],
            ['razon_social' => 'ESENTIA', 'alias_3letras' => 'FER', 'sector' => 'Energía', 'segmento' => 'Privado'],
            ['razon_social' => 'ENGIE', 'alias_3letras' => 'ENG', 'sector' => 'Energía', 'segmento' => 'Privado'],
            ['razon_social' => 'PEMEX', 'alias_3letras' => 'PMX', 'sector' => 'Oil & Gas', 'segmento' => 'Gobierno'],
            ['razon_social' => 'CENAGAS', 'alias_3letras' => 'CNG', 'sector' => 'Oil & Gas', 'segmento' => 'Gobierno'],
            ['razon_social' => 'TECHINT', 'alias_3letras' => 'TEC', 'sector' => 'Oil & Gas', 'segmento' => 'Privado'],
            ['razon_social' => 'SIEMENS', 'alias_3letras' => 'SIE', 'sector' => 'Energía', 'segmento' => 'Privado'],
            ['razon_social' => 'BOSCH', 'alias_3letras' => 'BOS', 'sector' => 'Industrial', 'segmento' => 'Privado'],
        ];

        foreach ($clientes as $cliente) {
            DB::table('clientes')->insert(array_merge($cliente, [
                'activo' => true, 'created_at' => now(), 'updated_at' => now(),
            ]));
        }
    }
}
