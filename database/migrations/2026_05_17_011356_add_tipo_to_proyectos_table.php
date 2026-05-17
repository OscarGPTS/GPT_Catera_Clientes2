<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Agrega el campo `tipo` para distinguir oportunidades de proyectos
     * en la tabla compartida `proyectos`.
     *
     * - oportunidad : registros en pipeline comercial (pre-ejecución)
     * - proyecto     : registros adjudicados y en ejecución / cierre
     */
    public function up(): void
    {
        Schema::table('proyectos', function (Blueprint $table) {
            $table->enum('tipo', ['oportunidad', 'proyecto'])
                  ->default('oportunidad')
                  ->after('estado')
                  ->comment('Distingue si el registro es una oportunidad comercial o un proyecto en ejecución');
        });

        // Poblar el campo en registros existentes basándose en el estado actual:
        // Los estados de ejecución / cierre = proyecto; el resto = oportunidad
        DB::statement("
            UPDATE proyectos
            SET tipo = CASE
                WHEN estado IN ('adjudicado_firmado', 'en_ejecucion', 'en_cierre', 'cerrado')
                    THEN 'proyecto'
                ELSE 'oportunidad'
            END
            WHERE deleted_at IS NULL
        ");
    }

    public function down(): void
    {
        Schema::table('proyectos', function (Blueprint $table) {
            $table->dropColumn('tipo');
        });
    }
};
