<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('libros_proyecto', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proyecto_id')->constrained('proyectos')->cascadeOnDelete();
            $table->date('fecha_apertura');
            $table->date('fecha_cierre_estimado')->nullable();
            $table->date('fecha_cierre_real')->nullable();
            $table->integer('porcentaje_avance_global')->default(0);
            $table->boolean('bloqueado_para_cierre')->default(false);
            $table->timestamps();
            $table->unique('proyecto_id');
        });

        Schema::create('libro_secciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('libro_id')->constrained('libros_proyecto')->cascadeOnDelete();
            $table->enum('codigo', ['A','B','C','D','E','F','G','H','I','J']);
            $table->string('nombre');
            $table->text('descripcion')->nullable();
            $table->integer('porcentaje_avance')->default(0);
            $table->enum('estado', ['pendiente','en_proceso','completo'])->default('pendiente');
            $table->foreignId('responsable_id')->nullable()->constrained('users');
            $table->text('observaciones')->nullable();
            $table->timestamps();
        });

        Schema::create('libro_seccion_checklist', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seccion_id')->constrained('libro_secciones')->cascadeOnDelete();
            $table->text('item_descripcion');
            $table->boolean('completado')->default(false);
            $table->foreignId('evidencia_documento_id')->nullable();
            $table->foreignId('completado_por_id')->nullable()->constrained('users');
            $table->timestamp('completado_at')->nullable();
            $table->timestamps();
        });

        Schema::create('libro_documentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seccion_id')->constrained('libro_secciones')->cascadeOnDelete();
            $table->string('nombre');
            $table->string('archivo_path');
            $table->integer('version')->default(1);
            $table->string('mime_type')->nullable();
            $table->bigInteger('tamaño')->nullable();
            $table->foreignId('subido_por_id')->nullable()->constrained('users');
            $table->timestamp('subido_at')->useCurrent();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('libro_documentos');
        Schema::dropIfExists('libro_seccion_checklist');
        Schema::dropIfExists('libro_secciones');
        Schema::dropIfExists('libros_proyecto');
    }
};
