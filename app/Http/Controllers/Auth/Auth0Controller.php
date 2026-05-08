<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\Auth\AuthOrchestrator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class Auth0Controller extends Controller
{
    public function __construct(
        private AuthOrchestrator $orchestrator,
    ) {}

    public function redirect(Request $request)
    {
        if (! config('services.auth0.client_id')) {
            $request->validate([
                'email' => 'required|email',
            ]);

            return $this->handleCorporateLogin($request->input('email'));
        }

        $request->session()->put('auth0_return_to', $request->query('return_to', '/'));
        $auth0 = app(\Auth0\SDK\Auth0::class);

        return redirect($auth0->login());
    }

    public function callback(Request $request)
    {
        try {
            $auth0 = app(\Auth0\SDK\Auth0::class);
            $auth0->handleCallback();

            $user = $auth0->getUser();

            if (! $user) {
                return redirect()->route('login')->withErrors(['email' => 'No se pudo obtener información del usuario.']);
            }

            $email = $user['email'] ?? null;
            $providerUserId = $user['sub'] ?? null;
            $profileData = [
                'name' => $user['name'] ?? $user['nickname'] ?? null,
            ];

            $systemUser = $this->orchestrator->loginOrProvision(
                'auth0',
                $providerUserId,
                $email,
                $profileData,
            );

            Auth::login($systemUser);
            $request->session()->regenerate();

            $returnTo = $request->session()->pull('auth0_return_to', '/');

            return redirect()->intended($returnTo);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return redirect()->route('login')->withErrors(['email' => $e->getMessage()]);
        } catch (\Throwable $e) {
            Log::error('Auth0 callback error: ' . $e->getMessage());

            return redirect()->route('login')->withErrors(['email' => 'Error al procesar el inicio de sesión. Intenta de nuevo.']);
        }
    }

    private function handleCorporateLogin(string $email)
    {
        try {
            $systemUser = $this->orchestrator->loginOrProvision(
                'auth0',
                null,
                $email,
                ['name' => explode('@', $email)[0]],
            );

            Auth::login($systemUser);
            session()->regenerate();

            return redirect()->intended('/');
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return redirect()->route('login')->withErrors(['email' => $e->getMessage()]);
        }
    }
}