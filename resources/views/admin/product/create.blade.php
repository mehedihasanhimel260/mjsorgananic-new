@extends('layouts.admin_main')

@section('content')
<section class="section main-section">
    <div class="card">
        <header class="card-header">
            <p class="card-header-title">
                <span class="icon"><i class="mdi mdi-plus-box-outline"></i></span>
                Create Product
            </p>
        </header>
        <div class="card-content">
            <form method="post" action="{{ route('admin.products.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="field">
                    <label class="label">Category</label>
                    <div class="control">
                        <div class="select">
                            <select name="category_id">
                                @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="field">
                    <label class="label">Name</label>
                    <div class="control">
                        <input class="input" type="text" name="name" placeholder="Product Name" required>
                    </div>
                </div>
                <div class="field">
                    <label class="label">Short Description</label>
                    <div class="control">
                        <textarea class="textarea" name="short_description" placeholder="Short Description"></textarea>
                    </div>
                </div>
                <div class="field">
                    <label class="label">Long Description</label>
                    <div class="control">
                        <textarea id="summernote" name="long_description"></textarea>
                    </div>
                </div>
                <div class="field">
                    <label class="label">Keywords</label>
                    <div class="control">
                        <input class="input" type="text" name="keywords" placeholder="Product Keywords">
                    </div>
                </div>
                <div class="field">
                    <label class="label">SKU</label>
                    <div class="control">
                        <input class="input" type="text" name="sku" placeholder="SKU" required>
                    </div>
                </div>
                <div class="field">
                    <label class="label">Selling Price</label>
                    <div class="control">
                        <input class="input" type="number" step="0.01" name="selling_price" placeholder="Selling Price" required>
                    </div>
                </div>
                <div class="field">
                    <label class="label">Status</label>
                    <div class="control">
                        <div class="select">
                            <select name="status">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="field">
                    <label class="label">Image</label>
                    <div class="control">
                        <input class="input" type="file" name="image">
                    </div>
                </div>
                <hr>
                <div class="field grouped">
                    <div class="control">
                        <button type="submit" class="button green">
                            Create
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
