<?php

namespace Database\Factories\Finanzas;

use App\Models\Finanzas\CierreMensual;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class CierreMensualFactory extends Factory
{
    protected $model = CierreMensual::class;

    public function definition(): array
    {
        return [
            'mes' => fake()->numberBetween(1, 12),
            'anio' => now()->year,
            'tipo' => fake()->randomElement(['contable_sat', 'gerencial_avance']),
            'fecha_corte' => fake()->dateTimeBetween('-1 month', 'now')->format('Y-m-d'),
            'status' => fake()->randomElement(['borrador', 'generado', 'aprobado']),
            'generado_por_id' => User::factory(),
            'observaciones' => fake()->optional()->sentence(),
        ];
    }

    public function generado(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => 'generado',
        ]);
    }

    public function aprobado(): static
    {
        return $this->state(function (array $attributes) {
            $aprobador = User::factory()->create();
            return [
                'status' => 'aprobado',
                'aprobado_por_id' => $aprobador->id,
            ];
        });
    }
}