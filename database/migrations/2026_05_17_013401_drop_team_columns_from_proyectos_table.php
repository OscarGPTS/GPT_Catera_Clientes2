<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Columnas de equipo migradas a proyecto_miembros. */
    private array $columns = [
        'director_dn_id',
        'gerente_proyectos_id',
        'gerente_operaciones_id',
        'ingeniero_costos_id',
        'ingeniero_proyectos_id',
        'trainee_id',
    ];

    public function up(): void
    {
        Schema::table('proyectos', function (Blueprint $table) {
            $table->dropForeign(['director_dn_id']);
            $table->dropForeign(['gerente_proyectos_id']);
            $table->dropForeign(['gerente_operaciones_id']);
            $table->dropForeign(['ingeniero_costos_id']);
            $table->dropForeign(['ingeniero_proyectos_id']);
            $table->dropForeign(['trainee_id']);
            $table->dropColumn($this->columns);
        });
    }

    public function down(): void
    {
        Schema::table('proyectos', function (Blueprint $table) {
            foreach ($this->columns as $col) {
                $table->foreignId($col)->nullable()->constrained('users');
            }
        });
    }
};
