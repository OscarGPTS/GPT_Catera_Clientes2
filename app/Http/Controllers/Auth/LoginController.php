<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\AuthProvider;
use App\Models\User;
use App\Services\Auth\RoleMapper;
use App\Services\Auth\SocioResolver;
use App\Services\Rh\RhClientInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    public function show()
    {
        if (Auth::check()) {
            return redirect('/');
        }

        return view('auth.login');
    }

    public function authenticate(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $credentials['email'])->first();

        if (! $user) {
            return back()->withErrors(['email' => 'Cuenta no autorizada. Contacta al administrador.']);
        }

        $provider = AuthProvider::where('user_id', $user->id)
            ->where('provider', 'email_password')
            ->first();

        if (! $provider || ! Hash::check($credentials['password'], $provider->password_hash)) {
            return back()->withErrors(['email' => 'Credenciales inválidas.']);
        }

        Auth::login($user);
        $user->update(['last_login_at' => now()]);
        $provider->update(['last_used_at' => now()]);

        $request->session()->regenerate();

        return redirect()->intended('/');
    }
}
