<?php

namespace Database\Factories\Proyectos;

use App\Models\Proyectos\Cotizacion;
use App\Models\Proyectos\Proyecto;
use Illuminate\Database\Eloquent\Factories\Factory;

class CotizacionFactory extends Factory
{
    protected $model = Cotizacion::class;

    public function definition(): array
    {
        $costoDirecto = fake()->randomFloat(2, 50000, 5000000);
        $factorIndirectos = fake()->randomFloat(4, 0.05, 0.15);
        $factorAdmin = fake()->randomFloat(4, 0.05, 0.10);
        $factorUtilidad = fake()->randomFloat(4, 0.08, 0.20);
        $precioVentaCalculado = $costoDirecto * (1 + $factorIndirectos + $factorAdmin + $factorUtilidad);

        return [
            'proyecto_id' => Proyecto::factory(),
            'version' => fake()->numberBetween(1, 5),
            'costo_directo' => $costoDirecto,
            'factor_indirectos' => $factorIndirectos,
            'factor_admin' => $factorAdmin,
            'factor_utilidad' => $factorUtilidad,
            'precio_venta_calculado' => $precioVentaCalculado,
            'precio_venta_final' => $precioVentaCalculado,
            'moneda' => 'USD',
            'status' => fake()->randomElement(['borrador', 'revision', 'interno_aprobado', 'presentado', 'aprobado', 'rechazado']),
            'fecha_emision' => fake()->optional()->dateTimeBetween('-3 months', 'now')?->format('Y-m-d'),
            'observaciones' => fake()->optional()->sentence(),
        ];
    }

    public function adjudicada(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => 'aprobado',
        ]);
    }
}