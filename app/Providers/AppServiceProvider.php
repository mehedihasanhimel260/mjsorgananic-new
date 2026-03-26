<?php

namespace App\Providers;

use App\Models\FbSetting;
use App\Models\SeoSetting;
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
        $seoSetting = null;

        try {
            if (Schema::hasTable('fb_settings')) {
                $fbSetting = FbSetting::first();
            }

            if (Schema::hasTable('seo_settings')) {
                $seoSetting = SeoSetting::first();
            }
        } catch (\Throwable $exception) {
            $fbSetting = null;
            $seoSetting = null;
        }

        View::share('fbSetting', $fbSetting);
        View::share('seoSetting', $seoSetting);
    }
}
