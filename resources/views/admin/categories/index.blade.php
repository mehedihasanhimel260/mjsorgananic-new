@extends('layouts.admin_main')
@section('content')
    <section class="section main-section">

        <div class="notification blue">
            <div class="flex flex-col md:flex-row items-center justify-between space-y-3 md:space-y-0">
                <div>
                    <span class="icon"><i class="mdi mdi-buffer"></i></span>
                    <b>Responsive table</b>
                </div>
                <button type="button" class="button small textual --jb-notification-dismiss">Dismiss</button>
            </div>
        </div>

        <div class="card has-table">
            <header class="card-header">
                <p class="card-header-title">
                    <span class="icon"><i class="mdi mdi-account-multiple"></i></span>
                    Category List
                </p>
                <a href="#" class="button blue --jb-modal" data-target="sample-modal">
                    Create Category
                </a>
                <a href="#" class="card-header-icon">
                    <span class="icon"><i class="mdi mdi-reload"></i></span>
                </a>
            </header>
            <div class="card-content">
                <table>
                    <thead>
                        <tr>
                            <th>SI</th>
                            <th>Name</th>
                            <th>Description</th>
                            <th>Quatity</th>
                            <th>Product</th>
                            <th>Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($categories as $category)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td data-label="Name">{{ $category->name }}</td>
                                <td data-label="Description">{{ $category->description }}</td>
                                <td data-label="Quatity"></td>
                                <td data-label="Product"></td>
                                <td data-label="Created">
                                    <small class="text-gray-500"
                                        title="{{ $category->created_at }}">{{ $category->created_at }}</small>
                                </td>
                                <td class="actions-cell">
                                    <div class="buttons right nowrap">
                                        <a href="{{ route('admin.categories.edit', $category->id) }}" class="button small blue" type="button">
                                            <span class="icon"><i class="mdi mdi-eye"></i></span>
                                        </a>
                                        <form action="{{ route('admin.categories.destroy', $category->id) }}"
                                            method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button class="button small red --jb-modal" type="submit">
                                                <span class="icon"><i class="mdi mdi-trash-can"></i></span>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="table-pagination">
                    <div class="flex items-center justify-between">
                        <div class="buttons">
                            <button type="button" class="button active">1</button>
                            <button type="button" class="button">2</button>
                            <button type="button" class="button">3</button>
                        </div>
                        <small>Page 1 of 3</small>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div id="sample-modal" class="modal">
        <div class="modal-background --jb-modal-close"></div>
        <div class="modal-card">
            <header class="modal-card-head">
                <p class="modal-card-title">Category Create</p>
            </header>
            <section class="modal-card-body">
                <div class="card mb-6">
                    <div class="card-content">
                        <form method="post" action="{{ route('admin.categories.store') }}">
                            @csrf
                            <div class="field">
                                <label class="label">Category</label>
                                <div class="field-body">
                                    <div class="field">
                                        <div class="control icons-left">
                                            <input class="input" type="text" name="name"
                                                placeholder="Category Name">
                                            <span class="icon left"><i class="mdi mdi-account"></i></span>
                                        </div>
                                    </div>
                                    <div class="field">
                                        <div class="control icons-left icons-right">
                                            <input class="input" type="text" placeholder="Category discribtion"
                                                name="description">
                                            <span class="icon left"><i class="mdi mdi-mail"></i></span>
                                            <span class="icon right"><i class="mdi mdi-check"></i></span>
                                        </div>
                                    </div>
                                    <div class="field">
                                        <div class="control icons-left icons-right">
                                            <div class="field grouped">
                                                <div class="control">
                                                    <button type="submit" class="button green">
                                                        Submit
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>


            </section>
            <footer class="modal-card-foot">
                <button class="button --jb-modal-close">close</button>
            </footer>
        </div>
    </div>

@endsection
