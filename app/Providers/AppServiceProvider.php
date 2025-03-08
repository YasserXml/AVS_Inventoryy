<?php

namespace App\Providers;

use App\Models\Barangmasuk;
use App\Observers\BarangmasukObserver;
use Illuminate\Support\ServiceProvider;
use Spatie\Permission\PermissionServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->register(PermissionServiceProvider::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Barangmasuk::observe(BarangmasukObserver::class);
    }
}
