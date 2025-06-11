<?php

namespace App\Providers;
use App\Models\Admin;
use App\Models\Complaint;
use App\Models\CyberComplaint;
use App\Policies\ComplaintsPolicy;
use App\Policies\CyberComplaintsPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Complaint::class => ComplaintsPolicy::class,
        CyberComplaint::class => CyberComplaintsPolicy::class,
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
