<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LandingPage;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class LandingPageController extends Controller
{
    public function index()
    {
        $landingPages = LandingPage::with('products')
            ->latest()
            ->paginate(15);

        return view('admin.landing-pages.index', compact('landingPages'));
    }

    public function create()
    {
        $landingPage = new LandingPage([
            'status' => 'inactive',
            'ingredients_title' => 'উপাদানভিত্তিক উপকারিতা',
            'benefits_title' => 'এই সাবান ব্যবহারে যে উপকারগুলো পাওয়া যায়',
            'how_to_use_title' => 'ব্যবহারের নিয়ম',
            'reviews_title' => 'Customer Review',
            'checkout_title' => 'অর্ডার করতে নিচের ফর্মটি পূরণ করে প্লেস অর্ডার বাটনে ক্লিক করুন!',
        ]);
        $products = Product::where('status', 'active')->orderBy('name')->get();
        $selectedProducts = collect();

        return view('admin.landing-pages.create', compact('landingPage', 'products', 'selectedProducts'));
    }

    public function store(Request $request)
    {
        $validated = $this->validateLandingPage($request);
        $validated['slug'] = $this->uniqueSlug($validated['slug'] ?: $validated['title']);
        $validated['hero_image'] = $this->uploadImage($request, 'hero_image');
        $validated['gallery_images'] = $this->uploadGalleryImages($request);
        $validated = array_merge($validated, $this->dynamicContent($request));

        $landingPage = LandingPage::create($validated);
        $this->deactivateOtherLandingPages($landingPage);
        $this->syncProducts($landingPage, $request->input('product_ids', []));

        return redirect()->route('admin.landing-pages.index')->with('success', 'Landing page created successfully.');
    }

    public function edit(LandingPage $landingPage)
    {
        $landingPage->load('products');
        $products = Product::where('status', 'active')->orderBy('name')->get();
        $selectedProducts = $landingPage->products->pluck('id');

        return view('admin.landing-pages.edit', compact('landingPage', 'products', 'selectedProducts'));
    }

    public function update(Request $request, LandingPage $landingPage)
    {
        $validated = $this->validateLandingPage($request, $landingPage);
        $validated['slug'] = $this->uniqueSlug($validated['slug'] ?: $validated['title'], $landingPage);

        if ($request->hasFile('hero_image')) {
            $validated['hero_image'] = $this->uploadImage($request, 'hero_image');
        }

        $galleryImages = $landingPage->gallery_images ?: [];
        if ($request->hasFile('gallery_images')) {
            $galleryImages = array_merge($galleryImages, $this->uploadGalleryImages($request));
        }
        $validated['gallery_images'] = array_values(array_filter($galleryImages));
        $validated = array_merge($validated, $this->dynamicContent($request));

        $landingPage->update($validated);
        $this->deactivateOtherLandingPages($landingPage);
        $this->syncProducts($landingPage, $request->input('product_ids', []));

        return redirect()->route('admin.landing-pages.index')->with('success', 'Landing page updated successfully.');
    }

    public function destroy(LandingPage $landingPage)
    {
        $landingPage->delete();

        return redirect()->route('admin.landing-pages.index')->with('success', 'Landing page deleted successfully.');
    }

    private function validateLandingPage(Request $request, ?LandingPage $landingPage = null): array
    {
        return $request->validate([
            'title' => 'required|string|max:255',
            'slug' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('landing_pages', 'slug')->ignore($landingPage),
            ],
            'status' => ['required', Rule::in(['inactive', 'active'])],
            'hero_title' => 'nullable|string|max:255',
            'hero_subtitle' => 'nullable|string|max:255',
            'hero_description' => 'nullable|string',
            'hero_image' => 'nullable|image|max:4096',
            'ingredients_title' => 'nullable|string|max:255',
            'benefits_title' => 'nullable|string|max:255',
            'how_to_use_title' => 'nullable|string|max:255',
            'reviews_title' => 'nullable|string|max:255',
            'checkout_title' => 'nullable|string|max:255',
            'final_cta_title' => 'nullable|string|max:255',
            'final_cta_text' => 'nullable|string',
            'seo_title' => 'nullable|string|max:255',
            'seo_description' => 'nullable|string|max:500',
            'product_ids' => 'required|array|min:1',
            'product_ids.*' => 'exists:products,id',
            'ingredient_images.*' => 'nullable|image|max:4096',
            'gallery_images.*' => 'nullable|image|max:4096',
        ]);
    }

    private function dynamicContent(Request $request): array
    {
        return [
            'ingredients' => $this->ingredientRows($request),
            'benefits' => $this->simpleRows($request->input('benefits', [])),
            'how_to_use' => $this->simpleRows($request->input('how_to_use', [])),
            'customer_reviews' => $this->reviewRows($request),
        ];
    }

    private function ingredientRows(Request $request): array
    {
        $names = $request->input('ingredient_names', []);
        $descriptions = $request->input('ingredient_descriptions', []);
        $existingImages = $request->input('existing_ingredient_images', []);
        $rows = [];

        foreach ($names as $index => $name) {
            $name = trim((string) $name);
            $description = trim((string) ($descriptions[$index] ?? ''));
            $image = $existingImages[$index] ?? null;

            if ($name === '' && $description === '') {
                continue;
            }

            if ($request->hasFile("ingredient_images.{$index}")) {
                $image = $this->uploadIndexedImage($request->file("ingredient_images.{$index}"));
            }

            $rows[] = [
                'name' => $name,
                'description' => $description,
                'image' => $image,
            ];
        }

        return $rows;
    }

    private function reviewRows(Request $request): array
    {
        $names = $request->input('review_names', []);
        $texts = $request->input('review_texts', []);
        $ratings = $request->input('review_ratings', []);
        $rows = [];

        foreach ($names as $index => $name) {
            $name = trim((string) $name);
            $text = trim((string) ($texts[$index] ?? ''));

            if ($name === '' && $text === '') {
                continue;
            }

            $rows[] = [
                'name' => $name,
                'text' => $text,
                'rating' => max(1, min(5, (int) ($ratings[$index] ?? 5))),
            ];
        }

        return $rows;
    }

    private function simpleRows(array $rows): array
    {
        return array_values(array_filter(array_map(function ($row) {
            return trim((string) $row);
        }, $rows)));
    }

    private function uploadImage(Request $request, string $field): ?string
    {
        if (! $request->hasFile($field)) {
            return null;
        }

        $file = $request->file($field);
        $name = time().'-'.Str::random(8).'.'.$file->getClientOriginalExtension();
        $file->move(public_path('images'), $name);

        return $name;
    }

    private function uploadGalleryImages(Request $request): array
    {
        if (! $request->hasFile('gallery_images')) {
            return [];
        }

        $images = [];
        foreach ($request->file('gallery_images') as $file) {
            $name = time().'-'.Str::random(8).'.'.$file->getClientOriginalExtension();
            $file->move(public_path('images'), $name);
            $images[] = $name;
        }

        return $images;
    }

    private function uploadIndexedImage($file): string
    {
        $name = time().'-'.Str::random(8).'.'.$file->getClientOriginalExtension();
        $file->move(public_path('images'), $name);

        return $name;
    }

    private function uniqueSlug(string $value, ?LandingPage $landingPage = null): string
    {
        $slug = Str::slug($value);
        $baseSlug = $slug ?: Str::random(8);
        $slug = $baseSlug;
        $counter = 2;

        while (LandingPage::where('slug', $slug)->when($landingPage, fn ($query) => $query->whereKeyNot($landingPage->id))->exists()) {
            $slug = $baseSlug.'-'.$counter;
            $counter++;
        }

        return $slug;
    }

    private function syncProducts(LandingPage $landingPage, array $productIds): void
    {
        $syncData = [];
        foreach (array_values($productIds) as $index => $productId) {
            $syncData[$productId] = ['sort_order' => $index + 1];
        }

        $landingPage->products()->sync($syncData);
    }

    private function deactivateOtherLandingPages(LandingPage $landingPage): void
    {
        if ($landingPage->status !== 'active') {
            return;
        }

        LandingPage::whereKeyNot($landingPage->id)->update(['status' => 'inactive']);
    }
}
