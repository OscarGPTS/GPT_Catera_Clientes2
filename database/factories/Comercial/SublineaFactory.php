<?php

namespace Database\Factories\Comercial;

use App\Models\Comercial\Sublinea;
use Illuminate\Database\Eloquent\Factories\Factory;

class SublineaFactory extends Factory
{
    protected $model = Sublinea::class;

    public function definition(): array
    {
        static $codigos = ['HTP', 'VAL', 'BOM', 'INS', 'TUB', 'FLD', 'MTR', 'ACC'];

        return [
            'codigo' => fake()->unique()->randomElement($codigos),
            'nombre' => fake()->randomElement([
                'Heat Transfer Products', 'Válvulas y Accesorios', 'Bombas Centrífugas',
                'Instrumentación', 'Tubería y Conexiones', 'Fldidos de Control',
                'Motores y Equipos', 'Accesorios Generales',
            ]),
            'descripcion' => fake()->sentence(),
        ];
    }
}