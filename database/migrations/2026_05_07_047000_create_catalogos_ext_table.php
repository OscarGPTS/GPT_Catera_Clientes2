<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Catálogo de clientes externos (importado del Excel de tech references)
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('customer');           // Nombre completo del cliente
            $table->string('acronym', 50)->nullable()->unique(); // Siglas / alias
            $table->timestamps();
        });

        // Catálogo de líneas de negocio (HTS, HTF, SMP, etc.)
        Schema::create('core_businesses', function (Blueprint $table) {
            $table->id();
            $table->string('core_business');              // Nombre (Hot Tapping Service)
            $table->string('acronym', 50)->nullable()->unique(); // Siglas (HTS)
            $table->text('description')->nullable();      // Descripción larga
            $table->timestamps();
        });

        // Catálogo genérico de varios (Obra Civil, etc.)
        Schema::create('varios', function (Blueprint $table) {
            $table->id();
            $table->string('nombre')->unique();
            $table->timestamps();
        });

        // Catálogo de tamaños de tubería
        Schema::create('sizes', function (Blueprint $table) {
            $table->id();
            $table->decimal('size_principal', 8, 2);    // Pulgadas (2, 4, 6…)
            $table->string('size_secundario', 50)->nullable(); // Representación fraccionaria (1 1/2)
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sizes');
        Schema::dropIfExists('varios');
        Schema::dropIfExists('core_businesses');
        Schema::dropIfExists('customers');
    }
};
