<form method="POST" action="{{ $action }}" enctype="multipart/form-data">
    @csrf
    @if($method !== 'POST') @method($method) @endif
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="field"><label class="label">Title</label><input class="input" type="text" name="title" value="{{ old('title', $sitePage?->title) }}" required></div>
        <div class="field"><label class="label">Slug</label><input class="input" type="text" name="slug" value="{{ old('slug', $sitePage?->slug) }}"></div>
        <div class="field md:col-span-2"><label class="label">Short Intro</label><textarea class="textarea" name="short_intro" rows="3">{{ old('short_intro', $sitePage?->short_intro) }}</textarea></div>
        <div class="field md:col-span-2"><label class="label">Content (HTML allowed)</label><textarea id="summernote" class="textarea" name="content" rows="12">{{ old('content', $sitePage?->content) }}</textarea></div>
        <div class="field"><label class="label">Meta Title</label><input class="input" type="text" name="meta_title" value="{{ old('meta_title', $sitePage?->meta_title) }}"></div>
        <div class="field"><label class="label">Status</label><select class="input" name="status"><option value="draft" {{ old('status', $sitePage?->status) === 'draft' ? 'selected' : '' }}>Draft</option><option value="published" {{ old('status', $sitePage?->status) === 'published' ? 'selected' : '' }}>Published</option></select></div>
        <div class="field md:col-span-2"><label class="label">Meta Description</label><textarea class="textarea" name="meta_description" rows="3">{{ old('meta_description', $sitePage?->meta_description) }}</textarea></div>
        <div class="field"><label class="label">Banner Image</label><input class="input" type="file" name="banner_image"> @if($sitePage?->banner_image)<img src="{{ asset($sitePage->banner_image) }}" class="mt-2 h-20 rounded">@endif</div>
        <div class="field flex items-end"><label class="checkbox"><input type="checkbox" name="show_in_menu" value="1" {{ old('show_in_menu', $sitePage?->show_in_menu) ? 'checked' : '' }}> <span class="check"></span><span class="control-label">Show in Menu</span></label></div>
    </div>
    <div class="field grouped mt-6"><div class="control"><button type="submit" class="button green">Save Page</button></div><div class="control"><a href="{{ route('admin.site-settings.pages.index') }}" class="button red">Cancel</a></div></div>
</form>

@push('scripts')
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js"></script>

<script>
    $(document).ready(function() {
        $('#summernote').summernote({
            placeholder: 'Page content',
            tabsize: 2,
            height: 320,
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
