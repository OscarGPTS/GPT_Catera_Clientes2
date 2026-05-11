<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SocioAllowlist;
use App\Models\User;
use Illuminate\Http\Request;

class SocioController extends Controller
{
    public function index()
    {
        return view('admin.socios');
    }

    public function toggleOverride(User $usuario)
    {
        $currentOverride = $usuario->es_socio_override;
        $newOverride = $currentOverride === null ? true : ! $currentOverride;

        $usuario->update(['es_socio_override' => $newOverride]);

        $esSocio = (new \App\Services\Auth\SocioResolver())->isSocio($usuario);
        $usuario->update(['es_socio' => $esSocio]);

        if ($esSocio && ! $usuario->hasRole('socio')) {
            $usuario->assignRole('socio');
        } elseif (! $esSocio && $usuario->hasRole('socio')) {
            $usuario->removeRole('socio');
        }

        return back()->with('success', 'Estado de socio actualizado.');
    }

    public function addAllowlist(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email|unique:socios_allowlist,email',
            'notes' => 'nullable|string|max:500',
        ]);

        SocioAllowlist::create([
            'email' => strtolower($validated['email']),
            'notes' => $validated['notes'],
            'added_by' => auth()->user()->name,
        ]);

        $user = User::where('email', $validated['email'])->first();
        if ($user) {
            $esSocio = (new \App\Services\Auth\SocioResolver())->isSocio($user);
            $user->update(['es_socio' => $esSocio]);
            if ($esSocio && ! $user->hasRole('socio')) {
                $user->assignRole('socio');
            }
        }

        return back()->with('success', 'Email agregado a la lista de socios.');
    }

    public function removeAllowlist(SocioAllowlist $socio)
    {
        $email = $socio->email;
        $socio->delete();

        $user = User::where('email', $email)->first();
        if ($user) {
            \Illuminate\Support\Facades\Cache::forget("socio_resolver:{$email}");
            $esSocio = (new \App\Services\Auth\SocioResolver())->isSocio($user);
            $user->update(['es_socio' => $esSocio]);
            if (! $esSocio && $user->hasRole('socio')) {
                $user->removeRole('socio');
            }
        }

        return back()->with('success', 'Email removido de la lista de socios.');
    }
}