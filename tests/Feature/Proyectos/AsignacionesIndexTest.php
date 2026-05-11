<?php

namespace Tests\Feature\Proyectos;

use App\Models\Proyectos\AsignacionSnapshot;
use App\Models\User;
use App\Services\Asignaciones\SnapshotService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AsignacionesIndexTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RolesPermissionsSeeder::class);
    }

    public function test_snapshot_service_generates_data_for_active_users(): void
    {
        $user = User::factory()->create(['status' => 'active']);
        $user->assignRole('gerente_proyectos');

        $service = new SnapshotService();
        $service->generar(now()->month, now()->year);

        $this->assertDatabaseHas('asignaciones_personas', [
            'user_id' => $user->id,
            'mes' => now()->month,
            'anio' => now()->year,
        ]);
    }

    public function test_snapshot_service_gets_data_structure(): void
    {
        $user = User::factory()->create(['status' => 'active']);
        $user->assignRole('gerente_proyectos');

        AsignacionSnapshot::create([
            'user_id' => $user->id,
            'mes' => now()->month,
            'anio' => now()->year,
            'cp_asignados' => 3,
            'dn_activos' => 2,
            'gerencia_regional' => 'GPT-IM',
        ]);

        $service = new SnapshotService();
        $data = $service->getSnapshotData(now()->month, now()->year);

        $this->assertTrue($data['snapshot_generado']);
        $this->assertEquals(1, $data['equipo_total']);
        $this->assertArrayHasKey('asignaciones', $data);
    }

    public function test_snapshot_service_returns_empty_when_no_data(): void
    {
        $service = new SnapshotService();
        $data = $service->getSnapshotData(now()->month, now()->year);

        $this->assertFalse($data['snapshot_generado']);
        $this->assertEquals(0, $data['equipo_total']);
        $this->assertCount(0, $data['asignaciones']);
    }

    public function test_snapshot_service_filters_by_gerencia(): void
    {
        $user1 = User::factory()->create(['status' => 'active', 'departamento' => 'GRC']);
        $user1->assignRole('gerente_proyectos');

        AsignacionSnapshot::create([
            'user_id' => $user1->id,
            'mes' => now()->month,
            'anio' => now()->year,
            'cp_asignados' => 5,
            'dn_activos' => 3,
            'gerencia_regional' => 'GRC',
        ]);

        $service = new SnapshotService();
        $data = $service->getSnapshotData(now()->month, now()->year);

        $found = collect($data['asignaciones'])->firstWhere('gerencia', 'GRC');
        $this->assertNotNull($found);
        $this->assertEquals(5, $found['cp_asignados']);
    }
}