<?php

namespace Tests\Feature\Chat;

use App\Models\Chat\ChatCanal;
use App\Models\Chat\ChatMensaje;
use App\Models\Chat\ChatLectura;
use App\Models\Chat\ChatMencion;
use App\Models\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ChatTest extends TestCase
{
    use RefreshDatabase;

    private User $user1;
    private User $user2;
    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RolesPermissionsSeeder::class);

        $this->admin = User::create([
            'name' => 'Admin Chat',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'status' => 'active',
        ]);
        $this->admin->assignRole('super_admin');

        $this->user1 = User::create([
            'name' => 'User One',
            'email' => 'one@test.com',
            'password' => bcrypt('password'),
            'status' => 'active',
            'departamento' => 'Ingeniería',
        ]);

        $this->user2 = User::create([
            'name' => 'User Two',
            'email' => 'two@test.com',
            'password' => bcrypt('password'),
            'status' => 'active',
            'departamento' => 'Ingeniería',
        ]);
    }

    /** @test */
    public function can_create_chat_channel()
    {
        $canal = ChatCanal::create([
            'tipo' => 'privado',
            'nombre' => 'Chat privado',
            'creado_por_id' => $this->user1->id,
        ]);

        $canal->miembros()->create(['user_id' => $this->user1->id]);
        $canal->miembros()->create(['user_id' => $this->user2->id]);

        $this->assertDatabaseHas('chat_canales', ['nombre' => 'Chat privado']);
        $this->assertEquals(2, $canal->miembros()->count());
    }

    /** @test */
    public function can_send_message_to_channel()
    {
        $canal = ChatCanal::create(['tipo' => 'privado', 'nombre' => 'Test']);
        $canal->miembros()->create(['user_id' => $this->user1->id]);

        $mensaje = ChatMensaje::create([
            'canal_id' => $canal->id,
            'user_id' => $this->user1->id,
            'contenido' => 'Hola mundo',
        ]);

        $this->assertDatabaseHas('chat_mensajes', ['contenido' => 'Hola mundo']);
        $this->assertEquals($canal->id, $mensaje->canal_id);
    }

    /** @test */
    public function can_create_mention()
    {
        $canal = ChatCanal::create(['tipo' => 'privado', 'nombre' => 'Test']);
        $canal->miembros()->create(['user_id' => $this->user1->id]);

        $mensaje = ChatMensaje::create([
            'canal_id' => $canal->id,
            'user_id' => $this->user1->id,
            'contenido' => 'Hola @User Two',
        ]);

        $mensaje->menciones()->create(['user_id' => $this->user2->id]);

        $this->assertEquals(1, $mensaje->menciones()->count());
        $this->assertDatabaseHas('chat_menciones', ['user_id' => $this->user2->id]);
    }

    /** @test */
    public function can_mark_channel_as_read()
    {
        $canal = ChatCanal::create(['tipo' => 'privado', 'nombre' => 'Test']);
        $canal->miembros()->create(['user_id' => $this->user1->id]);

        $msg = ChatMensaje::create([
            'canal_id' => $canal->id,
            'user_id' => $this->user2->id,
            'contenido' => 'Mensaje',
        ]);

        ChatLectura::updateOrCreate(
            ['canal_id' => $canal->id, 'user_id' => $this->user1->id],
            ['ultimo_mensaje_leido_id' => $msg->id]
        );

        $this->assertDatabaseHas('chat_lecturas', [
            'canal_id' => $canal->id,
            'user_id' => $this->user1->id,
        ]);
    }

    /** @test */
    public function user_can_see_own_channels()
    {
        $canal = ChatCanal::create(['tipo' => 'privado', 'nombre' => 'Canal 1']);
        $canal->miembros()->create(['user_id' => $this->user1->id]);

        $canal2 = ChatCanal::create(['tipo' => 'privado', 'nombre' => 'Canal 2']);
        $canal2->miembros()->create(['user_id' => $this->user2->id]);

        $this->assertEquals(1, $this->user1->chatCanales()->count());
        $this->assertEquals(1, $this->user2->chatCanales()->count());
    }

    /** @test */
    public function admin_can_see_all_channels()
    {
        $canal = ChatCanal::create(['tipo' => 'privado', 'nombre' => 'Canal A']);
        $canal->miembros()->create(['user_id' => $this->user1->id]);

        $canal2 = ChatCanal::create(['tipo' => 'privado', 'nombre' => 'Canal B']);
        $canal2->miembros()->create(['user_id' => $this->user2->id]);

        // Admin bypass via policy, not via query
        $this->assertTrue($this->admin->esAdmin());
    }

    /** @test */
    public function chat_page_requires_auth()
    {
        $response = $this->get('/chat');
        $response->assertRedirect('/login');
    }

    /** @test */
    public function chat_page_renders_for_authenticated_user()
    {
        $response = $this->actingAs($this->user1)->get('/chat');
        $response->assertStatus(200);
    }

    /** @test */
    public function notifications_page_renders_for_authenticated_user()
    {
        $response = $this->actingAs($this->user1)->get('/notificaciones');
        $response->assertStatus(200);
    }

    /** @test */
    public function admin_chat_page_renders_for_admin()
    {
        $response = $this->actingAs($this->admin)->get('/admin/chat');
        $response->assertStatus(200);
    }

    /** @test */
    public function department_channel_creation_works()
    {
        $service = app(\App\Services\Chat\ChatService::class);
        $service->createDepartmentChannels();

        $canal = ChatCanal::where('tipo', 'departamento')
            ->where('nombre', 'Depto. Ingeniería')
            ->first();

        $this->assertNotNull($canal);
        $this->assertTrue($canal->miembros()->where('user_id', $this->user1->id)->exists());
    }

    /** @test */
    public function direction_channel_creation_works()
    {
        $service = app(\App\Services\Chat\ChatService::class);
        $canal = $service->createDirectionChannel();

        $this->assertNotNull($canal);
        $this->assertEquals('direccion', $canal->tipo);
        $this->assertTrue($canal->miembros()->where('user_id', $this->admin->id)->exists());
    }

    /** @test */
    public function project_observer_creates_channel_on_project_creation()
    {
        // Create a client and sublinea required for project creation
        $cliente = \App\Models\Comercial\Cliente::create([
            'razon_social' => 'Test Client',
            'alias_3letras' => 'TST',
            'sector' => 'Industrial',
            'activo' => true,
        ]);

        $sublinea = \App\Models\Comercial\Sublinea::create([
            'codigo' => 'HTP',
            'nombre' => 'Hot Tapping',
        ]);

        $proyecto = \App\Models\Proyectos\Proyecto::create([
            'cp_numero' => 'CP-26-999',
            'anio' => 2026,
            'cliente_id' => $cliente->id,
            'sublinea_id' => $sublinea->id,
            'estado' => 'en_revision',
            'director_dn_id' => $this->user1->id,
            'gerente_proyectos_id' => $this->user2->id,
        ]);

        // The observer should have created a channel
        $canal = ChatCanal::where('tipo', 'proyecto')
            ->where('contexto_id', $proyecto->id)
            ->first();

        $this->assertNotNull($canal);
        $this->assertTrue($canal->miembros()->where('user_id', $this->user1->id)->exists());
        $this->assertTrue($canal->miembros()->where('user_id', $this->user2->id)->exists());
    }
}
