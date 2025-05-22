<?php

namespace App\Providers;

use App\Models\Ticket;
use App\Policies\TicketPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Ticket::class => TicketPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // Définition de gates supplémentaires si nécessaire
        Gate::define('manage-techniciens', function ($user) {
            return $user->type === 'prestataire' || $user->type === 'admin';
        });
        
        Gate::define('manage-categories', function ($user) {
            return $user->type === 'admin';
        });
    }
} 