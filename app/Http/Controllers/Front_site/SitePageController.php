<?php

namespace App\Http\Controllers\Front_site;

use App\Http\Controllers\Controller;
use App\Models\SitePage;

class SitePageController extends Controller
{
    public function show(string $slug)
    {
        $page = SitePage::where('slug', $slug)
            ->where('status', 'published')
            ->firstOrFail();

        return view('front-site.page', compact('page'));
    }
}
