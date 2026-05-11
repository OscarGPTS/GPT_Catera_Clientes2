<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Already executed via fix_anio.php script
        // This migration records the column rename from 'año' (corrupted encoding) to 'anio'
    }

    public function down(): void
    {
        $tables = ['proyectos', 'cierres_mensuales', 'secuencias'];
        foreach ($tables as $table) {
            if (Schema::hasColumn($table, 'anio')) {
                DB::statement("ALTER TABLE `{$table}` CHANGE `anio` `anio` YEAR NOT NULL");
            }
        }
    }
};