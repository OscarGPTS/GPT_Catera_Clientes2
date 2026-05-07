<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bitacora_diaria', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proyecto_id')->constrained('proyectos')->cascadeOnDelete();
            $table->date('fecha');
            $table->text('relacion_actividades');
            $table->json('personal_gpt')->nullable();
            $table->json('equipos_en_sitio')->nullable();
            $table->json('proveedores_subcontratistas')->nullable();
            $table->string('vobo_cliente_nombre')->nullable();
            $table->string('vobo_cliente_organizacion')->nullable();
            $table->date('vobo_cliente_fecha')->nullable();
            $table->string('vobo_cliente_firma_path')->nullable();
            $table->foreignId('cargado_por_id')->constrained('users');
            $table->timestamp('firmado_at')->nullable();
            $table->timestamps();
            $table->unique(['proyecto_id', 'fecha']);
        });

        Schema::create('reportes_semanales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proyecto_id')->constrained('proyectos')->cascadeOnDelete();
            $table->date('semana_inicio');
            $table->date('semana_fin');
            $table->text('contenido_html')->nullable();
            $table->timestamp('generado_at')->nullable();
            $table->timestamp('enviado_at')->nullable();
            $table->json('recipients')->nullable();
            $table->timestamps();
        });

        Schema::create('solicitudes_viaticos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proyecto_id')->constrained('proyectos')->cascadeOnDelete();
            $table->date('periodo_inicio');
            $table->date('periodo_fin');
            $table->text('justificacion')->nullable();
            $table->enum('status', ['borrador', 'pendiente_serv_grales', 'pendiente_direccion', 'aprobado', 'rechazado'])->default('borrador');
            $table->foreignId('solicitante_id')->constrained('users');
            $table->foreignId('aprobador_serv_grales_id')->nullable()->constrained('users');
            $table->foreignId('aprobador_direccion_id')->nullable()->constrained('users');
            $table->timestamp('aprobado_at')->nullable();
            $table->string('pdf_path')->nullable();
            $table->timestamps();
        });

        Schema::create('viaticos_personal', function (Blueprint $table) {
            $table->id();
            $table->foreignId('solicitud_id')->constrained('solicitudes_viaticos')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users');
            $table->integer('dias')->default(1);
            $table->timestamps();
        });

        Schema::create('viaticos_partidas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('solicitud_id')->constrained('solicitudes_viaticos')->cascadeOnDelete();
            $table->enum('concepto', ['hospedaje', 'alimentos', 'transporte']);
            $table->decimal('monto_estimado', 15, 2)->default(0);
            $table->decimal('monto_real', 15, 2)->nullable();
            $table->text('observaciones')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('viaticos_partidas');
        Schema::dropIfExists('viaticos_personal');
        Schema::dropIfExists('solicitudes_viaticos');
        Schema::dropIfExists('reportes_semanales');
        Schema::dropIfExists('bitacora_diaria');
    }
};
