<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('proyecto_ponderacion_historial', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proyecto_id')->constrained('proyectos')->cascadeOnDelete();
            $table->foreignId('ponderacion_id')->constrained('ponderaciones')->restrictOnDelete();
            $table->unsignedSmallInteger('anio');
            $table->unsignedTinyInteger('mes');                 // 1..12
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('notas', 500)->nullable();
            $table->timestamps();

            // Un snapshot por proyecto y mes
            $table->unique(['proyecto_id', 'anio', 'mes'], 'proy_pond_hist_unico');
            $table->index(['anio', 'mes']);
        });

        // Backfill: snapshot del mes actual usando proyectos.ponderacion vigente.
        // Una sola fila por oportunidad existente, sin inventar historial.
        $anio = (int) now()->year;
        $mes  = (int) now()->month;

        $pondMap = DB::table('ponderaciones')->pluck('id', 'porcentaje'); // [0=>id_perdida, 10=>id_remoto, 25=>..., 75=>..., 100=>...]
        // Fallback: si la ponderación no coincide exactamente, usar la más cercana
        $defaultId = $pondMap[10] ?? DB::table('ponderaciones')->orderBy('porcentaje')->value('id');

        // Note: the `tipo` column may not exist yet at this point in the migration
        // sequence (it's added by a later migration). All existing records are
        // effectively oportunidades, so no filter is needed here.
        $proyectos = DB::table('proyectos')
            ->whereNull('deleted_at')
            ->when(Schema::hasColumn('proyectos', 'tipo'), fn($q) => $q->where('tipo', 'oportunidad'))
            ->get(['id', 'ponderacion']);

        $rows = [];
        $now = now();
        foreach ($proyectos as $p) {
            $pondId = $pondMap[(int) $p->ponderacion] ?? $defaultId;
            $rows[] = [
                'proyecto_id'    => $p->id,
                'ponderacion_id' => $pondId,
                'anio'           => $anio,
                'mes'            => $mes,
                'user_id'        => null,
                'notas'          => 'Snapshot inicial (creado por migración).',
                'created_at'     => $now,
                'updated_at'     => $now,
            ];
        }
        if (!empty($rows)) {
            DB::table('proyecto_ponderacion_historial')->insert($rows);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('proyecto_ponderacion_historial');
    }
};
