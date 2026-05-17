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

    private function createChannelWithMembers(string $tipo = 'privado', string $nombre = 'Test Channel', array $memberIds = []): ChatCanal
    {
        $canal = ChatCanal::create([
            'tipo' => $tipo,
            'nombre' => $nombre,
            'creado_por_id' => $memberIds[0] ?? $this->user1->id,
        ]);
        foreach ($memberIds as $uid) {
            $canal->miembros()->create(['user_id' => $uid]);
        }
        return $canal;
    }

    // ---- Channel CRUD ----

    /** @test */
    public function can_create_chat_channel()
    {
        $canal = $this->createChannelWithMembers('privado', 'Chat privado', [$this->user1->id, $this->user2->id]);

        $this->assertDatabaseHas('chat_canales', ['nombre' => 'Chat privado']);
        $this->assertEquals(2, $canal->miembros()->count());
    }

    /** @test */
    public function can_send_message_to_channel()
    {
        $canal = $this->createChannelWithMembers('privado', 'Test', [$this->user1->id]);

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
        $canal = $this->createChannelWithMembers('privado', 'Test', [$this->user1->id]);

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
        $canal = $this->createChannelWithMembers('privado', 'Test', [$this->user1->id]);

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
        $canal = $this->createChannelWithMembers('privado', 'Canal 1', [$this->user1->id]);
        $canal2 = $this->createChannelWithMembers('privado', 'Canal 2', [$this->user2->id]);

        $this->assertEquals(1, $this->user1->chatCanales()->count());
        $this->assertEquals(1, $this->user2->chatCanales()->count());
    }

    /** @test */
    public function admin_can_see_all_channels()
    {
        $this->createChannelWithMembers('privado', 'Canal A', [$this->user1->id]);
        $this->createChannelWithMembers('privado', 'Canal B', [$this->user2->id]);

        $this->assertTrue($this->admin->esAdmin());
    }

    // ---- Page Access ----

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
    public function chat_page_with_canal_query_param()
    {
        $canal = $this->createChannelWithMembers('privado', 'Test', [$this->user1->id]);
        $response = $this->actingAs($this->user1)->get('/chat?canal=' . $canal->id);
        $response->assertStatus(200);
    }

    /** @test */
    public function admin_chat_page_renders_for_admin()
    {
        $response = $this->actingAs($this->admin)->get('/admin/chat');
        $response->assertStatus(200);
    }

    /** @test */
    public function notifications_page_renders_for_authenticated_user()
    {
        $response = $this->actingAs($this->user1)->get('/notificaciones');
        $response->assertStatus(200);
    }

    // ---- ChatCanalPolicy ----

    /** @test */
    public function member_can_view_channel()
    {
        $canal = $this->createChannelWithMembers('privado', 'Test', [$this->user1->id]);
        $this->assertTrue($this->user1->can('view', $canal));
    }

    /** @test */
    public function non_member_cannot_view_channel()
    {
        $canal = $this->createChannelWithMembers('privado', 'Test', [$this->user1->id]);
        $this->assertFalse($this->user2->can('view', $canal));
    }

    /** @test */
    public function admin_can_view_any_channel()
    {
        $canal = $this->createChannelWithMembers('privado', 'Test', [$this->user1->id]);
        $this->assertTrue($this->admin->can('view', $canal));
    }

    /** @test */
    public function creator_can_update_channel()
    {
        $canal = $this->createChannelWithMembers('privado', 'Test', [$this->user1->id]);
        $this->assertTrue($this->user1->can('update', $canal));
    }

    /** @test */
    public function non_creator_cannot_update_channel()
    {
        $canal = $this->createChannelWithMembers('privado', 'Test', [$this->user1->id]);
        $this->assertFalse($this->user2->can('update', $canal));
    }

    /** @test */
    public function admin_can_update_any_channel()
    {
        $canal = $this->createChannelWithMembers('privado', 'Test', [$this->user1->id]);
        $this->assertTrue($this->admin->can('update', $canal));
    }

    /** @test */
    public function only_admin_can_delete_channel()
    {
        $canal = $this->createChannelWithMembers('privado', 'Test', [$this->user1->id]);
        $this->assertFalse($this->user1->can('delete', $canal));
        $this->assertTrue($this->admin->can('delete', $canal));
    }

    // ---- ChatMensajePolicy ----

    /** @test */
    public function message_owner_can_update_message()
    {
        $canal = $this->createChannelWithMembers('privado', 'Test', [$this->user1->id]);
        $msg = ChatMensaje::create([
            'canal_id' => $canal->id,
            'user_id' => $this->user1->id,
            'contenido' => 'Original',
        ]);

        $this->assertTrue($this->user1->can('update', $msg));
    }

    /** @test */
    public function non_owner_cannot_update_message()
    {
        $canal = $this->createChannelWithMembers('privado', 'Test', [$this->user1->id]);
        $msg = ChatMensaje::create([
            'canal_id' => $canal->id,
            'user_id' => $this->user1->id,
            'contenido' => 'Original',
        ]);

        $this->assertFalse($this->user2->can('update', $msg));
    }

    /** @test */
    public function admin_can_update_any_message()
    {
        $canal = $this->createChannelWithMembers('privado', 'Test', [$this->user1->id]);
        $msg = ChatMensaje::create([
            'canal_id' => $canal->id,
            'user_id' => $this->user1->id,
            'contenido' => 'Original',
        ]);

        $this->assertTrue($this->admin->can('update', $msg));
    }

    /** @test */
    public function message_owner_can_delete_message()
    {
        $canal = $this->createChannelWithMembers('privado', 'Test', [$this->user1->id]);
        $msg = ChatMensaje::create([
            'canal_id' => $canal->id,
            'user_id' => $this->user1->id,
            'contenido' => 'Original',
        ]);

        $this->assertTrue($this->user1->can('delete', $msg));
    }

    /** @test */
    public function admin_can_delete_any_message()
    {
        $canal = $this->createChannelWithMembers('privado', 'Test', [$this->user1->id]);
        $msg = ChatMensaje::create([
            'canal_id' => $canal->id,
            'user_id' => $this->user1->id,
            'contenido' => 'Original',
        ]);

        $this->assertTrue($this->admin->can('delete', $msg));
    }

    // ---- ChatService ----

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
    public function private_channel_creation_prevents_duplicates()
    {
        $service = app(\App\Services\Chat\ChatService::class);
        $canal1 = $service->createPrivateChannel($this->user1->id, $this->user2->id);
        $canal2 = $service->createPrivateChannel($this->user1->id, $this->user2->id);

        $this->assertEquals($canal1->id, $canal2->id);
    }

    /** @test */
    public function private_channel_creation_reverse_order_prevents_duplicates()
    {
        $service = app(\App\Services\Chat\ChatService::class);
        $canal1 = $service->createPrivateChannel($this->user1->id, $this->user2->id);
        $canal2 = $service->createPrivateChannel($this->user2->id, $this->user1->id);

        $this->assertEquals($canal1->id, $canal2->id);
    }

    // ---- Soft-delete (content replacement) ----

    /** @test */
    public function soft_delete_replaces_message_content()
    {
        $canal = $this->createChannelWithMembers('privado', 'Test', [$this->user1->id]);
        $msg = ChatMensaje::create([
            'canal_id' => $canal->id,
            'user_id' => $this->user1->id,
            'contenido' => 'Original message',
        ]);

        $msg->update(['contenido' => 'Este mensaje fue eliminado', 'edited_at' => null]);

        $this->assertEquals('Este mensaje fue eliminado', $msg->fresh()->contenido);
        $this->assertNull($msg->fresh()->edited_at);
    }

    // ---- Read receipts ----

    /** @test */
    public function unread_count_is_correct()
    {
        $canal = $this->createChannelWithMembers('departamento', 'Test', [$this->user1->id, $this->user2->id]);

        $msg1 = ChatMensaje::create(['canal_id' => $canal->id, 'user_id' => $this->user2->id, 'contenido' => 'Msg 1']);
        $msg2 = ChatMensaje::create(['canal_id' => $canal->id, 'user_id' => $this->user2->id, 'contenido' => 'Msg 2']);

        ChatLectura::updateOrCreate(
            ['canal_id' => $canal->id, 'user_id' => $this->user1->id],
            ['ultimo_mensaje_leido_id' => $msg1->id]
        );

        $ultimoLeidoId = ChatLectura::where('canal_id', $canal->id)->where('user_id', $this->user1->id)->value('ultimo_mensaje_leido_id');
        $unread = ChatMensaje::where('canal_id', $canal->id)->where('id', '>', $ultimoLeidoId)->count();

        $this->assertEquals(1, $unread);
    }

    // ---- Project observer ----

    /** @test */
    public function project_observer_creates_channel_on_project_creation()
    {
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

        $canal = ChatCanal::where('tipo', 'proyecto')
            ->where('contexto_id', $proyecto->id)
            ->first();

        $this->assertNotNull($canal);
        $this->assertTrue($canal->miembros()->where('user_id', $this->user1->id)->exists());
        $this->assertTrue($canal->miembros()->where('user_id', $this->user2->id)->exists());
    }
}