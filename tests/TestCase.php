<?php

namespace Tests;

use App\Models\Comercial\Cliente;
use App\Models\Comercial\Sublinea;
use App\Models\Proyectos\Proyecto;
use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Hash;

abstract class TestCase extends BaseTestCase
{
    protected ?User $authUser = null;

    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function actingAsSuperAdmin(): User
    {
        return $this->authUser = User::factory()->superAdmin()->create();
    }

    protected function actingAsGerenteProyectos(): User
    {
        return $this->authUser = User::factory()->gerenteProyectos()->create();
    }

    protected function actingAsCfo(): User
    {
        return $this->authUser = User::factory()->cfo()->create();
    }

    protected function actingAsRole(string $role): User
    {
        $user = User::factory()->create();
        $user->assignRole($role);
        return $this->authUser = $user;
    }

    protected function createCliente(array $overrides = []): Cliente
    {
        return Cliente::create(array_merge([
            'razon_social' => 'Cliente Test SA',
            'alias_3letras' => 'CTE',
            'sector' => 'Gobierno',
            'activo' => true,
        ], $overrides));
    }

    protected function createSublinea(array $overrides = []): Sublinea
    {
        return Sublinea::create(array_merge([
            'codigo' => 'HTP',
            'nombre' => 'Heat Transfer Products',
        ], $overrides));
    }

    protected function createProyecto(array $overrides = []): Proyecto
    {
        $cliente = $overrides['cliente_id'] ?? Cliente::create([
            'razon_social' => 'Proyecto Test Cliente',
            'alias_3letras' => 'PTC',
            'sector' => 'Energía',
            'activo' => true,
        ]);

        if (!isset($overrides['cliente_id'])) {
            $overrides['cliente_id'] = $cliente instanceof Cliente ? $cliente->id : $cliente;
        }

        $sublinea = $overrides['sublinea_id'] ?? Sublinea::create([
            'codigo' => 'TST',
            'nombre' => 'Test Sublinea',
        ]);

        if (!isset($overrides['sublinea_id'])) {
            $overrides['sublinea_id'] = $sublinea instanceof Sublinea ? $sublinea->id : $sublinea;
        }

        return Proyecto::create(array_merge([
            'tech_reference' => 'TR-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT),
            'cp_numero' => 'CP-' . now()->format('y') . '-' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT),
            'año' => now()->year,
            'estado' => 'en_ejecucion',
            'metodo_distribucion_plurianual' => 'dias_naturales',
        ], $overrides));
    }
}
