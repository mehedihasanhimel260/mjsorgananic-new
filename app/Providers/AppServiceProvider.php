<?php

namespace App\Providers;

use App\Models\FbSetting;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        require_once app_path('helpers/helper.php');
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $fbSetting = null;

        try {
            if (Schema::hasTable('fb_settings')) {
                $fbSetting = FbSetting::first();
            }
        } catch (\Throwable $exception) {
            $fbSetting = null;
        }

        View::share('fbSetting', $fbSetting);
    }
}
