<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\Auth\AuthOrchestrator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;

class SocialiteController extends Controller
{
    private array $allowedProviders = ['google', 'microsoft', 'apple'];

    public function __construct(
        private AuthOrchestrator $orchestrator,
    ) {}

    public function redirect(string $provider): RedirectResponse
    {
        if (! in_array($provider, $this->allowedProviders)) {
            return redirect()->route('login')->withErrors(['email' => 'Proveedor no soportado.']);
        }

        return Socialite::driver($provider)->redirect();
    }

    public function callback(string $provider)
    {
        if (! in_array($provider, $this->allowedProviders)) {
            return redirect()->route('login')->withErrors(['email' => 'Proveedor no soportado.']);
        }

        try {
            $socialiteUser = Socialite::driver($provider)->user();

            $email = $socialiteUser->getEmail();
            $providerUserId = $socialiteUser->getId();
            $profileData = [
                'name' => $socialiteUser->getName(),
                'avatar_url' => $socialiteUser->getAvatar(),
            ];

            $user = $this->orchestrator->loginOrProvision(
                $provider,
                $providerUserId,
                $email,
                $profileData,
            );

            Auth::login($user);
            session()->regenerate();

            return redirect()->intended('/');
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return redirect()->route('login')->withErrors(['email' => $e->getMessage()]);
        } catch (\Throwable $e) {
            Log::error("Socialite {$provider} callback error: " . $e->getMessage());

            return redirect()->route('login')->withErrors(['email' => 'Error al procesar el inicio de sesión. Intenta de nuevo.']);
        }
    }
}