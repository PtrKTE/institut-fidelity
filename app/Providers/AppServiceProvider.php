<?php

namespace App\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Force URL prefix for subdirectory deployment
        URL::forceRootUrl(config('app.url'));

        // Blade directives for cliente auth
        Blade::if('auth_cliente', fn() => session()->has('cliente_id'));
    }
}
