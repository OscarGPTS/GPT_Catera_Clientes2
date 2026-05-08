<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureFinanzasAccess
{
    private const ALLOWED_ROLES = [
        'cfo',
        'direccion_general',
        'socio',
        'comite_socios',
        'analista_financiero',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()) {
            return redirect()->route('login');
        }

        if (! $request->user()->hasAnyRole(self::ALLOWED_ROLES) && ! $request->user()->can('ver finanzas')) {
            abort(403, 'No tienes permiso para acceder a esta sección.');
        }

        return $next($request);
    }
}