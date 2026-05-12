<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('proyectos', function (Blueprint $table) {
            $table->string('contacto')->nullable()->after('usuario_final');
            $table->string('lugar')->nullable()->after('sector');
            $table->string('oferta_codigo')->nullable()->after('dn_numero');
            $table->date('fecha_envio')->nullable()->after('ponderacion');
            $table->date('fecha_modificacion_oferta')->nullable()->after('fecha_envio');
            $table->text('hitos_pago')->nullable()->after('fecha_modificacion_oferta');
            $table->string('archivo_oferta')->nullable()->after('hitos_pago');
            $table->unsignedBigInteger('elaboro_id')->nullable()->after('archivo_oferta');

            $table->foreign('elaboro_id')->references('id')->on('users');
        });
    }

    public function down(): void
    {
        Schema::table('proyectos', function (Blueprint $table) {
            $table->dropForeign(['elaboro_id']);
            $table->dropColumn([
                'contacto', 'lugar', 'oferta_codigo',
                'fecha_envio', 'fecha_modificacion_oferta',
                'hitos_pago', 'archivo_oferta', 'elaboro_id',
            ]);
        });
    }
};
