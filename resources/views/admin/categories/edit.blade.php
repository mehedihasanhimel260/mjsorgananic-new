@extends('layouts.admin_main')
@section('content')
    <section class="section main-section">
        <div class="card">
            <header class="card-header">
                <p class="card-header-title">
                    <span class="icon"><i class="mdi mdi-account-circle"></i></span>
                    Edit Category
                </p>
            </header>
            <div class="card-content">
                <form method="post" action="{{ route('admin.categories.update', $category->id) }}">
                    @csrf
                    @method('PATCH')
                    <div class="field">
                        <label class="label">Category</label>
                        <div class="field-body">
                            <div class="field">
                                <div class="control icons-left">
                                    <input class="input" type="text" name="name" value="{{ $category->name }}" placeholder="Category Name">
                                    <span class="icon left"><i class="mdi mdi-account"></i></span>
                                </div>
                            </div>
                            <div class="field">
                                <div class="control icons-left icons-right">
                                    <input class="input" type="text" name="description" value="{{ $category->description }}" placeholder="Category description">
                                    <span class="icon left"><i class="mdi mdi-mail"></i></span>
                                    <span class="icon right"><i class="mdi mdi-check"></i></span>
                                </div>
                            </div>
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
                            <a href="{{ route('admin.categories.index') }}" class="button red">
                                Cancel
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </section>
@endsection
