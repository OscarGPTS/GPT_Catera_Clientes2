<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('secuencias')) {
            return;
        }

        Schema::create('secuencias', function (Blueprint $table) {
            $table->id();
            $table->string('tipo');
            $table->year('anio');
            $table->integer('ultimo_consecutivo')->default(0);
            $table->timestamps();

            $table->unique(['tipo', 'anio']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('secuencias');
    }
};