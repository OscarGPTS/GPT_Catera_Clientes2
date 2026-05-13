<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tech_references', function (Blueprint $table) {
            $table->id();

            // A - Customer
            $table->foreignId('cliente_id')->nullable()->constrained('clientes')->nullOnDelete();

            // B - Tech Reference (clave única de la oferta técnica)
            $table->string('tech_reference')->unique();

            // C - Date (YYMMDD)
            $table->string('fecha_referencia', 6)->nullable();

            // D - CP
            $table->string('cp_numero')->nullable();

            // E - Quote / CP Revision
            $table->unsignedTinyInteger('revision')->default(0);

            // F - Contact
            $table->string('contacto')->nullable();

            // G - State
            $table->string('estado_cliente')->nullable();

            // H - Country
            $table->string('pais', 10)->nullable();

            // I - Zone
            $table->string('zona')->nullable();

            // J - Customer Project Name
            $table->string('nombre_proyecto_cliente')->nullable();

            // K - Core Business
            $table->string('core_business')->nullable();

            // L - Pipe (in)
            $table->decimal('pipe_in', 8, 2)->nullable();

            // M - Branch (in)
            $table->decimal('branch_in', 8, 2)->nullable();

            // N - Long Description
            $table->text('descripcion_larga')->nullable();

            // O - Amount (USD)
            $table->decimal('amount_usd', 15, 2)->nullable();

            // P - Amount (MXN)
            $table->decimal('amount_mxn', 15, 2)->nullable();

            // Q - Quotation Personnel
            $table->string('quotation_personnel')->nullable();

            // R - Account Manager
            $table->string('account_manager')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tech_references');
    }
};
