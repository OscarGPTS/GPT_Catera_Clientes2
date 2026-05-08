<?php

namespace Database\Factories\Proyectos;

use App\Models\Proyectos\CotizacionPartida;
use App\Models\Proyectos\Cotizacion;
use Illuminate\Database\Eloquent\Factories\Factory;

class CotizacionPartidaFactory extends Factory
{
    protected $model = CotizacionPartida::class;

    public function definition(): array
    {
        $cantidad = fake()->randomFloat(2, 1, 100);
        $costoUnitario = fake()->randomFloat(2, 100, 50000);
        $costoTotal = $cantidad * $costoUnitario;

        return [
            'cotizacion_id' => Cotizacion::factory(),
            'numero_partida' => fake()->numberBetween(1, 50),
            'descripcion' => fake()->sentence(),
            'cantidad' => $cantidad,
            'unidad' => fake()->randomElement(['pza', 'kg', 'm', 'l', 'jgo', 'lote', 'hr']),
            'costo_unitario' => $costoUnitario,
            'costo_total' => $costoTotal,
            'observaciones' => fake()->optional()->sentence(),
        ];
    }
}