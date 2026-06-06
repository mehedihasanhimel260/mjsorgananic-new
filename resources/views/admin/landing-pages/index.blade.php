@extends('layouts.admin_main')

@section('content')
<section class="section main-section">
    <div class="card has-table">
        <header class="card-header">
            <p class="card-header-title">
                <span class="icon"><i class="mdi mdi-file-star-outline"></i></span>
                Landing Pages
            </p>
            <a href="{{ route('admin.landing-pages.create') }}" class="button blue">
                <span class="icon"><i class="mdi mdi-plus"></i></span>
                <span>Create Landing Page</span>
            </a>
        </header>
        <div class="card-content">
            @if(session('success'))
                <div class="notification green mb-4">{{ session('success') }}</div>
            @endif
            <table>
                <thead>
                    <tr>
                        <th>SI</th>
                        <th>Title</th>
                        <th>Products</th>
                        <th>Status</th>
                        <th>URL</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($landingPages as $landingPage)
                        <tr>
                            <td>{{ $landingPages->firstItem() + $loop->index }}</td>
                            <td>{{ $landingPage->title }}</td>
                            <td>{{ $landingPage->products->pluck('name')->join(', ') ?: 'No product' }}</td>
                            <td>{{ ucfirst($landingPage->status) }}</td>
                            <td>
                                <a href="{{ route('landing.show', $landingPage->slug) }}" target="_blank" class="text-blue-600 hover:underline">
                                    /landing/{{ $landingPage->slug }}
                                </a>
                            </td>
                            <td class="actions-cell">
                                <div class="buttons right nowrap">
                                    <a href="{{ route('admin.landing-pages.edit', $landingPage->id) }}" class="button small blue">
                                        <span class="icon"><i class="mdi mdi-pencil"></i></span>
                                    </a>
                                    <form action="{{ route('admin.landing-pages.destroy', $landingPage->id) }}" method="POST" onsubmit="return confirm('Delete this landing page?');">
                                        @csrf
                                        @method('DELETE')
                                        <button class="button small red" type="submit">
                                            <span class="icon"><i class="mdi mdi-trash-can"></i></span>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="has-text-centered">No landing pages found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            <div class="table-pagination">
                {{ $landingPages->links() }}
            </div>
        </div>
    </div>
</section>
@endsection
