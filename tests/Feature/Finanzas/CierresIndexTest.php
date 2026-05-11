<?php

namespace Tests\Feature\Finanzas;

use App\Models\Finanzas\CierreMensual;
use App\Models\User;
use App\Services\Finanzas\CierreService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CierresIndexTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RolesPermissionsSeeder::class);
    }

    public function test_cierre_service_generar_creates_record(): void
    {
        $user = User::factory()->create(['status' => 'active']);

        $service = new CierreService();
        $cierre = $service->generarCierreGerencial(now()->month, now()->year, $user->id);

        $this->assertEquals('generado', $cierre->status);
        $this->assertEquals($user->id, $cierre->generado_por_id);
    }

    public function test_cierre_service_aprobar_changes_status(): void
    {
        $user = User::factory()->create(['status' => 'active']);
        $approver = User::factory()->create(['status' => 'active']);

        $service = new CierreService();
        $cierre = $service->generarCierreGerencial(now()->month, now()->year, $user->id);
        $aprobado = $service->aprobarCierre($cierre->id, $approver->id);

        $this->assertEquals('aprobado', $aprobado->status);
        $this->assertEquals($approver->id, $aprobado->aprobado_por_id);
    }

    public function test_cierre_service_get_data_default_borrador(): void
    {
        $service = new CierreService();
        $data = $service->getCierreData(now()->month, now()->year);

        $this->assertEquals('borrador', $data['workflow_status']);
        $this->assertEquals('—', $data['generado_por']);
    }

    public function test_cierre_service_get_data_after_generation(): void
    {
        $user = User::factory()->create(['status' => 'active']);

        $service = new CierreService();
        $service->generarCierreGerencial(now()->month, now()->year, $user->id);

        $data = $service->getCierreData(now()->month, now()->year);

        $this->assertEquals('generado', $data['workflow_status']);
        $this->assertEquals($user->name, $data['generado_por']);
        $this->assertCount(1, $data['audit_entries']);
    }

    public function test_cierre_service_get_data_after_approval(): void
    {
        $user = User::factory()->create(['status' => 'active']);
        $approver = User::factory()->create(['status' => 'active']);

        $service = new CierreService();
        $cierre = $service->generarCierreGerencial(now()->month, now()->year, $user->id);
        $service->aprobarCierre($cierre->id, $approver->id);

        $data = $service->getCierreData(now()->month, now()->year);

        $this->assertEquals('aprobado', $data['workflow_status']);
        $this->assertEquals($approver->name, $data['aprobado_dg']);
        $this->assertCount(2, $data['audit_entries']);
    }

    public function test_cierre_service_generar_is_idempotent(): void
    {
        $user = User::factory()->create(['status' => 'active']);

        $service = new CierreService();
        $cierre1 = $service->generarCierreGerencial(now()->month, now()->year, $user->id);
        $cierre2 = $service->generarCierreGerencial(now()->month, now()->year, $user->id);

        $this->assertEquals($cierre1->id, $cierre2->id);
        $this->assertEquals(1, CierreMensual::where('mes', now()->month)->where('anio', now()->year)->count());
    }
}