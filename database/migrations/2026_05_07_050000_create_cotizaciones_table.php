<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cotizaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proyecto_id')->constrained('proyectos')->cascadeOnDelete();
            $table->integer('version')->default(1);
            $table->decimal('costo_directo', 15, 2)->default(0);
            $table->decimal('factor_indirectos', 5, 4)->default(0);
            $table->decimal('factor_admin', 5, 4)->default(0);
            $table->decimal('factor_utilidad', 5, 4)->default(0);
            $table->decimal('precio_venta_calculado', 15, 2)->default(0);
            $table->decimal('precio_venta_final', 15, 2)->default(0);
            $table->string('moneda', 3)->default('USD');
            $table->enum('status', ['borrador', 'revision', 'interno_aprobado', 'presentado', 'aprobado', 'rechazado'])->default('borrador');
            $table->foreignId('generado_por')->nullable()->constrained('users');
            $table->date('fecha_emision')->nullable();
            $table->text('observaciones')->nullable();
            $table->timestamps();
        });

        Schema::create('cotizacion_partidas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cotizacion_id')->constrained('cotizaciones')->cascadeOnDelete();
            $table->integer('numero_partida');
            $table->text('descripcion');
            $table->decimal('cantidad', 12, 2)->default(1);
            $table->string('unidad')->nullable();
            $table->decimal('costo_unitario', 15, 2)->default(0);
            $table->decimal('costo_total', 15, 2)->default(0);
            $table->text('observaciones')->nullable();
            $table->timestamps();
        });

        Schema::create('solicitudes_internas', function (Blueprint $table) {
            $table->id();
            $table->enum('tipo', ['requisicion_compras', 'orden_trabajo_ingenieria']);
            $table->foreignId('proyecto_id')->constrained('proyectos')->cascadeOnDelete();
            $table->string('cp_numero')->nullable();
            $table->string('codigo_formato')->nullable();
            $table->enum('estado', ['pendiente', 'en_proceso', 'respondida', 'cancelada'])->default('pendiente');
            $table->foreignId('solicitante_id')->constrained('users');
            $table->foreignId('asignado_id')->nullable()->constrained('users');
            $table->date('fecha_solicitud');
            $table->date('fecha_respuesta_requerida')->nullable();
            $table->date('fecha_respuesta_real')->nullable();
            $table->timestamps();
        });

        Schema::create('solicitudes_internas_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('solicitud_id')->constrained('solicitudes_internas')->cascadeOnDelete();
            $table->text('descripcion');
            $table->decimal('cantidad', 12, 2)->default(1);
            $table->string('unidad')->nullable();
            $table->text('especificacion')->nullable();
            $table->text('observaciones')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('solicitudes_internas_items');
        Schema::dropIfExists('solicitudes_internas');
        Schema::dropIfExists('cotizacion_partidas');
        Schema::dropIfExists('cotizaciones');
    }
};
