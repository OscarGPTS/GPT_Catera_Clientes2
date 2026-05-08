<?php

namespace App\Services\Auth;

use App\Models\SocioAllowlist;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

class SocioResolver
{
    public function isSocio(User $user): bool
    {
        if ($user->es_socio_override !== null) {
            return $user->es_socio_override;
        }

        if ($user->es_socio) {
            return true;
        }

        return Cache::remember("socio_resolver:{$user->email}", 30 * 60, function () use ($user) {
            return SocioAllowlist::where('email', $user->email)->exists();
        });
    }

    public function refreshSocioStatus(User $user): bool
    {
        $isSocio = $this->isSocio($user);
        $user->update(['es_socio' => $isSocio]);

        if ($isSocio && ! $user->hasRole('socio')) {
            $user->assignRole('socio');
        } elseif (! $isSocio && $user->hasRole('socio')) {
            $user->removeRole('socio');
        }

        return $isSocio;
    }
}