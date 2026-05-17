<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $tables = [
        'tech_references',
        'customers',
        'core_businesses',
        'personnel_acronyms',
        'countries',
        'varios',
        'sizes',
    ];

    public function up(): void
    {
        foreach ($this->tables as $table) {
            if (Schema::hasTable($table) && !Schema::hasColumn($table, 'status')) {
                Schema::table($table, function (Blueprint $t) {
                    $t->boolean('status')->default(true)->index();
                });
            }
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'status')) {
                Schema::table($table, function (Blueprint $t) {
                    $t->dropColumn('status');
                });
            }
        }
    }
};
