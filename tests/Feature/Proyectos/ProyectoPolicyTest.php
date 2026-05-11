<?php

namespace Tests\Feature\Proyectos;

use App\Models\User;
use App\Models\Proyectos\Proyecto;
use App\Models\Comercial\Cliente;
use App\Models\Comercial\Sublinea;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProyectoPolicyTest extends TestCase
{
    use RefreshDatabase;

    private Proyecto $proyecto;
    private User $superAdmin;
    private User $gerenteProyectos;
    private User $ingenieroCostos;
    private User $trainee;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\RolesPermissionsSeeder::class);

        $cliente = Cliente::create([
            'razon_social' => 'Cliente Test SA',
            'alias_3letras' => 'CTE',
            'sector' => 'Gobierno',
            'activo' => true,
        ]);

        $sublinea = Sublinea::create([
            'codigo' => 'HTP',
            'nombre' => 'Heat Transfer Products',
        ]);

        $this->proyecto = Proyecto::create([
            'tech_reference' => 'REF-001',
            'anio' => 2026,
            'cliente_id' => $cliente->id,
            'sublinea_id' => $sublinea->id,
            'estado' => 'en_revision',
        ]);

        $this->superAdmin = User::factory()->create();
        $this->superAdmin->assignRole('super_admin');

        $this->gerenteProyectos = User::factory()->create();
        $this->gerenteProyectos->assignRole('gerente_proyectos');

        $this->ingenieroCostos = User::factory()->create();
        $this->ingenieroCostos->assignRole('ingeniero_costos');

        $this->trainee = User::factory()->create();
        $this->trainee->assignRole('trainee_proyectos');
    }

    public function test_super_admin_can_view_any_proyecto(): void
    {
        $this->assertTrue($this->superAdmin->can('viewAny', Proyecto::class));
    }

    public function test_gerente_proyectos_can_view_any_proyecto(): void
    {
        $this->assertTrue($this->gerenteProyectos->can('viewAny', Proyecto::class));
    }

    public function test_trainee_can_view_any_proyecto(): void
    {
        $this->assertTrue($this->trainee->can('viewAny', Proyecto::class));
    }

    public function test_super_admin_can_update_proyecto(): void
    {
        $this->assertTrue($this->superAdmin->can('update', $this->proyecto));
    }

    public function test_gerente_proyectos_can_update_proyecto(): void
    {
        $this->assertTrue($this->gerenteProyectos->can('update', $this->proyecto));
    }

    public function test_trainee_cannot_update_proyecto(): void
    {
        $this->assertFalse($this->trainee->can('update', $this->proyecto));
    }

    public function test_super_admin_can_aprobar_cp(): void
    {
        $this->assertTrue($this->superAdmin->can('aprobarCp', $this->proyecto));
    }

    public function test_gerente_proyectos_can_aprobar_cp(): void
    {
        $this->assertTrue($this->gerenteProyectos->can('aprobarCp', $this->proyecto));
    }

    public function test_ingeniero_costos_cannot_aprobar_cp(): void
    {
        $this->assertFalse($this->ingenieroCostos->can('aprobarCp', $this->proyecto));
    }

    public function test_super_admin_can_adjudicar(): void
    {
        $this->assertTrue($this->superAdmin->can('adjudicar', $this->proyecto));
    }

    public function test_gerente_proyectos_can_adjudicar(): void
    {
        $this->assertTrue($this->gerenteProyectos->can('adjudicar', $this->proyecto));
    }

    public function test_trainee_cannot_adjudicar(): void
    {
        $this->assertFalse($this->trainee->can('adjudicar', $this->proyecto));
    }
}