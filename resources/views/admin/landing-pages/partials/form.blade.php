@if($errors->any())
    <div class="notification red mb-4">{{ $errors->first() }}</div>
@endif

<div class="grid gap-4 md:grid-cols-2">
    <div class="field">
        <label class="label">Page Title</label>
        <div class="control">
            <input class="input" type="text" name="title" value="{{ old('title', $landingPage->title) }}" required>
        </div>
    </div>
    <div class="field">
        <label class="label">Slug</label>
        <div class="control">
            <input class="input" type="text" name="slug" value="{{ old('slug', $landingPage->slug) }}" placeholder="goat-milk-soap">
        </div>
    </div>
    <div class="field">
        <label class="label">Status</label>
        <div class="control">
            <div class="select">
                <select name="status">
                    <option value="inactive" @selected(old('status', $landingPage->status) === 'inactive')>Inactive</option>
                    <option value="active" @selected(old('status', $landingPage->status) === 'active')>Active</option>
                </select>
            </div>
        </div>
    </div>
    <div class="field">
        <label class="label">Hero Image</label>
        <div class="control">
            <input class="input" type="file" name="hero_image">
        </div>
        @if($landingPage->hero_image)
            <img src="{{ asset('images/'.$landingPage->hero_image) }}" alt="" class="mt-2" style="width: 90px;">
        @endif
    </div>
</div>

<hr>

<div class="field">
    <label class="label">Select Product</label>
    <div class="grid gap-2 md:grid-cols-2">
        @foreach($products as $product)
            <label class="flex items-center gap-2 rounded border p-3">
                <input type="checkbox" name="product_ids[]" value="{{ $product->id }}" @checked(collect(old('product_ids', $selectedProducts))->contains($product->id))>
                <span>{{ $product->name }} - ৳ {{ number_format((float) $product->selling_price, 2) }}</span>
            </label>
        @endforeach
    </div>
</div>

<hr>

<div class="grid gap-4 md:grid-cols-2">
    <div class="field">
        <label class="label">Hero Title</label>
        <input class="input" type="text" name="hero_title" value="{{ old('hero_title', $landingPage->hero_title) }}">
    </div>
    <div class="field">
        <label class="label">Hero Subtitle</label>
        <input class="input" type="text" name="hero_subtitle" value="{{ old('hero_subtitle', $landingPage->hero_subtitle) }}">
    </div>
</div>
<div class="field">
    <label class="label">Hero Description</label>
    <textarea class="textarea" name="hero_description" rows="4">{{ old('hero_description', $landingPage->hero_description) }}</textarea>
</div>

<hr>

<div class="field">
    <label class="label">Ingredients Title</label>
    <input class="input" type="text" name="ingredients_title" value="{{ old('ingredients_title', $landingPage->ingredients_title) }}">
</div>
@php($ingredients = old('ingredient_names') ? [] : ($landingPage->ingredients ?: []))
@for($i = 0; $i < max(6, count($ingredients)); $i++)
    <div class="grid gap-3 md:grid-cols-4">
        <div class="field">
            <input class="input" type="text" name="ingredient_names[]" placeholder="Ingredient name" value="{{ old('ingredient_names.'.$i, $ingredients[$i]['name'] ?? '') }}">
        </div>
        <div class="field md:col-span-2">
            <input class="input" type="text" name="ingredient_descriptions[]" placeholder="Description" value="{{ old('ingredient_descriptions.'.$i, $ingredients[$i]['description'] ?? '') }}">
        </div>
        <div class="field">
            <input type="hidden" name="existing_ingredient_images[]" value="{{ $ingredients[$i]['image'] ?? '' }}">
            <input class="input" type="file" name="ingredient_images[{{ $i }}]">
            @if(!empty($ingredients[$i]['image']))
                <img src="{{ asset('images/'.$ingredients[$i]['image']) }}" alt="" class="mt-2" style="width: 58px; height: 58px; object-fit: cover;">
            @endif
        </div>
    </div>
@endfor

<hr>

<div class="field">
    <label class="label">Benefits Title</label>
    <input class="input" type="text" name="benefits_title" value="{{ old('benefits_title', $landingPage->benefits_title) }}">
</div>
@php($benefits = old('benefits', $landingPage->benefits ?: []))
@for($i = 0; $i < max(8, count($benefits)); $i++)
    <div class="field">
        <input class="input" type="text" name="benefits[]" placeholder="Benefit point" value="{{ $benefits[$i] ?? '' }}">
    </div>
@endfor

<hr>

<div class="field">
    <label class="label">How To Use Title</label>
    <input class="input" type="text" name="how_to_use_title" value="{{ old('how_to_use_title', $landingPage->how_to_use_title) }}">
</div>
@php($steps = old('how_to_use', $landingPage->how_to_use ?: []))
@for($i = 0; $i < max(5, count($steps)); $i++)
    <div class="field">
        <input class="input" type="text" name="how_to_use[]" placeholder="Usage step" value="{{ $steps[$i] ?? '' }}">
    </div>
@endfor

<hr>

<div class="field">
    <label class="label">Reviews Title</label>
    <input class="input" type="text" name="reviews_title" value="{{ old('reviews_title', $landingPage->reviews_title) }}">
</div>
@php($reviews = old('review_names') ? [] : ($landingPage->customer_reviews ?: []))
@for($i = 0; $i < max(3, count($reviews)); $i++)
    <div class="grid gap-3 md:grid-cols-5">
        <div class="field">
            <input class="input" type="text" name="review_names[]" placeholder="Customer name" value="{{ old('review_names.'.$i, $reviews[$i]['name'] ?? '') }}">
        </div>
        <div class="field md:col-span-3">
            <input class="input" type="text" name="review_texts[]" placeholder="Review text" value="{{ old('review_texts.'.$i, $reviews[$i]['text'] ?? '') }}">
        </div>
        <div class="field">
            <input class="input" type="number" name="review_ratings[]" min="1" max="5" value="{{ old('review_ratings.'.$i, $reviews[$i]['rating'] ?? 5) }}">
        </div>
    </div>
@endfor

<hr>

<div class="field">
    <label class="label">Gallery Images</label>
    <input class="input" type="file" name="gallery_images[]" multiple>
    @if(!empty($landingPage->gallery_images))
        <div class="mt-3 flex flex-wrap gap-2">
            @foreach($landingPage->gallery_images as $image)
                <img src="{{ asset('images/'.$image) }}" alt="" style="width: 80px; height: 80px; object-fit: cover;">
            @endforeach
        </div>
    @endif
</div>

<div class="grid gap-4 md:grid-cols-2">
    <div class="field">
        <label class="label">Checkout Title</label>
        <input class="input" type="text" name="checkout_title" value="{{ old('checkout_title', $landingPage->checkout_title) }}">
    </div>
    <div class="field">
        <label class="label">Final CTA Title</label>
        <input class="input" type="text" name="final_cta_title" value="{{ old('final_cta_title', $landingPage->final_cta_title) }}">
    </div>
</div>
<div class="field">
    <label class="label">Final CTA Text</label>
    <textarea class="textarea" name="final_cta_text" rows="3">{{ old('final_cta_text', $landingPage->final_cta_text) }}</textarea>
</div>

<hr>

<div class="grid gap-4 md:grid-cols-2">
    <div class="field">
        <label class="label">SEO Title</label>
        <input class="input" type="text" name="seo_title" value="{{ old('seo_title', $landingPage->seo_title) }}">
    </div>
    <div class="field">
        <label class="label">SEO Description</label>
        <input class="input" type="text" name="seo_description" value="{{ old('seo_description', $landingPage->seo_description) }}">
    </div>
</div>

<div class="field grouped mt-5">
    <div class="control">
        <button type="submit" class="button green">Save Landing Page</button>
    </div>
    <div class="control">
        <a href="{{ route('admin.landing-pages.index') }}" class="button red">Cancel</a>
    </div>
</div>
