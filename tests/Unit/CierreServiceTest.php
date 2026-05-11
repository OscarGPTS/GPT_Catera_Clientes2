<?php

namespace Tests\Unit;

use App\Models\Comercial\Cliente;
use App\Models\Comercial\Sublinea;
use App\Models\Proyectos\Cotizacion;
use App\Models\Proyectos\Proyecto;
use App\Models\User;
use App\Services\Finanzas\CierreService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CierreServiceTest extends TestCase
{
    use RefreshDatabase;

    protected Cliente $cliente;
    protected Sublinea $sublinea;

    protected function setUp(): void
    {
        parent::setUp();

        $this->cliente = Cliente::create([
            'razon_social' => 'Test Cliente Cierre',
            'alias_3letras' => 'TCC',
            'sector' => 'Energía',
            'activo' => true,
        ]);

        $this->sublinea = Sublinea::create([
            'codigo' => 'CRR',
            'nombre' => 'Cierre Test Sublinea',
        ]);
    }

    public function test_get_cierre_data_returns_empty_when_no_cierre(): void
    {
        $service = new CierreService();
        $data = $service->getCierreData(now()->month, now()->year);

        $this->assertEquals('borrador', $data['workflow_status']);
        $this->assertEquals('—', $data['generado_por']);
        $this->assertEquals(0, $data['total_sat']);
        $this->assertEquals(0, $data['total_devengado']);
        $this->assertEquals(0, $data['total_pipeline']);
        $this->assertCount(0, $data['audit_entries']);
    }

    public function test_generar_cierre_creates_cierre_record(): void
    {
        $user = User::factory()->create();

        $service = new CierreService();
        $cierre = $service->generarCierreGerencial(now()->month, now()->year, $user->id);

        $this->assertEquals('generado', $cierre->status);
        $this->assertEquals($user->id, $cierre->generado_por_id);
        $this->assertEquals(now()->month, $cierre->mes);
        $this->assertEquals(now()->year, $cierre->anio);
        $this->assertEquals('gerencial_avance', $cierre->tipo);
    }

    public function test_generar_cierre_creates_secciones(): void
    {
        $user = User::factory()->create();

        $service = new CierreService();
        $cierre = $service->generarCierreGerencial(now()->month, now()->year, $user->id);

        $this->assertCount(3, $cierre->secciones);
        $codigos = $cierre->secciones->pluck('codigo')->toArray();
        $this->assertContains('sat_base', $codigos);
        $this->assertContains('devengado', $codigos);
        $this->assertContains('pipeline_ponderado', $codigos);
    }

    public function test_generar_cierre_updates_existing(): void
    {
        $user = User::factory()->create();

        $service = new CierreService();
        $cierre1 = $service->generarCierreGerencial(now()->month, now()->year, $user->id);
        $cierre2 = $service->generarCierreGerencial(now()->month, now()->year, $user->id);

        $this->assertEquals($cierre1->id, $cierre2->id);
        $this->assertEquals(1, \App\Models\Finanzas\CierreMensual::where('mes', now()->month)->where('anio', now()->year)->count());
    }

    public function test_aprobar_cierre_changes_status(): void
    {
        $user = User::factory()->create();
        $approver = User::factory()->create();

        $service = new CierreService();
        $cierre = $service->generarCierreGerencial(now()->month, now()->year, $user->id);

        $aprobado = $service->aprobarCierre($cierre->id, $approver->id);

        $this->assertEquals('aprobado', $aprobado->status);
        $this->assertEquals($approver->id, $aprobado->aprobado_por_id);
    }

    public function test_pipeline_ponderado_calculates_from_cotizando_projects(): void
    {
        Proyecto::create([
            'tech_reference' => 'TR-PP-001',
            'cp_numero' => 'CP-26-PP1',
            'anio' => now()->year,
            'cliente_id' => $this->cliente->id,
            'sublinea_id' => $this->sublinea->id,
            'estado' => 'cotizando',
        ]);

        $service = new CierreService();
        $data = $service->getCierreData(now()->month, now()->year);

        $this->assertIsArray($data['pipeline_ponderado']);
    }

    public function test_get_cierre_data_after_generation_shows_generado(): void
    {
        $user = User::factory()->create();

        $service = new CierreService();
        $service->generarCierreGerencial(now()->month, now()->year, $user->id);

        $data = $service->getCierreData(now()->month, now()->year);

        $this->assertEquals('generado', $data['workflow_status']);
        $this->assertEquals($user->name, $data['generado_por']);
        $this->assertNotEmpty($data['fecha_generacion']);
        $this->assertCount(1, $data['audit_entries']);
    }

    public function test_get_cierre_data_after_approval_shows_aprobado(): void
    {
        $user = User::factory()->create();
        $approver = User::factory()->create();

        $service = new CierreService();
        $cierre = $service->generarCierreGerencial(now()->month, now()->year, $user->id);
        $service->aprobarCierre($cierre->id, $approver->id);

        $data = $service->getCierreData(now()->month, now()->year);

        $this->assertEquals('aprobado', $data['workflow_status']);
        $this->assertEquals($approver->name, $data['aprobado_dg']);
        $this->assertCount(2, $data['audit_entries']);
    }
}