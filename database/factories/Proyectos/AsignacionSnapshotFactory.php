<?php

namespace Database\Factories\Proyectos;

use App\Models\Proyectos\AsignacionSnapshot;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AsignacionSnapshotFactory extends Factory
{
    protected $model = AsignacionSnapshot::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'mes' => fake()->numberBetween(1, 12),
            'anio' => now()->year,
            'cp_asignados' => fake()->numberBetween(1, 12),
            'cp_ejecutados' => fake()->numberBetween(0, 8),
            'cp_remanentes' => fake()->numberBetween(0, 4),
            'cp_residual_anterior' => fake()->numberBetween(0, 2),
            'dn_activos' => fake()->numberBetween(0, 8),
            'dn_stand_by' => fake()->numberBetween(0, 3),
            'dn_cerrados' => fake()->numberBetween(0, 5),
            'dn_cancelados' => fake()->numberBetween(0, 2),
            'total_servicio' => fake()->randomFloat(2, 10000, 5000000),
            'total_suministro' => fake()->randomFloat(2, 5000, 2000000),
            'gerencia_regional' => fake()->randomElement(['GRC', 'GRS', 'GRN', 'DG', 'GPT-IM']),
        ];
    }
}