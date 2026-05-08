<?php

namespace Database\Factories\Proyectos;

use App\Models\Proyectos\SolicitudViatico;
use App\Models\Proyectos\Proyecto;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class SolicitudViaticoFactory extends Factory
{
    protected $model = SolicitudViatico::class;

    public function definition(): array
    {
        $fechaInicio = fake()->dateTimeBetween('-1 month', '+1 month');

        return [
            'proyecto_id' => Proyecto::factory(),
            'fecha_inicio' => $fechaInicio->format('Y-m-d'),
            'fecha_fin' => fake()->dateTimeInInterval($fechaInicio, '+7 days')->format('Y-m-d'),
            'destino' => fake()->city(),
            'motivo' => fake()->sentence(),
            'status' => fake()->randomElement(['borrador', 'pendiente_serv_grales', 'pendiente_direccion', 'aprobado', 'rechazado']),
            'solicitante_id' => User::factory(),
            'monto_total' => fake()->randomFloat(2, 1000, 50000),
        ];
    }

    public function pendiente(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => 'pendiente_serv_grales',
        ]);
    }

    public function aprobado(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => 'aprobado',
        ]);
    }
}