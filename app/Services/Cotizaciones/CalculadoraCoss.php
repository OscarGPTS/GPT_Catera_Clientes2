<?php

namespace App\Services\Cotizaciones;

class CalculadoraCoss
{
    /**
     * Calcula el precio de venta usando el método COSS.
     * Replica las fórmulas del template FO-GPT-VTS-01-F.
     *
     * Caso de validación Texmelucan:
     * Costo directo: $61,717.61 → Precio venta: $96,998.30, margen 41.59%
     */
    public function calcular(
        float $costoDirecto,
        float $factorIndirectos,
        float $factorAdmin,
        float $factorUtilidad
    ): ResultadoCoss {
        $indirectos = round($costoDirecto * $factorIndirectos, 2);
        $admin = round(($costoDirecto + $indirectos) * $factorAdmin, 2);
        $base = $costoDirecto + $indirectos + $admin;
        $utilidad = round($base * $factorUtilidad, 2);
        $precioVenta = round($base + $utilidad, 2);
        $margenNeto = $precioVenta > 0 ? round(($utilidad / $precioVenta) * 100, 2) : 0;

        return new ResultadoCoss(
            costoDirecto: $costoDirecto,
            indirectos: $indirectos,
            admin: $admin,
            utilidad: $utilidad,
            precioVenta: $precioVenta,
            margenNeto: $margenNeto,
        );
    }

    /**
     * Calcula a partir de un array de partidas.
     */
    public function calcularDesdePartidas(array $partidas, array $factores): ResultadoCoss
    {
        $costoDirecto = array_sum(array_column($partidas, 'costo_total'));

        return $this->calcular(
            costoDirecto: $costoDirecto,
            factorIndirectos: $factores['indirectos'] ?? 0,
            factorAdmin: $factores['admin'] ?? 0,
            factorUtilidad: $factores['utilidad'] ?? 0,
        );
    }
}
