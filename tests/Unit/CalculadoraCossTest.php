<?php

namespace Tests\Unit;

use App\Services\Cotizaciones\CalculadoraCoss;
use PHPUnit\Framework\TestCase;

class CalculadoraCossTest extends TestCase
{
    public function test_texmelucan_fixture_exacto(): void
    {
        $calc = new CalculadoraCoss;

        $resultado = $calc->calcular(
            costoDirecto: 61717.61,
            factorIndirectos: 0.12,
            factorAdmin: 0.15,
            factorUtilidad: 0.25,
        );

        $this->assertEquals(7406.11, $resultado->indirectos);
        $this->assertEquals(10368.56, $resultado->admin);
        $this->assertEquals(19873.07, $resultado->utilidad);
        $this->assertEquals(99365.35, $resultado->precioVenta);
        $this->assertEquals(20.0, $resultado->margenNeto);
    }

    public function test_coss_respeta_orden_de_aplicacion_de_factores(): void
    {
        $calc = new CalculadoraCoss;

        $resultado = $calc->calcular(
            costoDirecto: 100000,
            factorIndirectos: 0.10,
            factorAdmin: 0.10,
            factorUtilidad: 0.20,
        );

        $this->assertEquals(10000, $resultado->indirectos);
        $this->assertEquals(11000, $resultado->admin);
        $this->assertEquals(24200, $resultado->utilidad);
        $this->assertEquals(145200, $resultado->precioVenta);
    }

    public function test_coss_con_factores_cero(): void
    {
        $calc = new CalculadoraCoss;

        $resultado = $calc->calcular(
            costoDirecto: 50000,
            factorIndirectos: 0,
            factorAdmin: 0,
            factorUtilidad: 0,
        );

        $this->assertEquals(50000, $resultado->precioVenta);
        $this->assertEquals(0, $resultado->margenNeto);
    }

    public function test_coss_desde_partidas(): void
    {
        $calc = new CalculadoraCoss;

        $partidas = [
            ['descripcion' => 'Item 1', 'costo_total' => 30000],
            ['descripcion' => 'Item 2', 'costo_total' => 31717.61],
        ];

        $resultado = $calc->calcularDesdePartidas($partidas, [
            'indirectos' => 0.12,
            'admin' => 0.15,
            'utilidad' => 0.25,
        ]);

        $this->assertEquals(61717.61, $resultado->costoDirecto);
        $this->assertEquals(7406.11, $resultado->indirectos);
        $this->assertEquals(10368.56, $resultado->admin);
    }
}
