<?php

namespace Database\Factories\Proyectos;

use App\Models\Proyectos\LibroProyecto;
use App\Models\Proyectos\Proyecto;
use Illuminate\Database\Eloquent\Factories\Factory;

class LibroProyectoFactory extends Factory
{
    protected $model = LibroProyecto::class;

    public function definition(): array
    {
        return [
            'proyecto_id' => Proyecto::factory(),
            'fecha_apertura' => fake()->dateTimeBetween('-6 months', 'now')->format('Y-m-d'),
            'fecha_cierre_estimado' => fake()->optional()->dateTimeBetween('+1 month', '+12 months')?->format('Y-m-d'),
            'porcentaje_avance_global' => fake()->numberBetween(0, 100),
            'bloqueado_para_cierre' => false,
        ];
    }
}