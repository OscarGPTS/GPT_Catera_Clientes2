<?php

namespace Tests\Feature\Auth;

use App\Models\AuthProvider;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthOrchestratorTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_with_email_password_can_login(): void
    {
        $this->withoutMiddleware();

        $user = User::factory()->create([
            'email' => 'ingeniero.test@gptservices.com',
            'name' => 'Test Ingeniero',
            'puesto' => 'Ingeniero de Proyectos',
            'status' => 'active',
        ]);

        AuthProvider::create([
            'user_id' => $user->id,
            'provider' => 'email_password',
            'password_hash' => bcrypt('password123'),
            'is_primary' => true,
        ]);

        $response = $this->post('/login', [
            'email' => 'ingeniero.test@gptservices.com',
            'password' => 'password123',
        ]);

        $this->assertAuthenticatedAs($user);
    }

    public function test_non_existent_user_cannot_login(): void
    {
        $this->withoutMiddleware();

        $response = $this->post('/login', [
            'email' => 'noexiste@gptservices.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_invalid_password_rejected(): void
    {
        $this->withoutMiddleware();

        $user = User::factory()->create([
            'email' => 'password.test@gptservices.com',
            'status' => 'active',
        ]);

        AuthProvider::create([
            'user_id' => $user->id,
            'provider' => 'email_password',
            'password_hash' => bcrypt('correct_password'),
            'is_primary' => true,
        ]);

        $response = $this->post('/login', [
            'email' => 'password.test@gptservices.com',
            'password' => 'wrong_password',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }
}
