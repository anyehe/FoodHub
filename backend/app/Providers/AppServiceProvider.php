<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Application service bindings can be registered here.
    }

    public function boot(): void
    {
        // Application bootstrapping hooks can be registered here.
    }
}
