<?php

namespace App\Providers;

use App\Models\AuthProvider;
use App\Models\Comercial\Cliente;
use App\Models\Proyectos\Cotizacion;
use App\Models\Proyectos\Proyecto;
use App\Models\User;
use App\Observers\ProyectoChatObserver;
use App\Policies\AuthProviderPolicy;
use App\Policies\ClientePolicy;
use App\Policies\CotizacionPolicy;
use App\Policies\ProyectoPolicy;
use App\Policies\UserPolicy;
use App\Services\Auth\AuthOrchestrator;
use App\Services\Rh\RhClientInterface;
use App\Services\Rh\RhClientMock;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        if (config('rh_api.use_mock', true)) {
            $this->app->singleton(RhClientInterface::class, RhClientMock::class);
        }

        $this->app->singleton(AuthOrchestrator::class);
    }

    public function boot(): void
    {
        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(AuthProvider::class, AuthProviderPolicy::class);
        Gate::policy(Proyecto::class, ProyectoPolicy::class);
        Gate::policy(Cliente::class, ClientePolicy::class);
        Gate::policy(Cotizacion::class, CotizacionPolicy::class);

        Proyecto::observe(ProyectoChatObserver::class);
    }
}