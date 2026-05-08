<?php

namespace Database\Factories\Proyectos;

use App\Models\Proyectos\Proyecto;
use App\Models\Comercial\Cliente;
use App\Models\Comercial\Sublinea;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProyectoFactory extends Factory
{
    protected $model = Proyecto::class;

    public function definition(): array
    {
        $estados = [
            'en_revision', 'cotizando', 'cotizado', 'presentado',
            'adjudicado_pendiente', 'adjudicado_firmado', 'en_ejecucion',
            'en_cierre', 'cerrado', 'cancelado', 'perdido', 'archivado',
        ];

        return [
            'tech_reference' => fake()->unique()->bothify('TR-####'),
            'cp_numero' => 'CP-' . now()->format('y') . '-' . str_pad(fake()->unique()->numberBetween(1, 999), 3, '0', STR_PAD_LEFT),
            'dn_numero' => fake()->optional(0.7)->bothify('DN-####'),
            'año' => now()->year,
            'cliente_id' => Cliente::factory(),
            'sublinea_id' => Sublinea::factory(),
            'usuario_final' => fake()->company(),
            'sector' => fake()->randomElement(['Petróleo', 'Energía', 'Industrial', 'Gobierno']),
            'estado' => fake()->randomElement($estados),
            'fecha_inicio_planeada' => fake()->optional()->dateTimeBetween('-6 months', '+3 months')?->format('Y-m-d'),
            'fecha_fin_planeada' => fake()->optional()->dateTimeBetween('+3 months', '+18 months')?->format('Y-m-d'),
            'metodo_distribucion_plurianual' => fake()->randomElement(['dias_naturales', 'hitos']),
            'notas' => fake()->optional()->sentence(),
        ];
    }

    public function enEjecucion(): static
    {
        return $this->state(fn(array $attributes) => [
            'estado' => 'en_ejecucion',
        ]);
    }

    public function adjudicado(): static
    {
        return $this->state(fn(array $attributes) => [
            'estado' => 'adjudicado_firmado',
        ]);
    }

    public function cotizando(): static
    {
        return $this->state(fn(array $attributes) => [
            'estado' => 'cotizando',
        ]);
    }

    public function conEquipo(): static
    {
        return $this->state(function (array $attributes) {
            $gerente = User::factory()->create();
            $gerente->assignRole('gerente_proyectos');
            $ingeniero = User::factory()->create();
            $ingeniero->assignRole('ingeniero_proyectos');

            return [
                'gerente_proyectos_id' => $gerente->id,
                'ingeniero_proyectos_id' => $ingeniero->id,
            ];
        });
    }
}