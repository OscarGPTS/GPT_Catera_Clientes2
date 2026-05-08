<?php

namespace App\Services\Proyectos;

use App\Models\Comercial\Secuencia;
use App\Models\Proyectos\Proyecto;
use Illuminate\Support\Facades\DB;

class SecuenciasService
{
    public function asignarCp(Proyecto $proyecto): string
    {
        return DB::transaction(function () use ($proyecto) {
            $año = $proyecto->año ?? now()->year;

            $secuencia = Secuencia::where('tipo', 'cp')
                ->where('año', $año)
                ->lockForUpdate()
                ->first();

            if (!$secuencia) {
                $secuencia = Secuencia::create([
                    'tipo' => 'cp',
                    'año' => $año,
                    'ultimo_consecutivo' => 0,
                ]);
            }

            $secuencia->increment('ultimo_consecutivo');
            $consecutivo = $secuencia->ultimo_consecutivo;

            $cpNumero = 'CP-' . substr((string) $año, -2) . '-' . str_pad($consecutivo, 3, '0', STR_PAD_LEFT);

            $proyecto->update(['cp_numero' => $cpNumero]);

            $proyecto->eventos()->create([
                'tipo' => 'cp_asignado',
                'user_id' => auth()->id() ?? 1,
                'comentario' => "CP asignado: {$cpNumero}",
                'metadata' => ['cp_numero' => $cpNumero, 'consecutivo' => $consecutivo],
            ]);

            return $cpNumero;
        });
    }

    public function asignarDn(Proyecto $proyecto): string
    {
        return DB::transaction(function () use ($proyecto) {
            $año = $proyecto->año ?? now()->year;

            $secuencia = Secuencia::where('tipo', 'dn')
                ->where('año', $año)
                ->lockForUpdate()
                ->first();

            if (!$secuencia) {
                $secuencia = Secuencia::create([
                    'tipo' => 'dn',
                    'año' => $año,
                    'ultimo_consecutivo' => 0,
                ]);
            }

            $secuencia->increment('ultimo_consecutivo');
            $consecutivo = $secuencia->ultimo_consecutivo;

            $dnNumero = 'DN-' . substr((string) $año, -2) . '-' . str_pad($consecutivo, 3, '0', STR_PAD_LEFT);

            $proyecto->update(['dn_numero' => $dnNumero]);

            $proyecto->eventos()->create([
                'tipo' => 'dn_asignado',
                'user_id' => auth()->id() ?? 1,
                'comentario' => "DN asignado: {$dnNumero}",
                'metadata' => ['dn_numero' => $dnNumero, 'consecutivo' => $consecutivo],
            ]);

            return $dnNumero;
        });
    }
}