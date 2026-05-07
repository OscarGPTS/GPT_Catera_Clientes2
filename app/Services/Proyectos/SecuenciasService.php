<?php

namespace App\Services\Proyectos;

use App\Models\Comercial\Secuencia;
use Illuminate\Support\Facades\DB;

class SecuenciasService
{
    public function asignarCp(int $año): string
    {
        return DB::transaction(function () use ($año) {
            $sec = Secuencia::where('tipo', 'cp')
                ->where('año', $año)
                ->lockForUpdate()
                ->firstOrCreate(
                    ['tipo' => 'cp', 'año' => $año],
                    ['ultimo_consecutivo' => 0]
                );
            $sec->increment('ultimo_consecutivo');

            return sprintf('CP-%03d/%02d', $sec->ultimo_consecutivo, $año % 100);
        });
    }

    public function asignarDn(int $año): string
    {
        return DB::transaction(function () use ($año) {
            $sec = Secuencia::where('tipo', 'dn')
                ->where('año', $año)
                ->lockForUpdate()
                ->firstOrCreate(
                    ['tipo' => 'dn', 'año' => $año],
                    ['ultimo_consecutivo' => 0]
                );
            $sec->increment('ultimo_consecutivo');

            return sprintf('DN-%03d/%02d', $sec->ultimo_consecutivo, $año % 100);
        });
    }
}
