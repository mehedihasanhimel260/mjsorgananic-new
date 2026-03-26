<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SitePage;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class SitePageController extends Controller
{
    public function index()
    {
        $pages = SitePage::latest()->paginate(20);

        return view('admin.site-settings.pages.index', compact('pages'));
    }

    public function create()
    {
        return view('admin.site-settings.pages.create');
    }

    public function store(Request $request)
    {
        $validated = $this->validatePage($request);
        SitePage::create($validated);

        return redirect()->route('admin.site-settings.pages.index')->with('success', 'Page created successfully.');
    }

    public function edit(SitePage $sitePage)
    {
        return view('admin.site-settings.pages.edit', compact('sitePage'));
    }

    public function update(Request $request, SitePage $sitePage)
    {
        $validated = $this->validatePage($request, $sitePage->id);
        $sitePage->update($validated);

        return redirect()->route('admin.site-settings.pages.index')->with('success', 'Page updated successfully.');
    }

    private function validatePage(Request $request, ?int $pageId = null): array
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('site_pages', 'slug')->ignore($pageId)],
            'short_intro' => 'nullable|string|max:2000',
            'content' => 'nullable|string',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:2000',
            'status' => 'required|string|in:published,draft',
            'show_in_menu' => 'nullable|boolean',
            'banner_image' => 'nullable|image|mimes:jpg,jpeg,png,webp,svg|max:4096',
        ]);

        $validated['slug'] = $validated['slug'] ?: Str::slug($validated['title']);
        $validated['show_in_menu'] = $request->boolean('show_in_menu');

        if ($request->hasFile('banner_image')) {
            $file = $request->file('banner_image');
            $filename = Str::uuid().'.'.$file->getClientOriginalExtension();
            $file->move(public_path('images'), $filename);
            $validated['banner_image'] = 'images/'.$filename;
        } else {
            unset($validated['banner_image']);
        }

        return $validated;
    }
}
