<?php

namespace Tests\Feature\Comercial;

use App\Models\User;
use App\Models\Comercial\Cliente;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ClienteControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $superAdmin;
    private User $gerenteProyectos;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\RolesPermissionsSeeder::class);

        $this->superAdmin = User::factory()->superAdmin()->create();
        $this->gerenteProyectos = User::factory()->gerenteProyectos()->create();
    }

    public function test_index_requires_authentication(): void
    {
        $response = $this->get(route('clientes.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_index_accessible_by_authorized_users(): void
    {
        $response = $this->actingAs($this->superAdmin)->get(route('clientes.index'));
        $response->assertSuccessful();
    }

    public function test_index_accessible_by_gerente_proyectos(): void
    {
        $response = $this->actingAs($this->gerenteProyectos)->get(route('clientes.index'));
        $response->assertSuccessful();
    }

    public function test_show_displays_cliente(): void
    {
        $cliente = $this->makeCliente();

        $response = $this->actingAs($this->superAdmin)->get(route('clientes.show', $cliente));
        $response->assertSuccessful();
    }

    public function test_store_creates_cliente_via_livewire(): void
    {
        $this->actingAs($this->superAdmin);

        \Livewire\Livewire::test(\App\Livewire\Comercial\ClientesIndex::class)
            ->set('razon_social', 'Nueva Empresa SA')
            ->set('alias_3letras', 'NES')
            ->set('rfc', 'NES260101ABC')
            ->set('nuevoSector', 'Industrial')
            ->set('nuevoSegmento', 'A')
            ->call('store')
            ->assertSuccessful();

        $this->assertDatabaseHas('clientes', [
            'razon_social' => 'Nueva Empresa SA',
            'alias_3letras' => 'NES',
        ]);
    }

    public function test_store_validates_required_fields(): void
    {
        $this->actingAs($this->superAdmin);

        \Livewire\Livewire::test(\App\Livewire\Comercial\ClientesIndex::class)
            ->set('razon_social', '')
            ->set('alias_3letras', '')
            ->call('store')
            ->assertHasErrors(['razon_social', 'alias_3letras']);
    }

    public function test_add_contacto_via_livewire(): void
    {
        $cliente = $this->makeCliente();
        $this->actingAs($this->superAdmin);

        \Livewire\Livewire::test(\App\Livewire\Comercial\ClienteDetalle::class, ['cliente' => $cliente])
            ->set('contacto_nombre', 'Juan Perez')
            ->set('contacto_puesto', 'Director')
            ->set('contacto_email', 'juan@test.com')
            ->set('contacto_telefono', '555-1234')
            ->set('contacto_principal', true)
            ->call('addContacto')
            ->assertSuccessful();

        $this->assertDatabaseHas('contactos_cliente', [
            'cliente_id' => $cliente->id,
            'nombre' => 'Juan Perez',
            'email' => 'juan@test.com',
            'principal' => true,
        ]);
    }

    public function test_update_cliente_via_livewire(): void
    {
        $cliente = $this->makeCliente();
        $this->actingAs($this->superAdmin);

        \Livewire\Livewire::test(\App\Livewire\Comercial\ClienteDetalle::class, ['cliente' => $cliente])
            ->set('edit_razon_social', 'Cliente Modificado')
            ->set('edit_alias_3letras', 'CMO')
            ->call('updateCliente')
            ->assertSuccessful();

        $this->assertDatabaseHas('clientes', [
            'id' => $cliente->id,
            'razon_social' => 'Cliente Modificado',
            'alias_3letras' => 'CMO',
        ]);
    }

    private function makeCliente(array $overrides = []): Cliente
    {
        return Cliente::create(array_merge([
            'razon_social' => 'Test Cliente SA',
            'alias_3letras' => 'TCS',
            'sector' => 'Gobierno',
            'activo' => true,
        ], $overrides));
    }
}
