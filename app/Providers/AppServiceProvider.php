<?php

namespace App\Providers;

use App\Http\Responses\Auth\LogoutResponse as AuthLogoutResponse;
use App\Listeners\NotificationListener;
use App\Models\Barangmasuk;
use App\Models\Pengajuan;
use App\Observers\BarangmasukObserver;
use App\Observers\PengajuanObserver;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Filament\Http\Responses\Auth\Contracts\LogoutResponse;
use Illuminate\Support\ServiceProvider;
use Spatie\Permission\PermissionServiceProvider;
use TomatoPHP\FilamentSimpleTheme\FilamentSimpleThemeServiceProvider;

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
        date_default_timezone_set('Asia/Jakarta');
        Barangmasuk::observe(BarangmasukObserver::class);
        Pengajuan::observe(PengajuanObserver::class);
        Carbon::setLocale('id');
        
        CarbonImmutable::setLocale('id');
    }
}
