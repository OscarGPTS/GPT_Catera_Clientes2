<?php

namespace Database\Factories\Proyectos;

use App\Models\Proyectos\BitacoraDiaria;
use App\Models\Proyectos\Proyecto;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class BitacoraDiariaFactory extends Factory
{
    protected $model = BitacoraDiaria::class;

    public function definition(): array
    {
        return [
            'proyecto_id' => Proyecto::factory(),
            'fecha' => fake()->dateTimeBetween('-1 month', 'now')->format('Y-m-d'),
            'relacion_actividades' => fake()->paragraph(),
            'personal_gpt' => [fake()->name(), fake()->name()],
            'equipos_en_sitio' => [fake()->word() . ' ' . fake()->randomNumber(3)],
            'proveedores_subcontratistas' => [fake()->company()],
            'vobo_cliente_nombre' => fake()->optional()->name(),
            'vobo_cliente_organizacion' => fake()->optional()->company(),
            'vobo_cliente_fecha' => fake()->optional()->dateTimeBetween('-1 month', 'now')?->format('Y-m-d'),
            'cargado_por_id' => User::factory(),
        ];
    }
}