<?php

namespace Tests\Feature\Comercial;

use App\Models\User;
use App\Models\Comercial\Cliente;
use App\Models\Comercial\ContactoCliente;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ClienteControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $superAdmin;
    private User $gerenteProyectos;
    private User $guestUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\RolesPermissionsSeeder::class);

        $this->superAdmin = User::factory()->create();
        $this->superAdmin->assignRole('super_admin');

        $this->gerenteProyectos = User::factory()->create();
        $this->gerenteProyectos->assignRole('gerente_proyectos');

        $this->guestUser = User::factory()->create();
    }

    public function test_index_requires_authentication(): void
    {
        $response = $this->get(route('clientes.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_index_accessible_by_super_admin(): void
    {
        $response = $this->actingAs($this->superAdmin)->get(route('clientes.index'));
        $response->assertOk();
        $response->assertViewIs('comercial.clientes');
    }

    public function test_index_accessible_by_gerente_proyectos(): void
    {
        $response = $this->actingAs($this->gerenteProyectos)->get(route('clientes.index'));
        $response->assertOk();
    }

    public function test_store_creates_cliente(): void
    {
        $response = $this->actingAs($this->superAdmin)->post(route('clientes.store'), [
            'razon_social' => 'Nueva Empresa SA',
            'alias_3letras' => 'NES',
            'rfc' => 'NES260101ABC',
            'sector' => 'Industrial',
            'segmento' => 'A',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('clientes', [
            'razon_social' => 'Nueva Empresa SA',
            'alias_3letras' => 'NES',
        ]);
    }

    public function test_store_validates_required_fields(): void
    {
        $response = $this->actingAs($this->superAdmin)->post(route('clientes.store'), []);

        $response->assertSessionHasErrors(['razon_social', 'alias_3letras']);
    }

    public function test_show_displays_cliente(): void
    {
        $cliente = Cliente::create([
            'razon_social' => 'Cliente Show Test',
            'alias_3letras' => 'CST',
            'activo' => true,
        ]);

        $response = $this->actingAs($this->superAdmin)->get(route('clientes.show', $cliente));
        $response->assertOk();
        $response->assertViewIs('comercial.cliente-detalle');
        $response->assertViewHas('cliente');
    }

    public function test_update_modifies_cliente(): void
    {
        $cliente = Cliente::create([
            'razon_social' => 'Cliente Original',
            'alias_3letras' => 'COR',
            'activo' => true,
        ]);

        $response = $this->actingAs($this->superAdmin)->put(route('clientes.update', $cliente), [
            'razon_social' => 'Cliente Modificado',
            'alias_3letras' => 'CMO',
            'activo' => true,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('clientes', [
            'id' => $cliente->id,
            'razon_social' => 'Cliente Modificado',
            'alias_3letras' => 'CMO',
        ]);
    }

    public function test_destroy_soft_deletes_cliente(): void
    {
        $cliente = Cliente::create([
            'razon_social' => 'Cliente a Desactivar',
            'alias_3letras' => 'CDA',
            'activo' => true,
        ]);

        $response = $this->actingAs($this->superAdmin)->delete(route('clientes.destroy', $cliente));
        $response->assertRedirect(route('clientes.index'));

        $cliente->refresh();
        $this->assertFalse($cliente->activo);
    }

    public function test_add_contacto_to_cliente(): void
    {
        $cliente = Cliente::create([
            'razon_social' => 'Cliente Contacto Test',
            'alias_3letras' => 'CCT',
            'activo' => true,
        ]);

        $response = $this->actingAs($this->superAdmin)->post(route('clientes.add-contacto', $cliente), [
            'nombre' => 'Juan Perez',
            'puesto' => 'Director',
            'email' => 'juan@test.com',
            'telefono' => '555-1234',
            'principal' => true,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('contactos_cliente', [
            'cliente_id' => $cliente->id,
            'nombre' => 'Juan Perez',
            'email' => 'juan@test.com',
            'principal' => true,
        ]);
    }
}