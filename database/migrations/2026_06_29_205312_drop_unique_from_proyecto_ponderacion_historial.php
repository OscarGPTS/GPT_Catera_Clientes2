<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * proyecto_ponderacion_historial pasa de "un snapshot por mes" (unique
     * proyecto_id+anio+mes) a un LOG de cambios: una fila por cada cambio de
     * ponderación, conservando el historial completo en el tiempo. El dashboard
     * deriva el valor mensual tomando el último cambio de cada mes.
     */
    public function up(): void
    {
        // El unique (proyecto_id, anio, mes) está soportando la FK de proyecto_id.
        // Primero damos a la FK un índice propio sobre proyecto_id, y recién
        // entonces podemos quitar el unique sin romper la restricción.
        Schema::table('proyecto_ponderacion_historial', function (Blueprint $table) {
            $table->index('proyecto_id', 'proy_pond_hist_proyecto_idx');
        });

        Schema::table('proyecto_ponderacion_historial', function (Blueprint $table) {
            $table->dropUnique('proy_pond_hist_unico');
        });
    }

    public function down(): void
    {
        // Antes de re-aplicar el unique hay que colapsar duplicados (quedarse con
        // el último cambio de cada mes), de lo contrario la creación del índice falla.
        $dups = DB::table('proyecto_ponderacion_historial')
            ->selectRaw('proyecto_id, anio, mes, MAX(id) AS keep_id, COUNT(*) AS c')
            ->groupBy('proyecto_id', 'anio', 'mes')
            ->having('c', '>', 1)
            ->get();

        foreach ($dups as $d) {
            DB::table('proyecto_ponderacion_historial')
                ->where('proyecto_id', $d->proyecto_id)
                ->where('anio', $d->anio)
                ->where('mes', $d->mes)
                ->where('id', '<>', $d->keep_id)
                ->delete();
        }

        Schema::table('proyecto_ponderacion_historial', function (Blueprint $table) {
            $table->unique(['proyecto_id', 'anio', 'mes'], 'proy_pond_hist_unico');
        });

        Schema::table('proyecto_ponderacion_historial', function (Blueprint $table) {
            $table->dropIndex('proy_pond_hist_proyecto_idx');
        });
    }
};
