<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;  
use App\Models\Complaint;
use App\Policies\ComplaintsPolicy;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Complaint::class => ComplaintsPolicy::class,
    ];

    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->registerPolicies();
    }
}
