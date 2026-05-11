<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('proyectos', function (Blueprint $table) {
            $table->id();
            $table->string('tech_reference')->nullable()->unique();
            $table->string('cp_numero')->nullable()->unique();
            $table->string('dn_numero')->nullable()->unique();
            $table->year('anio');
            $table->foreignId('cliente_id')->constrained('clientes');
            $table->foreignId('sublinea_id')->constrained('sublineas');
            $table->string('usuario_final')->nullable();
            $table->string('sector')->nullable();
            $table->enum('estado', [
                'en_revision', 'cotizando', 'cotizado', 'presentado',
                'adjudicado_pendiente', 'adjudicado_firmado', 'en_ejecucion',
                'en_cierre', 'cerrado', 'cancelado', 'perdido', 'archivado'
            ])->default('en_revision');
            $table->date('fecha_inicio_planeada')->nullable();
            $table->date('fecha_fin_planeada')->nullable();
            $table->enum('metodo_distribucion_plurianual', ['dias_naturales', 'hitos'])->default('dias_naturales');
            $table->foreignId('director_dn_id')->nullable()->constrained('users');
            $table->foreignId('gerente_proyectos_id')->nullable()->constrained('users');
            $table->foreignId('gerente_operaciones_id')->nullable()->constrained('users');
            $table->foreignId('ingeniero_costos_id')->nullable()->constrained('users');
            $table->foreignId('ingeniero_proyectos_id')->nullable()->constrained('users');
            $table->foreignId('trainee_id')->nullable()->constrained('users');
            $table->text('notas')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('proyecto_eventos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proyecto_id')->constrained()->cascadeOnDelete();
            $table->string('tipo');
            $table->json('metadata')->nullable();
            $table->foreignId('user_id')->nullable()->constrained();
            $table->text('comentario')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('proyecto_eventos');
        Schema::dropIfExists('proyectos');
    }
};
