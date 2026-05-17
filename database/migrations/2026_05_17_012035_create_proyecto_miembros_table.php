<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabla normalizada de miembros del equipo por proyecto/oportunidad.
     *
     * Roles conocidos: creador, director_dn, gerente_proyectos,
     * gerente_operaciones, ingeniero_costos, ingeniero_proyectos, trainee
     */
    public function up(): void
    {
        Schema::create('proyecto_miembros', function (Blueprint $table) {
            $table->id();

            $table->foreignId('proyecto_id')
                  ->constrained('proyectos')
                  ->cascadeOnDelete();

            $table->foreignId('user_id')
                  ->constrained('users')
                  ->cascadeOnDelete();

            // Rol dentro del proyecto; null = miembro sin rol específico
            $table->string('rol')->nullable()
                  ->comment('creador|director_dn|gerente_proyectos|gerente_operaciones|ingeniero_costos|ingeniero_proyectos|trainee');

            $table->timestamps();

            $table->index(['proyecto_id', 'user_id']);
        });

        // ─── Migrar datos de las columnas legacy ──────────────────────────
        $mapa = [
            'elaboro_id'             => 'creador',
            'director_dn_id'         => 'director_dn',
            'gerente_proyectos_id'   => 'gerente_proyectos',
            'gerente_operaciones_id' => 'gerente_operaciones',
            'ingeniero_costos_id'    => 'ingeniero_costos',
            'ingeniero_proyectos_id' => 'ingeniero_proyectos',
            'trainee_id'             => 'trainee',
        ];

        foreach ($mapa as $columna => $rol) {
            DB::statement("
                INSERT INTO proyecto_miembros (proyecto_id, user_id, rol, created_at, updated_at)
                SELECT id, `{$columna}`, '{$rol}', NOW(), NOW()
                FROM proyectos
                WHERE `{$columna}` IS NOT NULL
                  AND deleted_at IS NULL
            ");
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('proyecto_miembros');
    }
};
