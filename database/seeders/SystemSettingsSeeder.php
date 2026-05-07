<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SystemSettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            ['group' => 'system', 'name' => 'minuta_entrega_obligatoria', 'payload' => json_encode(false)],
            ['group' => 'system', 'name' => 'bloqueo_cierre_dossier_incompleto', 'payload' => json_encode(true)],
            ['group' => 'system', 'name' => 'bloqueo_cierre_post_mortem_pendiente', 'payload' => json_encode(false)],
            ['group' => 'system', 'name' => 'auth_dominios_corporativos', 'payload' => json_encode(['gptservices.com', 'satechenergy.com'])],
            ['group' => 'system', 'name' => 'concentracion_cliente_alerta_umbral', 'payload' => json_encode(50)],
        ];

        foreach ($settings as $setting) {
            DB::table('settings')->updateOrInsert(
                ['group' => $setting['group'], 'name' => $setting['name']],
                ['payload' => $setting['payload'], 'locked' => true, 'created_at' => now(), 'updated_at' => now()]
            );
        }
    }
}
