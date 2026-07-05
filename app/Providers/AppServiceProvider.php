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
        $this->app->bind(
            \App\Interface\CategoryInterface::class,
            \App\Repositories\CategoryRepository::class
        );
        $this->app->bind(
            \App\Interface\ProductInterface::class,
            \App\Repositories\ProductRepository::class
        );
        $this->app->bind(
            \App\Interface\StockTransactionInterface::class,
            \App\Repositories\StockTransactionRepository::class
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
