<?php

namespace Tests\Feature\Proyectos;

use App\Models\User;
use App\Models\Proyectos\Proyecto;
use App\Models\Proyectos\BitacoraDiaria;
use App\Models\Proyectos\AsignacionPersona;
use App\Models\Comercial\Cliente;
use App\Models\Comercial\Sublinea;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BitacoraFormTest extends TestCase
{
    use RefreshDatabase;

    private Proyecto $proyecto;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RolesPermissionsSeeder::class);

        $cliente = Cliente::create([
            'razon_social' => 'Cliente Bitacora SA',
            'alias_3letras' => 'CBT',
            'sector' => 'Industrial',
            'activo' => true,
        ]);

        $sublinea = Sublinea::create([
            'codigo' => 'HTP',
            'nombre' => 'Heat Transfer Products',
        ]);

        $this->user = User::create([
            'name' => 'Test User',
            'email' => 'test@test.com',
            'password' => bcrypt('password'),
            'puesto' => 'Ingeniero',
        ]);

        $this->proyecto = Proyecto::create([
            'cp_numero' => 'CP-26-001',
            'año' => 2026,
            'cliente_id' => $cliente->id,
            'sublinea_id' => $sublinea->id,
            'estado' => 'en_ejecucion',
        ]);
    }

    public function test_bitacora_page_renders_for_authorized_user()
    {
        $this->user->givePermissionTo('ver bitacora');
        $response = $this->actingAs($this->user)->get('/bitacora');
        $response->assertStatus(200);
        $response->assertSee('Bitácora Diaria');
    }

    public function test_bitacora_page_requires_authentication()
    {
        $response = $this->get('/bitacora');
        $response->assertRedirect(route('login'));
    }

    public function test_can_save_bitacora_as_draft()
    {
        $bitacora = BitacoraDiaria::create([
            'proyecto_id' => $this->proyecto->id,
            'fecha' => '2026-05-01',
            'relacion_actividades' => 'Soldadura de tuberías',
            'personal_gpt' => [['nombre' => 'Juan', 'horas' => 8]],
            'equipos_en_sitio' => [['nombre' => 'Grúa', 'en_sitio' => true]],
            'cargado_por_id' => $this->user->id,
        ]);

        $this->assertDatabaseHas('bitacora_diaria', [
            'id' => $bitacora->id,
        ]);
        $this->assertNull($bitacora->fresh()->firmado_at);
    }

    public function test_can_save_bitacora_as_signed()
    {
        $bitacora = BitacoraDiaria::create([
            'proyecto_id' => $this->proyecto->id,
            'fecha' => '2026-05-02',
            'relacion_actividades' => 'Pruebas hidrostáticas',
            'cargado_por_id' => $this->user->id,
            'firmado_at' => now(),
        ]);

        $this->assertNotNull($bitacora->fresh()->firmado_at);
    }

    public function test_duplicate_bitacora_for_same_project_and_date_is_prevented()
    {
        BitacoraDiaria::create([
            'proyecto_id' => $this->proyecto->id,
            'fecha' => '2026-05-03',
            'relacion_actividades' => 'Primera bitácora',
            'cargado_por_id' => $this->user->id,
        ]);

        $this->expectException(\Illuminate\Database\QueryException::class);
        BitacoraDiaria::create([
            'proyecto_id' => $this->proyecto->id,
            'fecha' => '2026-05-03',
            'relacion_actividades' => 'Segunda bitácora duplicada',
            'cargado_por_id' => $this->user->id,
        ]);
    }

    public function test_bitacora_stores_json_fields()
    {
        $bitacora = BitacoraDiaria::create([
            'proyecto_id' => $this->proyecto->id,
            'fecha' => '2026-05-04',
            'relacion_actividades' => 'Actividades varias',
            'personal_gpt' => [['nombre' => 'Juan Pérez', 'horas' => 8, 'extra' => false]],
            'equipos_en_sitio' => [['nombre' => 'Grúa 50T', 'en_sitio' => true, 'estado' => 'operativo']],
            'proveedores_subcontratistas' => [['nombre' => 'Pedro', 'empresa' => 'Sub SA', 'horas' => 6]],
            'cargado_por_id' => $this->user->id,
        ]);

        $fresh = $bitacora->fresh();
        $this->assertCount(1, $fresh->personal_gpt);
        $this->assertEquals('Juan Pérez', $fresh->personal_gpt[0]['nombre']);
        $this->assertCount(1, $fresh->equipos_en_sitio);
        $this->assertCount(1, $fresh->proveedores_subcontratistas);
    }

    public function test_proyecto_has_asignaciones_relationship()
    {
        AsignacionPersona::create([
            'user_id' => $this->user->id,
            'proyecto_id' => $this->proyecto->id,
            'rol' => 'ingeniero_proyectos',
            'status' => 'activo',
        ]);

        $this->assertCount(1, $this->proyecto->fresh()->asignaciones);
        $this->assertEquals('ingeniero_proyectos', $this->proyecto->fresh()->asignaciones->first()->rol);
    }

    public function test_proyecto_has_bitacoras_relationship()
    {
        BitacoraDiaria::create([
            'proyecto_id' => $this->proyecto->id,
            'fecha' => '2026-05-05',
            'relacion_actividades' => 'Test',
            'cargado_por_id' => $this->user->id,
        ]);

        $this->assertCount(1, $this->proyecto->fresh()->bitacoras);
    }
}