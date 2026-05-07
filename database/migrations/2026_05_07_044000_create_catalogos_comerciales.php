<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clientes', function (Blueprint $table) {
            $table->id();
            $table->string('razon_social');
            $table->string('alias_3letras', 3)->nullable();
            $table->string('rfc', 13)->nullable();
            $table->string('sector')->nullable();
            $table->string('segmento')->nullable();
            $table->boolean('activo')->default(true);
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('contactos_cliente', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->constrained('clientes')->cascadeOnDelete();
            $table->string('nombre');
            $table->string('puesto')->nullable();
            $table->string('email')->nullable();
            $table->string('telefono')->nullable();
            $table->boolean('principal')->default(false);
            $table->timestamps();
        });

        Schema::create('sublineas', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 10)->unique();
            $table->string('nombre');
            $table->text('descripcion')->nullable();
            $table->timestamps();
        });

        Schema::create('secuencias', function (Blueprint $table) {
            $table->id();
            $table->enum('tipo', ['cp', 'dn']);
            $table->year('año');
            $table->integer('ultimo_consecutivo')->default(0);
            $table->timestamps();
            $table->unique(['tipo', 'año']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('secuencias');
        Schema::dropIfExists('sublineas');
        Schema::dropIfExists('contactos_cliente');
        Schema::dropIfExists('clientes');
    }
};
