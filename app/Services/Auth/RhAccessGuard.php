<?php

namespace App\Services\Auth;

use App\Models\EmailAllowlist;
use App\Models\User;
use App\Services\Rh\RhClientInterface;
use App\Services\Rh\RhUnavailableException;
use Illuminate\Support\Facades\Log;

/**
 * Decide si un email tiene permitido autenticarse según RH.
 *
 * Reglas (en orden):
 *   1) Si el email está en `email_allowlist` → bypass (invitado externo).
 *   2) Si el User local tiene status='invited' → bypass (invitado externo
 *      ya creado vía inviteExternalUser pero aún no activado).
 *   3) Si RH responde y el usuario existe + activo=true → permitir.
 *   4) Si RH responde y el usuario existe + activo=false → BLOQUEAR.
 *   5) Si RH responde y el usuario NO existe → BLOQUEAR.
 *   6) Si RH no responde (timeout/5xx) → FAIL-OPEN: permitir + log warning.
 *
 * Cache lo aplica RhClientHttp internamente (5 min por email).
 */
class RhAccessGuard
{
    public const REASON_OK              = 'ok';
    public const REASON_EXTERNAL        = 'external_allowlist';
    public const REASON_INVITED         = 'invited_user';
    public const REASON_INACTIVE        = 'rh_inactive';
    public const REASON_NOT_FOUND       = 'rh_not_found';
    public const REASON_API_UNAVAILABLE = 'rh_unavailable_failopen';

    public function __construct(
        private readonly RhClientInterface $rh,
    ) {}

    public function check(string $email, ?User $user = null): RhAccessResult
    {
        $email = mb_strtolower(trim($email));

        if (EmailAllowlist::where('email', $email)->exists()) {
            return new RhAccessResult(true, self::REASON_EXTERNAL);
        }

        if ($user !== null && $user->status === 'invited') {
            return new RhAccessResult(true, self::REASON_INVITED);
        }

        try {
            $rhUser = $this->rh->searchByEmail($email);
        } catch (RhUnavailableException $e) {
            Log::warning("RH API no disponible para {$email}: " . $e->getMessage());
            return new RhAccessResult(true, self::REASON_API_UNAVAILABLE);
        } catch (\Throwable $e) {
            Log::error("RH API error inesperado para {$email}: " . $e->getMessage());
            return new RhAccessResult(true, self::REASON_API_UNAVAILABLE);
        }

        if ($rhUser === null) {
            return new RhAccessResult(false, self::REASON_NOT_FOUND);
        }

        if (! $rhUser->activo) {
            return new RhAccessResult(false, self::REASON_INACTIVE);
        }

        return new RhAccessResult(true, self::REASON_OK);
    }
}

class RhAccessResult
{
    public function __construct(
        public readonly bool $allowed,
        public readonly string $reason,
    ) {}

    public function message(): string
    {
        return match ($this->reason) {
            RhAccessGuard::REASON_INACTIVE   => 'Tu cuenta de RH se encuentra inactiva. Contacta a Recursos Humanos.',
            RhAccessGuard::REASON_NOT_FOUND  => 'No estás dado de alta en RH. Contacta al administrador.',
            default                          => 'Acceso no autorizado.',
        };
    }
}
