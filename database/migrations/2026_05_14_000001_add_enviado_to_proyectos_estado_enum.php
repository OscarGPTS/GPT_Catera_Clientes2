<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Agrega 'enviado' al ENUM de estado en proyectos
        DB::statement("
            ALTER TABLE proyectos
            MODIFY COLUMN estado ENUM(
                'en_revision','cotizando','cotizado','enviado','presentado',
                'adjudicado_pendiente','adjudicado_firmado','en_ejecucion',
                'en_cierre','cerrado','cancelado','perdido','archivado'
            ) NOT NULL DEFAULT 'en_revision'
        ");
    }

    public function down(): void
    {
        // Remueve 'enviado' (registros con ese estado pasan a 'presentado')
        DB::statement("UPDATE proyectos SET estado = 'presentado' WHERE estado = 'enviado'");

        DB::statement("
            ALTER TABLE proyectos
            MODIFY COLUMN estado ENUM(
                'en_revision','cotizando','cotizado','presentado',
                'adjudicado_pendiente','adjudicado_firmado','en_ejecucion',
                'en_cierre','cerrado','cancelado','perdido','archivado'
            ) NOT NULL DEFAULT 'en_revision'
        ");
    }
};
