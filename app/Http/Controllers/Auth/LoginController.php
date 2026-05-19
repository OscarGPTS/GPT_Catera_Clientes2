<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\AuthProvider;
use App\Services\Auth\AuthOrchestrator;
use App\Services\Auth\RhAccessGuard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function __construct(
        private AuthOrchestrator $orchestrator,
        private RhAccessGuard $rhGuard,
    ) {}

    public function show()
    {
        if (Auth::check()) {
            return redirect('/');
        }

        return view('auth.login');
    }

    public function authenticate(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $email = strtolower($request->input('email'));
        $password = $request->input('password');

        $throttleKey = strtolower($email) . '|' . $request->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            throw ValidationException::withMessages([
                'email' => ["Demasiados intentos. Intenta de nuevo en {$seconds} segundos."],
            ]);
        }

        $user = \App\Models\User::where('email', $email)->first();

        if (! $user) {
            RateLimiter::hit($throttleKey);

            throw ValidationException::withMessages([
                'email' => 'Cuenta no autorizada. Contacta al administrador.',
            ]);
        }

        if ($user->status === 'suspended') {
            throw ValidationException::withMessages([
                'email' => 'Esta cuenta está suspendida. Contacta al administrador.',
            ]);
        }

        // Validación contra RH: bloquea si no está en RH activo, salvo
        // que el email esté en email_allowlist o el User tenga status='invited'.
        // Fail-open ante caída del servicio (registra log).
        $rhResult = $this->rhGuard->check($email, $user);
        if (! $rhResult->allowed) {
            RateLimiter::hit($throttleKey);
            throw ValidationException::withMessages([
                'email' => $rhResult->message(),
            ]);
        }

        $provider = AuthProvider::where('user_id', $user->id)
            ->where('provider', 'email_password')
            ->first();

        if (! $provider || ! Hash::check($password, $provider->password_hash)) {
            RateLimiter::hit($throttleKey);

            throw ValidationException::withMessages([
                'email' => 'Credenciales inválidas.',
            ]);
        }

        RateLimiter::clear($throttleKey);

        Auth::login($user);
        $user->update(['last_login_at' => now()]);
        $provider->update(['last_used_at' => now()]);

        $request->session()->regenerate();

        return redirect()->intended('/');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}