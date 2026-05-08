<?php

namespace Tests\Unit;

use App\Models\RhRoleMapping;
use App\Models\User;
use App\Services\Auth\RoleMapper;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RoleMapperTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $permissions = ['ver dashboard', 'ver proyectos'];
        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        $roles = ['direccion_general', 'director_dn', 'gerente_proyectos', 'ingeniero_proyectos', 'ingeniero_costos', 'gerente_operaciones', 'cfo', 'comercial'];
        foreach ($roles as $roleName) {
            Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
        }

        RhRoleMapping::create(['puesto_rh' => '%Director General%', 'rol_sistema' => 'direccion_general', 'prioridad' => 100, 'activo' => true]);
        RhRoleMapping::create(['puesto_rh' => '%Director%Desarrollo de Negocios%', 'rol_sistema' => 'director_dn', 'prioridad' => 90, 'activo' => true]);
        RhRoleMapping::create(['puesto_rh' => '%Gerente de Proyectos%', 'rol_sistema' => 'gerente_proyectos', 'prioridad' => 80, 'activo' => true]);
        RhRoleMapping::create(['puesto_rh' => '%Gerente de Operaciones%', 'rol_sistema' => 'gerente_operaciones', 'prioridad' => 80, 'activo' => true]);
        RhRoleMapping::create(['puesto_rh' => '%Ingeniero de Costos%', 'rol_sistema' => 'ingeniero_costos', 'prioridad' => 70, 'activo' => true]);
        RhRoleMapping::create(['puesto_rh' => '%Ingeniero de Proyectos%', 'rol_sistema' => 'ingeniero_proyectos', 'prioridad' => 60, 'activo' => true]);
        RhRoleMapping::create(['puesto_rh' => '%CFO%', 'rol_sistema' => 'cfo', 'prioridad' => 90, 'activo' => true]);
        RhRoleMapping::create(['puesto_rh' => '%Comercial%', 'rol_sistema' => 'comercial', 'prioridad' => 60, 'activo' => true]);
    }

    private function createUser(array $overrides = []): User
    {
        return User::create(array_merge([
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'password' => Hash::make('password'),
            'status' => 'active',
        ], $overrides));
    }

    public function test_maps_director_general(): void
    {
        $mapper = new RoleMapper();
        $user = $this->createUser([
            'puesto' => 'Director General',
            'departamento' => 'Dirección',
        ]);

        $this->assertEquals('direccion_general', $mapper->resolveRoleForUser($user));
    }

    public function test_maps_gerente_proyectos(): void
    {
        $mapper = new RoleMapper();
        $user = $this->createUser([
            'puesto' => 'Gerente de Proyectos Senior',
            'departamento' => 'Proyectos',
        ]);

        $this->assertEquals('gerente_proyectos', $mapper->resolveRoleForUser($user));
    }

    public function test_maps_cfo(): void
    {
        $mapper = new RoleMapper();
        $user = $this->createUser([
            'puesto' => 'CFO',
            'departamento' => 'Finanzas',
        ]);

        $this->assertEquals('cfo', $mapper->resolveRoleForUser($user));
    }

    public function test_fallback_when_no_match(): void
    {
        $mapper = new RoleMapper();
        $user = $this->createUser([
            'puesto' => 'Puesto Inexistente',
            'departamento' => 'Algo',
        ]);

        $this->assertEquals('ingeniero_proyectos', $mapper->resolveRoleForUser($user));
    }

    public function test_fallback_when_empty_puesto(): void
    {
        $mapper = new RoleMapper();
        $user = $this->createUser([
            'puesto' => null,
            'departamento' => null,
        ]);

        $this->assertEquals('ingeniero_proyectos', $mapper->resolveRoleForUser($user));
    }

    public function test_departamento_filter_applies(): void
    {
        RhRoleMapping::create([
            'puesto_rh' => '%Director%',
            'rol_sistema' => 'director_dn',
            'prioridad' => 95,
            'departamento_filter' => 'Comercial',
            'activo' => true,
        ]);

        $mapper = new RoleMapper();

        $userInComercial = $this->createUser([
            'puesto' => 'Director de Desarrollo de Negocios',
            'departamento' => 'Comercial',
        ]);

        $this->assertEquals('director_dn', $mapper->resolveRoleForUser($userInComercial));
    }

    public function test_inactive_rule_is_ignored(): void
    {
        RhRoleMapping::create([
            'puesto_rh' => '%Test Inactivo%',
            'rol_sistema' => 'cfo',
            'prioridad' => 200,
            'activo' => false,
        ]);

        $mapper = new RoleMapper();
        $user = $this->createUser([
            'puesto' => 'Test Inactivo Role',
            'departamento' => 'Finanzas',
        ]);

        // Inactive rule should be ignored, so it falls through to other matching rules or fallback
        $this->assertEquals('ingeniero_proyectos', $mapper->resolveRoleForUser($user));
    }
}