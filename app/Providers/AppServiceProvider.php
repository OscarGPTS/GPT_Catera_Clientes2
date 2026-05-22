<?php

namespace App\Providers;

use App\Models\AuthProvider;
use App\Models\Chat\ChatCanal;
use App\Models\Chat\ChatMensaje;
use App\Models\Comercial\Cliente;
use App\Models\Proyectos\Cotizacion;
use App\Models\Proyectos\Proyecto;
use App\Models\User;
use App\Observers\ProyectoChatObserver;
use App\Policies\AuthProviderPolicy;
use App\Policies\ChatCanalPolicy;
use App\Policies\ChatMensajePolicy;
use App\Policies\ClientePolicy;
use App\Policies\CotizacionPolicy;
use App\Policies\ProyectoPolicy;
use App\Policies\UserPolicy;
use App\Services\Auth\AuthOrchestrator;
use App\Services\Rh\RhClientHttp;
use App\Services\Rh\RhClientInterface;
use App\Services\Rh\RhClientMock;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        if (config('rh_api.use_mock', true)) {
            $this->app->singleton(RhClientInterface::class, RhClientMock::class);
        } else {
            $this->app->singleton(RhClientInterface::class, fn() => new RhClientHttp(
                baseUrl: (string) config('rh_api.url'),
                token:   (string) config('rh_api.token'),
            ));
        }

        $this->app->singleton(AuthOrchestrator::class);
    }

    public function boot(): void
    {
        // Cuando estamos detrás de un proxy HTTPS (prod), Laravel debe generar
        // URLs con https:// — si no, Livewire hace requests http:// y el browser
        // los bloquea por Mixed Content.
        if (str_starts_with((string) config('app.url'), 'https://')) {
            URL::forceScheme('https');
        }

        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(AuthProvider::class, AuthProviderPolicy::class);
        Gate::policy(Proyecto::class, ProyectoPolicy::class);
        Gate::policy(Cliente::class, ClientePolicy::class);
        Gate::policy(Cotizacion::class, CotizacionPolicy::class);
        Gate::policy(ChatCanal::class, ChatCanalPolicy::class);
        Gate::policy(ChatMensaje::class, ChatMensajePolicy::class);

        Proyecto::observe(ProyectoChatObserver::class);
    }
}