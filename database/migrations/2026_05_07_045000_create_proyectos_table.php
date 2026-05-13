<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Catálogo de lugares (debe crearse antes de proyectos por la FK)
        Schema::create('lugares', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('tipo')->default('estado'); // estado, ciudad, municipio, pais
            $table->string('pais')->default('México');
            $table->string('codigo', 10)->nullable();
            $table->timestamps();
        });

        Schema::create('proyectos', function (Blueprint $table) {
            $table->id();

            // ── Identificadores ───────────────────────────────────────────────
            $table->string('cp_numero')->nullable()->unique();
            $table->string('dn_numero')->nullable();        // Número interno DN (asignado post-adjudicación)
            $table->string('tech_reference')->nullable();   // Código técnico de la oferta (OFERTA en CSV)
            $table->year('anio');

            // ── Relaciones de catálogo ────────────────────────────────────────
            $table->foreignId('cliente_id')->constrained('clientes');
            $table->foreignId('sublinea_id')->nullable()->constrained('sublineas');
            $table->foreignId('lugar_id')->nullable()->constrained('lugares');

            // ── Datos del contacto / alcance ──────────────────────────────────
            $table->string('usuario_final')->nullable();
            $table->string('contacto')->nullable();
            $table->text('datos_contacto')->nullable();
            $table->string('sector')->nullable();
            $table->text('alcance')->nullable();

            // ── Estado y ponderación ──────────────────────────────────────────
            $table->enum('estado', [
                'en_revision', 'cotizando', 'cotizado', 'presentado',
                'adjudicado_pendiente', 'adjudicado_firmado', 'en_ejecucion',
                'en_cierre', 'cerrado', 'cancelado', 'perdido', 'archivado',
            ])->default('en_revision');
            $table->unsignedTinyInteger('ponderacion')->default(10); // 10=REMOTO … 100=CONTRATADO

            // ── Montos y fechas de la oferta ──────────────────────────────────
            $table->decimal('monto_usd', 15, 2)->nullable();
            $table->date('fecha_envio')->nullable();
            $table->date('fecha_modificacion_oferta')->nullable();
            $table->text('hitos_pago')->nullable();
            $table->string('archivo_oferta')->nullable();

            // ── Adjudicación ──────────────────────────────────────────────────
            $table->string('concepto_adjudicacion')->nullable();
            $table->decimal('porcentaje_adjudicacion', 5, 2)->nullable();
            $table->decimal('cartera_esperada', 15, 2)->nullable();

            // ── Responsable de la oferta ──────────────────────────────────────
            $table->foreignId('elaboro_id')->nullable()->constrained('users');

            // ── Planificación (post-adjudicación) ─────────────────────────────
            $table->date('fecha_inicio_planeada')->nullable();
            $table->date('fecha_fin_planeada')->nullable();
            $table->enum('metodo_distribucion_plurianual', ['dias_naturales', 'hitos'])->default('dias_naturales');

            // ── Equipo del proyecto ───────────────────────────────────────────
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
        Schema::dropIfExists('lugares');
    }
};
