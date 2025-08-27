<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Blade;



class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (app()->isProduction()) {
            URL::forceScheme('https');
        }

        Blade::if('taskLocked', function ($task) {
            return (bool)($task?->complete);
        });

        Blade::if('taskOpen', function ($task) {
            return ! (bool)($task?->complete);
        });

        Blade::if('canAccess', function (string $resource, ...$needs) {
            $user = auth()->user();
            if (!$user) return false;
            return \App\Services\Access::can($user, $resource, $needs);
        });
    }
}
