<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\AuthProvider;
use App\Services\Auth\AuthOrchestrator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LinkProviderController extends Controller
{
    public function __construct(
        private AuthOrchestrator $orchestrator,
    ) {}

    public function show()
    {
        $user = Auth::user();
        $providers = $user->authProviders;

        return view('auth.link-provider', compact('providers'));
    }

    public function redirect(string $provider)
    {
        $allowedProviders = ['google', 'microsoft', 'apple'];

        if (auth()->check() && in_array($provider, $allowedProviders)) {
            session()->put('link_provider_mode', true);
            session()->put('link_provider', $provider);

            return \Laravel\Socialite\Facades\Socialite::driver($provider)->redirect();
        }

        return redirect()->route('auth.link-provider.show')
            ->withErrors(['provider' => 'Proveedor no soportado.']);
    }

    public function callback(string $provider)
    {
        $allowedProviders = ['google', 'microsoft', 'apple'];

        if (! in_array($provider, $allowedProviders)) {
            return redirect()->route('auth.link-provider.show')
                ->withErrors(['provider' => 'Proveedor no soportado.']);
        }

        try {
            $socialiteUser = \Laravel\Socialite\Facades\Socialite::driver($provider)->user();
            $user = Auth::user();

            $existingLink = AuthProvider::where('provider', $provider)
                ->where('provider_user_id', $socialiteUser->getId())
                ->first();

            if ($existingLink && $existingLink->user_id !== $user->id) {
                return redirect()->route('auth.link-provider.show')
                    ->withErrors(['provider' => 'Esta cuenta ya está vinculada a otro usuario.']);
            }

            $this->orchestrator->linkProvider($user, $provider, $socialiteUser->getId());

            if ($socialiteUser->getAvatar() && ! $user->avatar_url) {
                $user->update(['avatar_url' => $socialiteUser->getAvatar()]);
            }

            return redirect()->route('auth.link-provider.show')
                ->with('success', "Cuenta de {$provider} vinculada correctamente.");
        } catch (\Throwable $e) {
            return redirect()->route('auth.link-provider.show')
                ->withErrors(['provider' => 'Error al vincular la cuenta. Intenta de nuevo.']);
        }
    }

    public function unlink(string $provider)
    {
        try {
            $this->orchestrator->unlinkProvider(Auth::user(), $provider);

            return redirect()->route('auth.link-provider.show')
                ->with('success', "Cuenta de {$provider} desvinculada correctamente.");
        } catch (\Exception $e) {
            return redirect()->route('auth.link-provider.show')
                ->withErrors(['provider' => $e->getMessage()]);
        }
    }
}