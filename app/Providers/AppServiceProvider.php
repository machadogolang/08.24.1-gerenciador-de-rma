<?php

namespace App\Providers;

use App\Rma\Dominio\RepositorioDeRmas;
use App\Rma\Infraestrutura\RmasEmBanco;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(RepositorioDeRmas::class, RmasEmBanco::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
