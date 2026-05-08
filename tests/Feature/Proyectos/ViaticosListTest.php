<?php

namespace Tests\Feature\Proyectos;

use App\Models\User;
use App\Models\Proyectos\Proyecto;
use App\Models\Proyectos\SolicitudViatico;
use App\Models\Comercial\Cliente;
use App\Models\Comercial\Sublinea;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ViaticosListTest extends TestCase
{
    use RefreshDatabase;

    private Proyecto $proyecto;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RolesPermissionsSeeder::class);

        $cliente = Cliente::create([
            'razon_social' => 'Cliente Viaticos SA',
            'alias_3letras' => 'CVT',
            'sector' => 'Industrial',
            'activo' => true,
        ]);

        $sublinea = Sublinea::create([
            'codigo' => 'HTP',
            'nombre' => 'Heat Transfer Products',
        ]);

        $this->user = User::create([
            'name' => 'Test User',
            'email' => 'test@viaticos.com',
            'password' => bcrypt('password'),
            'puesto' => 'Ingeniero',
        ]);

        $this->proyecto = Proyecto::create([
            'cp_numero' => 'CP-26-VTC',
            'año' => 2026,
            'cliente_id' => $cliente->id,
            'sublinea_id' => $sublinea->id,
            'estado' => 'en_ejecucion',
        ]);
    }

    public function test_viaticos_page_renders_for_authorized_user()
    {
        $this->user->givePermissionTo('solicitar viaticos');
        $response = $this->actingAs($this->user)->get('/viaticos');
        $response->assertStatus(200);
        $response->assertSee('Viáticos');
    }

    public function test_viaticos_page_requires_authentication()
    {
        $response = $this->get('/viaticos');
        $response->assertRedirect(route('login'));
    }

    public function test_solicitud_viatico_can_be_created()
    {
        $solicitud = SolicitudViatico::create([
            'proyecto_id' => $this->proyecto->id,
            'fecha_inicio' => '2026-06-01',
            'fecha_fin' => '2026-06-05',
            'destino' => 'Veracruz',
            'motivo' => 'Inspección de obra',
            'solicitante_id' => $this->user->id,
            'status' => 'pendiente_serv_grales',
            'monto_total' => 15000,
        ]);

        $this->assertDatabaseHas('solicitudes_viaticos', [
            'id' => $solicitud->id,
            'status' => 'pendiente_serv_grales',
        ]);
    }

    public function test_solicitud_viatico_can_be_approved_by_sg()
    {
        $solicitud = SolicitudViatico::create([
            'proyecto_id' => $this->proyecto->id,
            'fecha_inicio' => '2026-06-01',
            'fecha_fin' => '2026-06-05',
            'destino' => 'Veracruz',
            'solicitante_id' => $this->user->id,
            'status' => 'pendiente_serv_grales',
        ]);

        $solicitud->update([
            'status' => 'pendiente_direccion',
            'aprobado_por_id' => $this->user->id,
            'aprobado_at' => now(),
        ]);

        $this->assertEquals('pendiente_direccion', $solicitud->fresh()->status);
    }

    public function test_solicitud_viatico_can_be_rejected()
    {
        $solicitud = SolicitudViatico::create([
            'proyecto_id' => $this->proyecto->id,
            'fecha_inicio' => '2026-06-01',
            'fecha_fin' => '2026-06-05',
            'destino' => 'Veracruz',
            'solicitante_id' => $this->user->id,
            'status' => 'pendiente_serv_grales',
        ]);

        $solicitud->update([
            'status' => 'rechazado',
            'motivo_rechazo' => 'Presupuesto insuficiente',
        ]);

        $this->assertEquals('rechazado', $solicitud->fresh()->status);
        $this->assertEquals('Presupuesto insuficiente', $solicitud->fresh()->motivo_rechazo);
    }
}