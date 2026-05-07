<?php

namespace App\Services\Cotizaciones;

readonly class ResultadoCoss
{
    public function __construct(
        public float $costoDirecto,
        public float $indirectos,
        public float $admin,
        public float $utilidad,
        public float $precioVenta,
        public float $margenNeto,
    ) {}
}
