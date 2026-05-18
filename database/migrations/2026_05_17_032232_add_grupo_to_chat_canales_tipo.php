<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE chat_canales MODIFY COLUMN tipo ENUM('proyecto','departamento','direccion','privado','grupo') NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE chat_canales MODIFY COLUMN tipo ENUM('proyecto','departamento','direccion','privado') NOT NULL");
    }
};