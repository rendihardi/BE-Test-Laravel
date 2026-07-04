<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            \App\Interface\UserInterface::class,
            \App\Repositories\UserRepository::class
        );
        $this->app->bind(
            \App\Interface\RoleInterface::class,
            \App\Repositories\RoleRepository::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
