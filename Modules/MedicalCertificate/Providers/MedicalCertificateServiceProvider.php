<?php

namespace Modules\MedicalCertificate\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;

class MedicalCertificateServiceProvider extends ServiceProvider
{
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
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        $this->loadViewsFrom(__DIR__ . '/../Resources/views', 'medicalcertificate');
        
        Route::middleware(['web'])
            ->group(function () {
                $this->loadRoutesFrom(__DIR__ . '/../Routes/web.php');
            });
    }
}
