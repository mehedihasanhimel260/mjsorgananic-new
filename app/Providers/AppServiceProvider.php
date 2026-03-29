<?php

namespace App\Providers;

use App\Models\FbSetting;
use App\Models\SeoSetting;
use App\Models\SiteMenu;
use App\Models\SitePage;
use App\Models\SiteSetting;
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
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $fbSetting = null;
        $seoSetting = null;
        $siteSetting = null;
        $publicMenus = collect();
        $footerPages = collect();

        try {
            if (Schema::hasTable('fb_settings')) {
                $fbSetting = FbSetting::first();
            }

            if (Schema::hasTable('seo_settings')) {
                $seoSetting = SeoSetting::first();
            }

            if (Schema::hasTable('site_settings')) {
                $siteSetting = SiteSetting::first();
            }

            if (Schema::hasTable('site_menus')) {
                $publicMenus = SiteMenu::with('children')
                    ->whereNull('parent_id')
                    ->where('is_visible', true)
                    ->orderBy('sort_order')
                    ->get();
            }

            if (Schema::hasTable('site_pages')) {
                $footerPages = SitePage::where('status', 'published')
                    ->where('show_in_menu', true)
                    ->orderBy('title')
                    ->get();
            }
        } catch (\Throwable $exception) {
            $fbSetting = null;
            $seoSetting = null;
            $siteSetting = null;
            $publicMenus = collect();
            $footerPages = collect();
        }

        View::share('fbSetting', $fbSetting);
        View::share('seoSetting', $seoSetting);
        View::share('siteSetting', $siteSetting);
        View::share('publicMenus', $publicMenus);
        View::share('footerPages', $footerPages);
    }
}
