<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kick_off_meetings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proyecto_id')->constrained('proyectos')->cascadeOnDelete();
            $table->enum('tipo', ['kom_interno', 'kom_cliente']);
            $table->date('fecha');
            $table->json('participantes')->nullable();
            $table->text('agenda')->nullable();
            $table->string('minuta_pdf_path')->nullable();
            $table->foreignId('cronograma_attached_id')->nullable();
            $table->timestamps();
        });

        Schema::create('cronogramas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proyecto_id')->constrained('proyectos')->cascadeOnDelete();
            $table->integer('version')->default(1);
            $table->foreignId('generado_por')->nullable()->constrained('users');
            $table->string('archivo_origen_path')->nullable();
            $table->date('fecha_inicio');
            $table->date('fecha_fin');
            $table->timestamps();
        });

        Schema::create('cronograma_actividades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cronograma_id')->constrained('cronogramas')->cascadeOnDelete();
            $table->string('codigo')->nullable();
            $table->string('nombre');
            $table->foreignId('parent_id')->nullable()->constrained('cronograma_actividades');
            $table->date('fecha_inicio_planeada');
            $table->date('fecha_fin_planeada');
            $table->date('fecha_inicio_real')->nullable();
            $table->date('fecha_fin_real')->nullable();
            $table->integer('porcentaje_avance')->default(0);
            $table->json('predecesoras')->nullable();
            $table->timestamps();
        });

        Schema::create('bom_boe_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proyecto_id')->constrained('proyectos')->cascadeOnDelete();
            $table->enum('tipo', ['BOM', 'BOE']);
            $table->text('descripcion');
            $table->decimal('cantidad', 12, 2)->default(1);
            $table->string('unidad')->nullable();
            $table->enum('status', ['en_almacen', 'por_afilar', 'por_fabricar', 'por_comprar', 'en_transito', 'entregado'])->default('en_almacen');
            $table->foreignId('responsable_id')->nullable()->constrained('users');
            $table->date('fecha_requerida')->nullable();
            $table->text('observaciones')->nullable();
            $table->timestamps();
        });

        Schema::create('listados_suministros', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proyecto_id')->constrained('proyectos')->cascadeOnDelete();
            $table->integer('porcentaje_avance_global')->default(0);
            $table->timestamps();
            $table->unique('proyecto_id');
        });

        Schema::create('listados_suministros_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('listado_id')->constrained('listados_suministros')->cascadeOnDelete();
            $table->text('descripcion');
            $table->decimal('cantidad', 12, 2)->default(1);
            $table->string('unidad')->nullable();
            $table->date('fecha_requerida')->nullable();
            $table->string('status')->nullable();
            $table->integer('porcentaje_avance')->default(0);
            $table->string('etapa')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('listados_suministros_items');
        Schema::dropIfExists('listados_suministros');
        Schema::dropIfExists('bom_boe_items');
        Schema::dropIfExists('cronograma_actividades');
        Schema::dropIfExists('cronogramas');
        Schema::dropIfExists('kick_off_meetings');
    }
};
