<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cartas_finiquito', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proyecto_id')->constrained('proyectos')->cascadeOnDelete();
            $table->date('fecha_emision');
            $table->json('personal_liberado')->nullable();
            $table->json('equipos_liberados')->nullable();
            $table->text('observaciones')->nullable();
            $table->string('pdf_path')->nullable();
            $table->timestamp('firmado_cliente_at')->nullable();
            $table->timestamp('firmado_gpt_at')->nullable();
            $table->timestamps();
        });

        Schema::create('post_mortem', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proyecto_id')->constrained('proyectos')->cascadeOnDelete();
            $table->date('fecha_sesion');
            $table->json('participantes')->nullable();
            $table->json('lecciones_aprendidas')->nullable();
            $table->decimal('desviaciones_costo', 15, 2)->nullable();
            $table->integer('desviaciones_tiempo')->nullable();
            $table->text('desviaciones_calidad')->nullable();
            $table->decimal('presupuesto_planeado', 15, 2)->nullable();
            $table->decimal('presupuesto_real', 15, 2)->nullable();
            $table->json('recomendaciones_mejora')->nullable();
            $table->string('pdf_path')->nullable();
            $table->timestamps();
        });

        Schema::create('asignaciones_personas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->integer('mes');
            $table->year('anio');
            $table->integer('cp_asignados')->default(0);
            $table->integer('cp_ejecutados')->default(0);
            $table->integer('cp_remanentes')->default(0);
            $table->integer('cp_residual_anterior')->default(0);
            $table->integer('dn_activos')->default(0);
            $table->integer('dn_stand_by')->default(0);
            $table->integer('dn_cerrados')->default(0);
            $table->integer('dn_cancelados')->default(0);
            $table->integer('total_servicio')->default(0);
            $table->integer('total_suministro')->default(0);
            $table->enum('gerencia_regional', ['GRC', 'GRS', 'GRN', 'DG', 'GPT-IM'])->nullable();
            $table->timestamp('generado_at')->useCurrent();
            $table->timestamps();
            $table->unique(['user_id', 'mes', 'anio']);
        });

        Schema::create('cuentas_bancarias', function (Blueprint $table) {
            $table->id();
            $table->enum('banco', ['BBVA', 'Banorte', 'Banamex', 'Santander', 'otro']);
            $table->string('alias')->nullable();
            $table->string('numero_cuenta_enmascarado')->nullable();
            $table->string('clabe_enmascarada')->nullable();
            $table->string('moneda', 3)->default('MXN');
            $table->boolean('activa')->default(true);
            $table->timestamps();
        });

        Schema::create('estados_cuenta', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cuenta_id')->constrained('cuentas_bancarias');
            $table->integer('mes');
            $table->year('anio');
            $table->string('archivo_origen_path')->nullable();
            $table->timestamp('parseado_at')->nullable();
            $table->integer('total_movimientos')->default(0);
            $table->timestamps();
        });

        Schema::create('movimientos_bancarios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('estado_cuenta_id')->constrained('estados_cuenta')->cascadeOnDelete();
            $table->date('fecha');
            $table->text('descripcion');
            $table->decimal('monto', 15, 2);
            $table->enum('tipo', ['cargo', 'abono']);
            $table->foreignId('conciliado_con_proyecto_id')->nullable()->constrained('proyectos');
            $table->string('conciliado_con_factura')->nullable();
            $table->timestamps();
        });

        Schema::create('cierres_mensuales', function (Blueprint $table) {
            $table->id();
            $table->integer('mes');
            $table->year('anio');
            $table->enum('tipo', ['contable_sat', 'gerencial_avance']);
            $table->date('fecha_corte');
            $table->enum('status', ['borrador', 'generado', 'aprobado'])->default('borrador');
            $table->foreignId('generado_por_id')->nullable()->constrained('users');
            $table->foreignId('aprobado_por_id')->nullable()->constrained('users');
            $table->text('observaciones')->nullable();
            $table->timestamps();
        });

        Schema::create('cierres_secciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cierre_id')->constrained('cierres_mensuales')->cascadeOnDelete();
            $table->enum('codigo', ['sat_base', 'devengado', 'pipeline_ponderado']);
            $table->decimal('total', 15, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('cierres_lineas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seccion_id')->constrained('cierres_secciones')->cascadeOnDelete();
            $table->foreignId('proyecto_id')->nullable()->constrained('proyectos');
            $table->decimal('monto', 15, 2)->default(0);
            $table->decimal('porcentaje_aplicado', 5, 2)->default(100);
            $table->text('observaciones')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cierres_lineas');
        Schema::dropIfExists('cierres_secciones');
        Schema::dropIfExists('cierres_mensuales');
        Schema::dropIfExists('movimientos_bancarios');
        Schema::dropIfExists('estados_cuenta');
        Schema::dropIfExists('cuentas_bancarias');
        Schema::dropIfExists('asignaciones_personas');
        Schema::dropIfExists('post_mortem');
        Schema::dropIfExists('cartas_finiquito');
    }
};
