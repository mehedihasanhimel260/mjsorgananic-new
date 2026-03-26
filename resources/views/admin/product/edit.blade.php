@extends('layouts.admin_main')

@section('content')
<section class="section main-section">
    <div class="card">
        <header class="card-header">
            <p class="card-header-title">
                <span class="icon"><i class="mdi mdi-pencil"></i></span>
                Edit Product
            </p>
            <a href="{{ route('admin.product-commissions.create', ['product_id' => $product->id]) }}" class="button green mr-4">
                <span class="icon"><i class="mdi mdi-cash-plus"></i></span>
                <span>Set Commission</span>
            </a>
        </header>
        <div class="card-content">
            <form method="post" action="{{ route('admin.products.update', $product->id) }}" enctype="multipart/form-data">
                @csrf
                @method('PATCH')
                <div class="field">
                    <label class="label">Category</label>
                    <div class="control">
                        <div class="select">
                            <select name="category_id">
                                @foreach($categories as $category)
                                <option value="{{ $category->id }}" @if($product->category_id == $category->id) selected @endif>{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="field">
                    <label class="label">Name</label>
                    <div class="control">
                        <input class="input" type="text" name="name" placeholder="Product Name" value="{{ $product->name }}" required>
                    </div>
                </div>
                <div class="field">
                    <label class="label">Short Description</label>
                    <div class="control">
                        <textarea class="textarea" name="short_description" placeholder="Short Description">{{ $product->short_description }}</textarea>
                    </div>
                </div>
                <div class="field">
                    <label class="label">Long Description</label>
                    <div class="control">
                        <textarea id="summernote" name="long_description">{{ $product->long_description }}</textarea>
                    </div>
                </div>
                <div class="field">
                    <label class="label">Keywords</label>
                    <div class="control">
                        <input class="input" type="text" name="keywords" placeholder="Product Keywords" value="{{ $product->keywords }}">
                    </div>
                </div>
                <div class="field">
                    <label class="label">SKU</label>
                    <div class="control">
                        <input class="input" type="text" name="sku" placeholder="SKU" value="{{ $product->sku }}" required>
                    </div>
                </div>
                <div class="field">
                    <label class="label">Selling Price</label>
                    <div class="control">
                        <input class="input" type="number" step="0.01" name="selling_price" placeholder="Selling Price" value="{{ $product->selling_price }}" required>
                    </div>
                </div>
                <div class="field">
                    <label class="label">Status</label>
                    <div class="control">
                        <div class="select">
                            <select name="status">
                                <option value="active" @if($product->status == 'active') selected @endif>Active</option>
                                <option value="inactive" @if($product->status == 'inactive') selected @endif>Inactive</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="field">
                    <label class="label">Current Image</label>
                    <div class="control">
                        @if($product->image)
                        <img src="{{ asset('images/'.$product->image) }}" alt="{{ $product->name }}" style="width: 100px; height: auto;">
                        @else
                        No Image
                        @endif
                    </div>
                </div>
                <div class="field">
                    <label class="label">New Image</label>
                    <div class="control">
                        <input class="input" type="file" name="image">
                    </div>
                </div>
                <hr>
                <div class="field grouped">
                    <div class="control">
                        <button type="submit" class="button green">
                            Update
                        </button>
                    </div>
                    <div class="control">
                        <a href="{{ route('admin.products.index') }}" class="button red">
                            Cancel
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<!-- include libraries(jQuery, bootstrap) -->
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js"></script>

<script>
    $(document).ready(function() {
        $('#summernote').summernote({
            placeholder: 'Long Description',
            tabsize: 2,
            height: 300,
            toolbar: [
                ['style', ['style']],
                ['font', ['bold', 'underline', 'clear']],
                ['color', ['color']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['table', ['table']],
                ['insert', ['link', 'picture', 'video']],
                ['view', ['fullscreen', 'codeview', 'help']]
            ]
        });
    });
</script>
@endpush
