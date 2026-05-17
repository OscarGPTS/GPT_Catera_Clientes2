<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ponderaciones', function (Blueprint $table) {
            $table->id();
            $table->string('concepto', 100)->unique();       // PERDIDA/PRESUPUESTAL, REMOTO, POSIBLE, PROBABLE, CONTRATADO
            $table->unsignedTinyInteger('porcentaje');       // 0, 10, 25, 75, 100
            $table->string('color', 32)->default('slate');   // Tailwind color base (slate, red, blue, yellow, green)
            $table->unsignedSmallInteger('orden')->default(0);
            $table->boolean('status')->default(true)->index();
            $table->timestamps();
        });

        // Seed inicial con los 5 conceptos estándar
        $now = now();
        DB::table('ponderaciones')->insert([
            ['concepto' => 'PERDIDA/PRESUPUESTAL', 'porcentaje' => 0,   'color' => 'red',    'orden' => 1, 'status' => true, 'created_at' => $now, 'updated_at' => $now],
            ['concepto' => 'REMOTO',               'porcentaje' => 10,  'color' => 'red',    'orden' => 2, 'status' => true, 'created_at' => $now, 'updated_at' => $now],
            ['concepto' => 'POSIBLE',              'porcentaje' => 25,  'color' => 'blue',   'orden' => 3, 'status' => true, 'created_at' => $now, 'updated_at' => $now],
            ['concepto' => 'PROBABLE',             'porcentaje' => 75,  'color' => 'yellow', 'orden' => 4, 'status' => true, 'created_at' => $now, 'updated_at' => $now],
            ['concepto' => 'CONTRATADO',           'porcentaje' => 100, 'color' => 'green',  'orden' => 5, 'status' => true, 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('ponderaciones');
    }
};
