<?php

namespace Tests\Feature\Proyectos;

use App\Models\User;
use App\Models\Proyectos\Proyecto;
use App\Models\Comercial\Cliente;
use App\Models\Comercial\Sublinea;
use App\Services\Proyectos\SecuenciasService;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SecuenciasServiceTest extends TestCase
{
    use RefreshDatabase;

    private SecuenciasService $service;
    private Proyecto $proyecto;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new SecuenciasService();

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
            'tech_reference' => 'REF-SEQ-001',
            'anio' => 2026,
            'cliente_id' => $cliente->id,
            'sublinea_id' => $sublinea->id,
            'estado' => 'cotizando',
        ]);
    }

    public function test_asignar_cp_generates_correct_format(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $cpNumero = $this->service->asignarCp($this->proyecto);

        $this->assertEquals('CP-26-001', $cpNumero);
        $this->proyecto->refresh();
        $this->assertEquals('CP-26-001', $this->proyecto->cp_numero);
    }

    public function test_asignar_cp_increments_consecutively(): void
    {
        $user = User::factory()->create();

        $cliente2 = Cliente::create([
            'razon_social' => 'Otro Cliente',
            'alias_3letras' => 'OTR',
            'activo' => true,
        ]);

        $proyecto2 = Proyecto::create([
            'tech_reference' => 'REF-SEQ-002',
            'anio' => 2026,
            'cliente_id' => $cliente2->id,
            'sublinea_id' => $this->proyecto->sublinea_id,
            'estado' => 'cotizando',
        ]);

        $this->actingAs($user);
        $cp1 = $this->service->asignarCp($this->proyecto);
        $cp2 = $this->service->asignarCp($proyecto2);

        $this->assertEquals('CP-26-001', $cp1);
        $this->assertEquals('CP-26-002', $cp2);
    }

    public function test_asignar_dn_generates_correct_format(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $dnNumero = $this->service->asignarDn($this->proyecto);

        $this->assertEquals('DN-26-001', $dnNumero);
        $this->proyecto->refresh();
        $this->assertEquals('DN-26-001', $this->proyecto->dn_numero);
    }

    public function test_asignar_cp_creates_event(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->service->asignarCp($this->proyecto);

        $this->assertEquals(1, $this->proyecto->eventos()->count());
        $evento = $this->proyecto->eventos()->first();
        $this->assertEquals('cp_asignado', $evento->tipo);
        $this->assertEquals($user->id, $evento->user_id);
    }

    public function test_asignar_dn_creates_event(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->service->asignarDn($this->proyecto);

        $this->assertEquals(1, $this->proyecto->eventos()->count());
        $evento = $this->proyecto->eventos()->first();
        $this->assertEquals('dn_asignado', $evento->tipo);
    }

    public function test_asignar_cp_different_years_independent(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->service->asignarCp($this->proyecto);

        $cliente25 = Cliente::create([
            'razon_social' => 'Cliente 2025',
            'alias_3letras' => 'C25',
            'activo' => true,
        ]);

        $proyecto2025 = Proyecto::create([
            'tech_reference' => 'REF-2025',
            'anio' => 2025,
            'cliente_id' => $cliente25->id,
            'sublinea_id' => $this->proyecto->sublinea_id,
            'estado' => 'cotizando',
        ]);

        $cp25 = $this->service->asignarCp($proyecto2025);
        $this->assertEquals('CP-25-001', $cp25);
    }
}