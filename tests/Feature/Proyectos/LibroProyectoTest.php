<?php

namespace Tests\Feature\Proyectos;

use App\Models\User;
use App\Models\Proyectos\Proyecto;
use App\Models\Proyectos\LibroProyecto;
use App\Models\Proyectos\LibroSeccion;
use App\Models\Proyectos\LibroSeccionChecklist;
use App\Models\Comercial\Cliente;
use App\Models\Comercial\Sublinea;
use App\Services\Libro\AperturaLibroService;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class LibroProyectoTest extends TestCase
{
    use RefreshDatabase;

    private Proyecto $proyecto;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RolesPermissionsSeeder::class);

        $cliente = Cliente::create([
            'razon_social' => 'Cliente Libro SA',
            'alias_3letras' => 'CLB',
            'sector' => 'Industrial',
            'activo' => true,
        ]);

        $sublinea = Sublinea::create([
            'codigo' => 'HTP',
            'nombre' => 'Heat Transfer Products',
        ]);

        $this->user = User::create([
            'name' => 'Test User',
            'email' => 'test@libro.com',
            'password' => bcrypt('password'),
            'puesto' => 'Ingeniero',
        ]);

        $this->proyecto = Proyecto::create([
            'cp_numero' => 'CP-26-LIB',
            'anio' => 2026,
            'cliente_id' => $cliente->id,
            'sublinea_id' => $sublinea->id,
            'estado' => 'en_ejecucion',
        ]);
    }

    public function test_libro_page_renders_for_authorized_user()
    {
        $this->user->givePermissionTo('ver libro proyecto');
        $response = $this->actingAs($this->user)->get("/proyectos/{$this->proyecto->id}/libro");
        $response->assertStatus(200);
        $response->assertSee('Libro de Proyecto');
    }

    public function test_libro_page_requires_authentication()
    {
        $response = $this->get("/proyectos/{$this->proyecto->id}/libro");
        $response->assertRedirect(route('login'));
    }

    public function test_apertura_libro_creates_secciones()
    {
        $service = new AperturaLibroService();
        $service->abrir($this->proyecto);

        $libro = LibroProyecto::where('proyecto_id', $this->proyecto->id)->first();
        $this->assertNotNull($libro);
        $this->assertEquals(10, $libro->secciones()->count());

        $codigos = $libro->secciones()->pluck('codigo')->toArray();
        $this->assertEquals(['A','B','C','D','E','F','G','H','I','J'], $codigos);
    }

    public function test_libro_seccion_has_checklist()
    {
        $service = new AperturaLibroService();
        $service->abrir($this->proyecto);

        $libro = LibroProyecto::where('proyecto_id', $this->proyecto->id)->first();
        $seccionA = $libro->secciones()->where('codigo', 'A')->first();

        $seccionA->checklist()->create([
            'item_descripcion' => 'Programa general de obra (Gantt)',
            'completado' => true,
            'completado_por_id' => $this->user->id,
            'completado_at' => now(),
        ]);

        $seccionA->checklist()->create([
            'item_descripcion' => 'Ruta crítica definida',
            'completado' => false,
        ]);

        $this->assertEquals(2, $seccionA->checklist()->count());
        $this->assertEquals(1, $seccionA->checklist()->where('completado', true)->count());
    }

    public function test_avance_calculator_works()
    {
        $service = new AperturaLibroService();
        $service->abrir($this->proyecto);

        $libro = LibroProyecto::where('proyecto_id', $this->proyecto->id)->first();
        $seccionA = $libro->secciones()->where('codigo', 'A')->first();

        foreach (['Item 1', 'Item 2', 'Item 3'] as $desc) {
            $seccionA->checklist()->create([
                'item_descripcion' => $desc,
                'completado' => true,
            ]);
        }

        $calculator = new \App\Services\Libro\AvanceCalculator();
        $avance = $calculator->calcularAvanceSeccion($seccionA->id);
        $this->assertEquals(100, $avance);
    }

    public function test_proyecto_has_libro_relationship()
    {
        $service = new AperturaLibroService();
        $service->abrir($this->proyecto);

        $this->assertNotNull($this->proyecto->fresh()->libroProyecto);
    }
}