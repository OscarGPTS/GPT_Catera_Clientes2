<?php

namespace Tests\Feature\Auth;

use App\Models\AuthProvider;
use App\Models\EmailAllowlist;
use App\Models\RhRoleMapping;
use App\Models\SocioAllowlist;
use App\Models\User;
use App\Services\Auth\AuthOrchestrator;
use App\Settings\SystemSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AuthOrchestratorFullTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Notification::fake();
        $this->seedBasics();
    }

    private function seedBasics(): void
    {
        $permissions = [
            'ver dashboard', 'ver oportunidades', 'crear oportunidad', 'ver proyectos',
            'ver chat', 'enviar mensaje', 'ver admin usuarios', 'gestionar usuarios',
            'gestionar socios', 'gestionar rh mapping', 'ver roles permisos', 'gestionar permisos',
        ];

        foreach ($permissions as $perm) {
            Permission::create(['name' => $perm, 'guard_name' => 'web']);
        }

        $roles = ['ingeniero_proyectos', 'gerente_proyectos', 'socio', 'super_admin', 'director_dn', 'cfo', 'comercial'];
        foreach ($roles as $roleName) {
            Role::create(['name' => $roleName, 'guard_name' => 'web']);
        }

        RhRoleMapping::create(['puesto_rh' => '%Gerente de Proyectos%', 'rol_sistema' => 'gerente_proyectos', 'prioridad' => 80, 'activo' => true]);
        RhRoleMapping::create(['puesto_rh' => '%Director General%', 'rol_sistema' => 'super_admin', 'prioridad' => 100, 'activo' => true]);

        SystemSettings::fake([
            'minuta_entrega_obligatoria' => false,
            'bloqueo_cierre_dossier_incompleto' => true,
            'bloqueo_cierre_post_mortem_pendiente' => false,
            'auth_dominios_corporativos' => ['gptservices.com', 'satechenergy.com'],
            'concentracion_cliente_alerta_umbral' => 50,
        ]);
    }

    private function createUser(array $overrides = []): User
    {
        return User::create(array_merge([
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'password' => Hash::make('password'),
            'status' => 'active',
        ], $overrides));
    }

    public function test_email_password_login_succeeds(): void
    {
        $user = $this->createUser(['email' => 'test@gptservices.com']);
        AuthProvider::create([
            'user_id' => $user->id,
            'provider' => 'email_password',
            'password_hash' => Hash::make('password123'),
            'is_primary' => true,
        ]);

        $response = $this->post('/login', [
            'email' => 'test@gptservices.com',
            'password' => 'password123',
        ]);

        $this->assertAuthenticatedAs($user);
    }

    public function test_email_password_login_rejects_unknown_user(): void
    {
        $response = $this->post('/login', [
            'email' => 'noexiste@gptservices.com',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_email_password_login_rejects_wrong_password(): void
    {
        $user = $this->createUser(['email' => 'wrongpass@gptservices.com']);
        AuthProvider::create([
            'user_id' => $user->id,
            'provider' => 'email_password',
            'password_hash' => Hash::make('correct_password'),
            'is_primary' => true,
        ]);

        $response = $this->post('/login', [
            'email' => 'wrongpass@gptservices.com',
            'password' => 'wrong_password',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_suspended_user_cannot_login(): void
    {
        $user = $this->createUser(['email' => 'suspended@gptservices.com', 'status' => 'suspended']);
        AuthProvider::create([
            'user_id' => $user->id,
            'provider' => 'email_password',
            'password_hash' => Hash::make('password123'),
            'is_primary' => true,
        ]);

        $response = $this->post('/login', [
            'email' => 'suspended@gptservices.com',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_orchestrator_provisions_corporate_user_via_rh(): void
    {
        $orchestrator = app(AuthOrchestrator::class);

        $user = $orchestrator->loginOrProvision(
            'auth0',
            'auth0-12345',
            'fernando.basave@gptservices.com',
            ['name' => 'Fernando Basave Arce'],
        );

        $this->assertDatabaseHas('users', [
            'email' => 'fernando.basave@gptservices.com',
            'name' => 'Fernando Basave Arce',
        ]);

        $this->assertDatabaseHas('auth_providers', [
            'user_id' => $user->id,
            'provider' => 'auth0',
            'provider_user_id' => 'auth0-12345',
        ]);

        $this->assertNotEmpty($user->roles);
    }

    public function test_orchestrator_rejects_non_corporate_non_allowlisted_email(): void
    {
        $this->expectException(\Illuminate\Auth\Access\AuthorizationException::class);

        $orchestrator = app(AuthOrchestrator::class);
        $orchestrator->loginOrProvision(
            'google',
            'google-12345',
            'random@gmail.com',
            ['name' => 'Random Person'],
        );
    }

    public function test_orchestrator_provisions_allowlisted_user(): void
    {
        EmailAllowlist::create([
            'email' => 'contractor@gmail.com',
            'allowed_providers' => ['google'],
            'role_default' => 'comercial',
        ]);

        $orchestrator = app(AuthOrchestrator::class);
        $user = $orchestrator->loginOrProvision(
            'google',
            'google-67890',
            'contractor@gmail.com',
            ['name' => 'Contractor Person'],
        );

        $this->assertEquals('Contractor Person', $user->name);
        $this->assertTrue($user->hasRole('comercial'));
    }

    public function test_orchestrator_rejects_allowlisted_email_from_wrong_provider(): void
    {
        EmailAllowlist::create([
            'email' => 'contractor2@gmail.com',
            'allowed_providers' => ['google'],
            'role_default' => 'comercial',
        ]);

        $this->expectException(\Illuminate\Auth\Access\AuthorizationException::class);

        $orchestrator = app(AuthOrchestrator::class);
        $orchestrator->loginOrProvision(
            'microsoft',
            'ms-12345',
            'contractor2@gmail.com',
            ['name' => 'Contractor2'],
        );
    }

    public function test_orchestrator_links_new_provider_to_existing_user(): void
    {
        $user = $this->createUser(['email' => 'existing@gptservices.com']);
        $user->assignRole('ingeniero_proyectos');

        AuthProvider::create([
            'user_id' => $user->id,
            'provider' => 'email_password',
            'password_hash' => Hash::make('password'),
            'is_primary' => true,
        ]);

        $orchestrator = app(AuthOrchestrator::class);
        $result = $orchestrator->loginOrProvision(
            'google',
            'google-new-id',
            'existing@gptservices.com',
            ['name' => 'Existing User'],
        );

        $this->assertEquals($user->id, $result->id);
        $this->assertEquals(2, $result->authProviders()->count());
    }

    public function test_orchestrator_returns_existing_provider_user(): void
    {
        $user = $this->createUser(['email' => 'auth0user@gptservices.com']);

        AuthProvider::create([
            'user_id' => $user->id,
            'provider' => 'auth0',
            'provider_user_id' => 'auth0-existing-id',
            'is_primary' => true,
        ]);

        $orchestrator = app(AuthOrchestrator::class);
        $result = $orchestrator->loginOrProvision(
            'auth0',
            'auth0-existing-id',
            'auth0user@gptservices.com',
        );

        $this->assertEquals($user->id, $result->id);
    }

    public function test_invite_external_user_creates_user_with_invited_status(): void
    {
        $orchestrator = app(AuthOrchestrator::class);
        $user = $orchestrator->inviteExternalUser(
            'external@example.com',
            'comercial',
            'Ventas',
            'External Contractor',
        );

        $this->assertEquals('External Contractor', $user->name);
        $this->assertEquals('external@example.com', $user->email);
        $this->assertEquals('invited', $user->status);
        $this->assertTrue($user->hasRole('comercial'));

        $this->assertDatabaseHas('auth_providers', [
            'user_id' => $user->id,
            'provider' => 'email_password',
            'is_primary' => true,
        ]);
    }

    public function test_invite_existing_email_throws_exception(): void
    {
        $this->createUser(['email' => 'already@example.com']);

        $this->expectException(\Exception::class);

        $orchestrator = app(AuthOrchestrator::class);
        $orchestrator->inviteExternalUser('already@example.com', 'comercial');
    }

    public function test_link_provider_to_user(): void
    {
        $user = $this->createUser(['email' => 'link@gptservices.com']);

        $orchestrator = app(AuthOrchestrator::class);
        $provider = $orchestrator->linkProvider($user, 'google', 'google-123');

        $this->assertEquals('google', $provider->provider);
        $this->assertEquals('google-123', $provider->provider_user_id);
        $this->assertTrue($provider->is_primary);
    }

    public function test_unlink_provider_removes_non_primary(): void
    {
        $user = $this->createUser(['email' => 'unlink@gptservices.com']);

        AuthProvider::create([
            'user_id' => $user->id,
            'provider' => 'email_password',
            'password_hash' => Hash::make('password'),
            'is_primary' => true,
        ]);

        AuthProvider::create([
            'user_id' => $user->id,
            'provider' => 'google',
            'provider_user_id' => 'google-123',
            'is_primary' => false,
        ]);

        $orchestrator = app(AuthOrchestrator::class);
        $result = $orchestrator->unlinkProvider($user, 'google');

        $this->assertTrue($result);
        $this->assertEquals(1, $user->authProviders()->count());
    }

    public function test_cannot_unlink_last_provider(): void
    {
        $user = $this->createUser(['email' => 'onlyone@gptservices.com']);

        AuthProvider::create([
            'user_id' => $user->id,
            'provider' => 'email_password',
            'password_hash' => Hash::make('password'),
            'is_primary' => true,
        ]);

        $this->expectException(\Exception::class);

        $orchestrator = app(AuthOrchestrator::class);
        $orchestrator->unlinkProvider($user, 'email_password');
    }
}