<?php

namespace App\Providers;

use App\Services\Rh\RhClientInterface;
use App\Services\Rh\RhClientMock;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        if (config('rh_api.use_mock', true)) {
            $this->app->singleton(RhClientInterface::class, RhClientMock::class);
        }
    }

    public function boot(): void
    {
        //
    }
}
