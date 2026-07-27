<?php

namespace App\Providers;

use App\Models\MikrotikSetting;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Throwable;

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
        Paginator::useBootstrapFive();

        // Bagikan router aktif ke shell admin untuk indikator sinyal di topbar.
        View::composer('layouts.admin', function ($view) {
            $router = null;
            try {
                $router = MikrotikSetting::active();
            } catch (Throwable $e) {
                // tabel belum ada / migrasi belum jalan — abaikan
            }
            $view->with('navRouter', $router);
        });
    }
}
