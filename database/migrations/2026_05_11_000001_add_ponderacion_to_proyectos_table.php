<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('proyectos', function (Blueprint $table) {
            // Ponderación de adjudicación: 10=REMOTO, 25=POSIBLE, 50=PROBABLE, 75=CASI PROBABLE, 100=CONTRATADO
            $table->unsignedTinyInteger('ponderacion')->default(10)->after('estado');
        });
    }

    public function down(): void
    {
        Schema::table('proyectos', function (Blueprint $table) {
            $table->dropColumn('ponderacion');
        });
    }
};
