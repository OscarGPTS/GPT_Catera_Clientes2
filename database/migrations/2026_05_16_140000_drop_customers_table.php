<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Consolidación: el catálogo `customers` se elimina porque su rol lo
     * cumple la tabla `clientes` (razon_social + alias actúan como nombre y
     * acrónimo). tech_references.cliente_id ya apunta a clientes.
     */
    public function up(): void
    {
        Schema::dropIfExists('customers');
    }

    public function down(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('customer');
            $table->string('acronym', 50)->nullable()->unique();
            $table->boolean('status')->default(true)->index();
            $table->timestamps();
        });
    }
};
