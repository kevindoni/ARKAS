<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\QueryException;
use App\Models\Setting;
use Illuminate\Pagination\Paginator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Use Bootstrap pagination views for AdminLTE compatibility
        Paginator::useBootstrap();

        // Share app settings (name, logo) to all views, guard if table not ready
        $appName = config('app.name', 'Laravel');
        $appLogo = null;
        try {
            if (Schema::hasTable('settings')) {
                $appName = Setting::get('app_name', $appName);
                $appLogo = Setting::get('app_logo');
            }
        } catch (QueryException $e) {
            // ignore during initial migration
        }
        View::share('appName', $appName);
        View::share('appLogo', $appLogo);
    }
}
