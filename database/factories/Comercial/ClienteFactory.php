<?php

namespace Database\Factories\Comercial;

use App\Models\Comercial\Cliente;
use Illuminate\Database\Eloquent\Factories\Factory;

class ClienteFactory extends Factory
{
    protected $model = Cliente::class;

    public function definition(): array
    {
        return [
            'razon_social' => fake()->company(),
            'alias_3letras' => strtoupper(fake()->lexify('???')),
            'rfc' => strtoupper(fake()->bothify('???######???')),
            'sector' => fake()->randomElement(['Gobierno', 'Energía', 'Petróleo', 'Industrial', 'Construcción']),
            'segmento' => fake()->randomElement(['Grandes cuentas', 'Medianas empresas', 'PEMEX', 'CFE', 'Privado']),
            'activo' => true,
        ];
    }
}