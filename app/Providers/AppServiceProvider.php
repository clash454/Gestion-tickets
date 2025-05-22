<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Filament\Navigation\NavigationGroup;
use Filament\Panel;
use Illuminate\Support\Facades\Gate;

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
        // Le panel Filament est déjà enregistré dans bootstrap/providers.php
        // Aucun besoin de l'enregistrer à nouveau ici
    }
}
