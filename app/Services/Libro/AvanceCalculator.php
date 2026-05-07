<?php

namespace App\Services\Libro;

use Illuminate\Support\Facades\DB;

class AvanceCalculator
{
    /**
     * % avance de una sección = (items checklist completados / items totales) * 100
     */
    public function calcularAvanceSeccion(int $seccionId): int
    {
        $total = DB::table('libro_seccion_checklist')
            ->where('seccion_id', $seccionId)
            ->count();

        if ($total === 0) {
            return 0;
        }

        $completados = DB::table('libro_seccion_checklist')
            ->where('seccion_id', $seccionId)
            ->where('completado', true)
            ->count();

        return (int) round(($completados / $total) * 100);
    }

    /**
     * % avance global = AVG(% avance de las 10 secciones)
     */
    public function calcularAvanceGlobal(int $libroId): int
    {
        $secciones = DB::table('libro_secciones')
            ->where('libro_id', $libroId)
            ->get();

        $total = 0;
        foreach ($secciones as $seccion) {
            $total += $this->calcularAvanceSeccion($seccion->id);
        }

        return (int) round($total / max(1, $secciones->count()));
    }
}
