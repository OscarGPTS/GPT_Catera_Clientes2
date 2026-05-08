<?php

namespace Tests\Unit;

use App\Models\SocioAllowlist;
use App\Models\User;
use App\Services\Auth\SocioResolver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SocioResolverTest extends TestCase
{
    use RefreshDatabase;

    private SocioResolver $resolver;

    protected function setUp(): void
    {
        parent::setUp();
        $this->resolver = new SocioResolver();
        Role::create(['name' => 'socio', 'guard_name' => 'web']);
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

    public function test_socio_resolver_checks_override_true(): void
    {
        $user = $this->createUser([
            'email' => 'override@test.com',
            'es_socio_override' => true,
        ]);

        $this->assertTrue($this->resolver->isSocio($user));
    }

    public function test_socio_resolver_checks_override_false(): void
    {
        $user = $this->createUser([
            'email' => 'override2@test.com',
            'es_socio_override' => false,
            'es_socio' => true,
        ]);

        $this->assertFalse($this->resolver->isSocio($user));
    }

    public function test_socio_resolver_checks_es_socio_field(): void
    {
        $user = $this->createUser([
            'email' => 'sociofield@test.com',
            'es_socio' => true,
            'es_socio_override' => null,
        ]);

        $this->assertTrue($this->resolver->isSocio($user));
    }

    public function test_socio_resolver_checks_allowlist(): void
    {
        $user = $this->createUser([
            'email' => 'allowlist@test.com',
            'es_socio' => false,
            'es_socio_override' => null,
        ]);

        SocioAllowlist::create([
            'email' => 'allowlist@test.com',
            'notes' => 'Test allowlist',
        ]);

        $this->assertTrue($this->resolver->isSocio($user));
    }

    public function test_socio_resolver_returns_false_when_nothing_matches(): void
    {
        $user = $this->createUser([
            'email' => 'notasocio@test.com',
            'es_socio' => false,
            'es_socio_override' => null,
        ]);

        $this->assertFalse($this->resolver->isSocio($user));
    }

    public function test_socio_resolver_caches_result(): void
    {
        $user = $this->createUser([
            'email' => 'cache@test.com',
            'es_socio' => false,
            'es_socio_override' => null,
        ]);

        $this->assertFalse($this->resolver->isSocio($user));

        SocioAllowlist::create(['email' => 'cache@test.com']);

        Cache::forget("socio_resolver:cache@test.com");
        $this->assertTrue($this->resolver->isSocio($user));
    }

    public function test_refresh_socio_status_assigns_role(): void
    {
        $user = $this->createUser([
            'email' => 'refresh@test.com',
            'es_socio' => false,
            'es_socio_override' => true,
        ]);

        $result = $this->resolver->refreshSocioStatus($user);

        $this->assertTrue($result);
        $this->assertTrue($user->fresh()->es_socio);
        $this->assertTrue($user->fresh()->hasRole('socio'));
    }
}