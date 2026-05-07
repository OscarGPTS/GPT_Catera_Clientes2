<?php

namespace App\Services\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

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
            return DB::table('socios_allowlist')
                ->where('email', $user->email)
                ->exists();
        });
    }
}
