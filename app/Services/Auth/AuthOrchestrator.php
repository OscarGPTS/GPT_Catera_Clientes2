<?php

namespace App\Services\Auth;

use App\Models\AuthProvider;
use App\Models\EmailAllowlist;
use App\Models\User;
use App\Notifications\UserProvisionedNotification;
use App\Settings\SystemSettings;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AuthOrchestrator
{
    public function __construct(
        private RoleMapper $roleMapper,
        private SocioResolver $socioResolver,
    ) {}

    public function loginOrProvision(
        string $provider,
        ?string $providerUserId,
        string $email,
        array $profileData = [],
    ): User {
        if ($providerUserId) {
            $existingProvider = AuthProvider::where('provider', $provider)
                ->where('provider_user_id', $providerUserId)
                ->first();

            if ($existingProvider) {
                $existingProvider->update(['last_used_at' => now()]);
                $user = $existingProvider->user;
                $user->update(['last_login_at' => now()]);

                return $user;
            }
        }

        $user = User::where('email', $email)->first();

        if ($user) {
            $this->linkProvider($user, $provider, $providerUserId);

            $user->update(['last_login_at' => now()]);

            return $user;
        }

        $dominios = app(SystemSettings::class)->auth_dominios_corporativos;
        $isCorporate = $this->isCorporateEmail($email, $dominios);

        if ($isCorporate) {
            return $this->provisionCorporateUser($provider, $providerUserId, $email, $profileData);
        }

        $allowlistEntry = EmailAllowlist::where('email', $email)->first();
        if ($allowlistEntry) {
            if ($provider !== 'email_password' && ! $allowlistEntry->allowsProvider($provider)) {
                throw new \Illuminate\Auth\Access\AuthorizationException(
                    "El proveedor {$provider} no está permitido para este email."
                );
            }

            return $this->provisionAllowlistUser($allowlistEntry, $provider, $providerUserId, $email, $profileData);
        }

        throw new \Illuminate\Auth\Access\AuthorizationException(
            'Cuenta no autorizada. Contacta al administrador.'
        );
    }

    public function linkProvider(User $user, string $provider, ?string $providerUserId): AuthProvider
    {
        $existing = AuthProvider::where('user_id', $user->id)
            ->where('provider', $provider)
            ->first();

        if ($existing) {
            $existing->update([
                'provider_user_id' => $providerUserId ?? $existing->provider_user_id,
                'last_used_at' => now(),
            ]);

            return $existing;
        }

        $providerCount = $user->authProviders()->count();

        return AuthProvider::create([
            'user_id' => $user->id,
            'provider' => $provider,
            'provider_user_id' => $providerUserId,
            'is_primary' => $providerCount === 0,
            'linked_at' => now(),
            'last_used_at' => now(),
        ]);
    }

    public function unlinkProvider(User $user, string $provider): bool
    {
        $authProvider = AuthProvider::where('user_id', $user->id)
            ->where('provider', $provider)
            ->first();

        if (! $authProvider) {
            return false;
        }

        if ($authProvider->is_primary && $user->authProviders()->count() > 1) {
            throw new \Exception('No puedes desvincular tu método de autenticación principal.');
        }

        if ($user->authProviders()->count() === 1) {
            throw new \Exception('Debes tener al menos un método de autenticación.');
        }

        return $authProvider->delete();
    }

    /**
     * Invita a un usuario EXTERNO (no de nómina). Estos usuarios NO se validan
     * contra RH porque por definición no existen ahí: son clientes, contratistas,
     * proveedores o invitados puntuales con acceso limitado.
     *
     * El email queda registrado y al primer login pasa el check de RhAccessGuard
     * vía la rama de email_allowlist (que esta función no agrega automáticamente:
     * el admin debe agregarlo si quiere bypass permanente del check RH).
     */
    public function inviteExternalUser(string $email, string $role, ?string $departamento = null, ?string $name = null): User
    {
        if (User::where('email', $email)->exists()) {
            throw new \Exception('Ya existe un usuario con este email.');
        }

        $user = User::create([
            'name' => $name ?? explode('@', $email)[0],
            'email' => $email,
            'password' => Hash::make(\Illuminate\Support\Str::random(32)),
            'departamento' => $departamento,
            'status' => 'invited',
        ]);

        $user->assignRole($role);

        $tempPassword = \Illuminate\Support\Str::password(16);

        AuthProvider::create([
            'user_id' => $user->id,
            'provider' => 'email_password',
            'password_hash' => Hash::make($tempPassword),
            'is_primary' => true,
            'linked_at' => now(),
        ]);

        $user->notify(new UserProvisionedNotification($tempPassword));

        return $user;
    }

    private function isCorporateEmail(string $email, array $domains): bool
    {
        $emailDomain = strtolower(explode('@', $email)[1] ?? '');

        foreach ($domains as $domain) {
            if ($emailDomain === strtolower($domain)) {
                return true;
            }
        }

        return false;
    }

    private function provisionCorporateUser(
        string $provider,
        ?string $providerUserId,
        string $email,
        array $profileData,
    ): User {
        // === Validación RH deshabilitada temporalmente ===
        // Se comenta la consulta y el bloqueo contra el servicio externo de RH
        // para dejar limpia la autenticación con Auth0. Reactivar cuando el
        // servicio RH esté estabilizado.
        //
        // $rhData = null;
        // $rhUnavailable = false;
        // try {
        //     $rhClient = app(\App\Services\Rh\RhClientInterface::class);
        //     $rhData = $rhClient->searchByEmail($email);
        // } catch (\App\Services\Rh\RhUnavailableException $e) {
        //     Log::warning("RH API no disponible al provisionar {$email}: " . $e->getMessage());
        //     $rhUnavailable = true;
        // } catch (\Throwable $e) {
        //     Log::warning("RH API lookup failed for {$email}: " . $e->getMessage());
        //     $rhUnavailable = true;
        // }
        //
        // if (! $rhUnavailable) {
        //     if ($rhData === null) {
        //         throw new \Illuminate\Auth\Access\AuthorizationException(
        //             "El email {$email} no está registrado en RH. Contacta al administrador."
        //         );
        //     }
        //     if (! $rhData->activo) {
        //         throw new \Illuminate\Auth\Access\AuthorizationException(
        //             "El usuario de RH {$email} se encuentra inactivo."
        //         );
        //     }
        // }

        $user = User::create([
            'name' => $profileData['name'] ?? explode('@', $email)[0],
            'email' => $email,
            'password' => Hash::make(\Illuminate\Support\Str::random(32)),
            'departamento' => $profileData['departamento'] ?? null,
            'puesto' => $profileData['puesto'] ?? null,
            'employee_id' => $profileData['employee_id'] ?? null,
            'es_socio' => false,
            'status' => 'active',
        ]);

        $role = $this->roleMapper->resolveRoleForUser($user);
        $user->assignRole($role);

        if ($this->socioResolver->isSocio($user)) {
            $user->update(['es_socio' => true]);
            if (! $user->hasRole('socio')) {
                $user->assignRole('socio');
            }
        }

        $this->linkProvider($user, $provider, $providerUserId);

        $user->notify(new UserProvisionedNotification());

        return $user;
    }

    private function provisionAllowlistUser(
        EmailAllowlist $allowlistEntry,
        string $provider,
        ?string $providerUserId,
        string $email,
        array $profileData,
    ): User {
        $user = User::create([
            'name' => $profileData['name'] ?? explode('@', $email)[0],
            'email' => $email,
            'password' => Hash::make(\Illuminate\Support\Str::random(32)),
            'departamento' => $allowlistEntry->departamento_default ?? $profileData['departamento'] ?? null,
            'puesto' => $profileData['puesto'] ?? null,
            'status' => 'active',
        ]);

        $role = $allowlistEntry->role_default ?? 'ingeniero_proyectos';
        $user->assignRole($role);

        $this->linkProvider($user, $provider, $providerUserId);

        $user->notify(new UserProvisionedNotification());

        return $user;
    }
}