<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Acrónimos del personal (vincula usuario del sistema con su sigla en cotizaciones)
        Schema::create('personnel_acronyms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('acronym', 50)->unique();        // Sigla (LJSM, JCHZ…)
            $table->string('account_manager', 50)->nullable(); // Sigla de account manager (puede diferir)
            $table->timestamps();
        });

        // Catálogo de estados / zonas / países
        Schema::create('countries', function (Blueprint $table) {
            $table->id();
            $table->string('state');            // Estado / provincia (Aguascalientes)
            $table->string('zone')->nullable(); // Zona comercial (Centro, Norte, Sur…)
            $table->string('country', 10)->default('MEX'); // Código de país
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('countries');
        Schema::dropIfExists('personnel_acronyms');
    }
};
