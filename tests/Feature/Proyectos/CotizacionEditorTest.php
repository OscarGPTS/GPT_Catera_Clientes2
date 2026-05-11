<?php

namespace Tests\Feature\Proyectos;

use App\Models\User;
use App\Models\Proyectos\Proyecto;
use App\Models\Proyectos\Cotizacion;
use App\Models\Proyectos\CotizacionPartida;
use App\Models\Comercial\Cliente;
use App\Models\Comercial\Sublinea;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CotizacionEditorTest extends TestCase
{
    use RefreshDatabase;

    private Proyecto $proyecto;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RolesPermissionsSeeder::class);

        $cliente = Cliente::create([
            'razon_social' => 'Cliente Cotizacion SA',
            'alias_3letras' => 'CCT',
            'sector' => 'Industrial',
            'activo' => true,
        ]);

        $sublinea = Sublinea::create([
            'codigo' => 'HTP',
            'nombre' => 'Heat Transfer Products',
        ]);

        $this->user = User::create([
            'name' => 'Test User',
            'email' => 'test@cot.com',
            'password' => bcrypt('password'),
            'puesto' => 'Ingeniero',
        ]);

        $this->proyecto = Proyecto::create([
            'cp_numero' => 'CP-26-COT',
            'anio' => 2026,
            'cliente_id' => $cliente->id,
            'sublinea_id' => $sublinea->id,
            'estado' => 'en_ejecucion',
        ]);
    }

    public function test_cotizacion_page_renders_for_authorized_user()
    {
        $this->user->givePermissionTo('ver cotizaciones');
        $response = $this->actingAs($this->user)->get("/proyectos/{$this->proyecto->id}/cotizacion");
        $response->assertStatus(200);
        $response->assertSee('Cotización');
    }

    public function test_cotizacion_page_requires_authentication()
    {
        $response = $this->get("/proyectos/{$this->proyecto->id}/cotizacion");
        $response->assertRedirect(route('login'));
    }

    public function test_can_create_cotizacion_with_partidas()
    {
        $cotizacion = Cotizacion::create([
            'proyecto_id' => $this->proyecto->id,
            'version' => 1,
            'costo_directo' => 50000,
            'factor_indirectos' => 0.10,
            'factor_admin' => 0.12,
            'factor_utilidad' => 0.25,
            'precio_venta_calculado' => 77000,
            'precio_venta_final' => 77000,
            'moneda' => 'USD',
            'status' => 'borrador',
            'generado_por' => $this->user->id,
        ]);

        CotizacionPartida::create([
            'cotizacion_id' => $cotizacion->id,
            'numero_partida' => 1,
            'descripcion' => 'Suministro de materiales',
            'cantidad' => 10,
            'unidad' => 'pza',
            'costo_unitario' => 5000,
            'costo_total' => 50000,
        ]);

        $this->assertDatabaseHas('cotizaciones', [
            'id' => $cotizacion->id,
            'proyecto_id' => $this->proyecto->id,
            'version' => 1,
        ]);

        $this->assertEquals(1, $cotizacion->partidas()->count());
        $this->assertEquals(50000, $cotizacion->partidas->first()->costo_total);
    }

    public function test_cotizacion_calculadora_produces_correct_result()
    {
        $calculadora = new \App\Services\Cotizaciones\CalculadoraCoss();
        $resultado = $calculadora->calcular(
            costoDirecto: 61717.61,
            factorIndirectos: 0.10,
            factorAdmin: 0.12,
            factorUtilidad: 0.25,
        );

        $this->assertEquals(61717.61, $resultado->costoDirecto);
        $this->assertGreaterThan(0, $resultado->precioVenta);
        $this->assertGreaterThan(0, $resultado->margenNeto);
    }

    public function test_proyecto_has_cotizaciones_relationship()
    {
        Cotizacion::create([
            'proyecto_id' => $this->proyecto->id,
            'version' => 1,
            'costo_directo' => 10000,
            'factor_indirectos' => 0.10,
            'factor_admin' => 0.12,
            'factor_utilidad' => 0.25,
            'precio_venta_calculado' => 15400,
            'precio_venta_final' => 15400,
            'moneda' => 'USD',
            'status' => 'borrador',
            'generado_por' => $this->user->id,
        ]);

        $this->assertCount(1, $this->proyecto->fresh()->cotizaciones);
    }
}