<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteMenu;
use App\Models\SitePage;
use Illuminate\Http\Request;

class SiteMenuController extends Controller
{
    public function index()
    {
        $menus = SiteMenu::with('parent')->orderBy('sort_order')->latest('id')->get();
        $pages = SitePage::where('status', 'published')->orderBy('title')->get();
        $parentMenus = SiteMenu::whereNull('parent_id')->orderBy('sort_order')->get();

        return view('admin.site-settings.menus', compact('menus', 'pages', 'parentMenus'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'menu_type' => 'required|string|in:internal_page,custom_link,category,product_section',
            'target_slug' => 'nullable|string|max:255',
            'url' => 'nullable|string|max:2048',
            'sort_order' => 'nullable|integer|min:0',
            'parent_id' => 'nullable|exists:site_menus,id',
            'is_visible' => 'nullable|boolean',
            'open_in_new_tab' => 'nullable|boolean',
        ]);

        $validated['is_visible'] = $request->boolean('is_visible');
        $validated['open_in_new_tab'] = $request->boolean('open_in_new_tab');

        SiteMenu::create($validated);

        return redirect()->route('admin.site-settings.menus')->with('success', 'Menu item created successfully.');
    }

    public function edit(SiteMenu $siteMenu)
    {
        $pages = SitePage::where('status', 'published')->orderBy('title')->get();
        $parentMenus = SiteMenu::whereNull('parent_id')->where('id', '!=', $siteMenu->id)->orderBy('sort_order')->get();

        return view('admin.site-settings.menu-edit', compact('siteMenu', 'pages', 'parentMenus'));
    }

    public function update(Request $request, SiteMenu $siteMenu)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'menu_type' => 'required|string|in:internal_page,custom_link,category,product_section',
            'target_slug' => 'nullable|string|max:255',
            'url' => 'nullable|string|max:2048',
            'sort_order' => 'nullable|integer|min:0',
            'parent_id' => 'nullable|exists:site_menus,id',
            'is_visible' => 'nullable|boolean',
            'open_in_new_tab' => 'nullable|boolean',
        ]);

        $validated['is_visible'] = $request->boolean('is_visible');
        $validated['open_in_new_tab'] = $request->boolean('open_in_new_tab');

        $siteMenu->update($validated);

        return redirect()->route('admin.site-settings.menus')->with('success', 'Menu item updated successfully.');
    }
}
