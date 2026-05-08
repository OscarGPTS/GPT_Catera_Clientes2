<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    protected $model = User::class;

    protected static ?string $password;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'departamento' => fake()->randomElement(['Proyectos', 'Comercial', 'Finanzas', 'Dirección', 'Operaciones', 'QHSE']),
            'puesto' => fake()->randomElement(['Ingeniero de Proyectos', 'Gerente de Proyectos', 'Director de Desarrollo de Negocios', 'Coordinador QHSE']),
            'status' => 'active',
            'es_socio' => false,
        ];
    }

    public function corporate(): static
    {
        return $this->state(fn (array $attributes) => [
            'email' => fake()->unique()->userName() . '@gptservices.com',
            'employee_id' => 'EMP-' . fake()->numberBetween(100, 999),
        ]);
    }

    public function socio(): static
    {
        return $this->state(fn (array $attributes) => [
            'es_socio' => true,
            'puesto' => 'Socio',
            'departamento' => 'Dirección',
        ]);
    }

    public function suspended(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'suspended',
        ]);
    }

    public function invited(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'invited',
        ]);
    }

    public function superAdmin(): static
    {
        return $this->state(fn(array $attributes) => [
            'departamento' => 'Dirección',
            'puesto' => 'Super Administrador',
        ])->afterCreating(function (User $user) {
            $user->assignRole('super_admin');
        });
    }

    public function gerenteProyectos(): static
    {
        return $this->state(fn(array $attributes) => [
            'departamento' => 'Proyectos',
            'puesto' => 'Gerente de Proyectos',
        ])->afterCreating(function (User $user) {
            $user->assignRole('gerente_proyectos');
        });
    }

    public function ingenieroProyectos(): static
    {
        return $this->state(fn(array $attributes) => [
            'departamento' => 'Proyectos',
            'puesto' => 'Ingeniero de Proyectos',
        ])->afterCreating(function (User $user) {
            $user->assignRole('ingeniero_proyectos');
        });
    }

    public function ingenieroCostos(): static
    {
        return $this->state(fn(array $attributes) => [
            'departamento' => 'Proyectos',
            'puesto' => 'Ingeniero de Costos',
        ])->afterCreating(function (User $user) {
            $user->assignRole('ingeniero_costos');
        });
    }

    public function directorDn(): static
    {
        return $this->state(fn(array $attributes) => [
            'departamento' => 'Dirección',
            'puesto' => 'Director DN',
        ])->afterCreating(function (User $user) {
            $user->assignRole('director_dn');
        });
    }

    public function cfo(): static
    {
        return $this->state(fn(array $attributes) => [
            'departamento' => 'Finanzas',
            'puesto' => 'CFO',
        ])->afterCreating(function (User $user) {
            $user->assignRole('cfo');
        });
    }
}