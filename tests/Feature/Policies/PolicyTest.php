<?php

namespace Tests\Feature\Policies;

use App\Models\User;
use App\Models\Comercial\Cliente;
use App\Models\Proyectos\Proyecto;
use App\Models\Proyectos\Cotizacion;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PolicyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RolesPermissionsSeeder::class);
    }

    protected function makeProyecto(): Proyecto
    {
        return $this->createProyecto([
            'tech_reference' => 'REF-POL-' . random_int(1000, 9999),
            'estado' => 'en_revision',
        ]);
    }

    public function test_cliente_policy_view_any(): void
    {
        $gerente = User::factory()->create();
        $gerente->assignRole('gerente_proyectos');

        $this->assertTrue($gerente->can('viewAny', Cliente::class));
    }

    public function test_cliente_policy_create(): void
    {
        $comercial = User::factory()->create();
        $comercial->assignRole('comercial');

        $this->assertTrue($comercial->can('create', Cliente::class));
    }

    public function test_cliente_policy_delete_requires_super_admin(): void
    {
        $cliente = Cliente::create([
            'razon_social' => 'Test Delete',
            'alias_3letras' => 'TD',
            'activo' => true,
        ]);

        $superAdmin = User::factory()->create();
        $superAdmin->assignRole('super_admin');

        $gerente = User::factory()->create();
        $gerente->assignRole('gerente_proyectos');

        $this->assertTrue($superAdmin->can('delete', $cliente));
        $this->assertFalse($gerente->can('delete', $cliente));
    }

    public function test_cotizacion_policy_view_any(): void
    {
        $ingeniero = User::factory()->create();
        $ingeniero->assignRole('ingeniero_costos');

        $this->assertTrue($ingeniero->can('viewAny', Cotizacion::class));
    }

    public function test_cotizacion_policy_create(): void
    {
        $ingeniero = User::factory()->create();
        $ingeniero->assignRole('ingeniero_costos');

        $this->assertTrue($ingeniero->can('create', Cotizacion::class));
    }

    public function test_cotizacion_policy_update_with_instance(): void
    {
        $proyecto = $this->makeProyecto();
        $cotizacion = Cotizacion::create([
            'proyecto_id' => $proyecto->id,
            'version' => 1,
            'costo_directo' => 100000,
            'factor_indirectos' => 0.10,
            'factor_admin' => 0.12,
            'factor_utilidad' => 0.25,
            'precio_venta_calculado' => 154000,
            'precio_venta_final' => 154000,
        ]);

        $ingeniero = User::factory()->create();
        $ingeniero->assignRole('ingeniero_costos');

        $this->assertTrue($ingeniero->can('update', $cotizacion));
    }

    public function test_trainee_cannot_create_cotizacion(): void
    {
        $trainee = User::factory()->create();
        $trainee->assignRole('trainee_proyectos');

        $this->assertFalse($trainee->can('create', Cotizacion::class));
    }

    public function test_proyecto_policy_ver_cotizacion(): void
    {
        $proyecto = $this->makeProyecto();

        $gerente = User::factory()->create();
        $gerente->assignRole('gerente_proyectos');

        $this->assertTrue($gerente->can('verCotizacion', $proyecto));
    }
}