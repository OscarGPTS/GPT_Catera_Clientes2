<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('minutas_entrega', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proyecto_id')->constrained('proyectos')->cascadeOnDelete();
            $table->date('fecha_reunion');
            $table->time('hora_inicio');
            $table->time('hora_fin');
            $table->enum('modalidad', ['presencial', 'virtual', 'mixta'])->default('presencial');
            $table->json('orden_del_dia')->nullable();
            $table->json('acuerdos')->nullable();
            $table->string('pdf_path')->nullable();
            $table->timestamp('firmado_at')->nullable();
            $table->enum('status', ['borrador', 'firmada'])->default('borrador');
            $table->timestamps();
            $table->unique('proyecto_id');
        });

        Schema::create('minuta_entrega_participantes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('minuta_id')->constrained('minutas_entrega')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users');
            $table->string('rol_en_minuta')->nullable();
            $table->boolean('firma_pendiente')->default(true);
            $table->timestamp('firmado_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('minuta_entrega_participantes');
        Schema::dropIfExists('minutas_entrega');
    }
};
