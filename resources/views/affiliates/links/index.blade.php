@extends('affiliates.layouts.app')

@section('content')
<section class="section main-section">
    <div class="card mb-6">
        <header class="card-header">
            <p class="card-header-title">
                <span class="icon"><i class="mdi mdi-plus-circle-outline"></i></span>
                Generate Affiliate Link
            </p>
        </header>
        <div class="card-content">
            <form method="POST" action="{{ route('affiliates.links.store') }}">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                    <div class="md:col-span-3">
                        <label class="label">Product</label>
                        <div class="control">
                            <select name="product_id" class="input" required>
                                <option value="">Select product</option>
                                @foreach ($products as $product)
                                <option value="{{ $product->id }}">{{ $product->name }} - {{ $product->selling_price }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div>
                        <button type="submit" class="button green w-full">
                            <span class="icon"><i class="mdi mdi-link-plus"></i></span>
                            <span>Create Link</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card has-table">
        <header class="card-header">
            <p class="card-header-title">
                <span class="icon"><i class="mdi mdi-link-variant"></i></span>
                My Links
            </p>
        </header>
        <div class="card-content">
            <table>
                <thead>
                    <tr>
                        <th>SI</th>
                        <th>Product</th>
                        <th>Tracking Code</th>
                        <th>Share URL</th>
                        <th>Created</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($links as $link)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $link->product?->name ?? 'N/A' }}</td>
                        <td>{{ $link->tracking_code }}</td>
                        <td class="text-sm">{{ get_affiliate_share_url($link) }}</td>
                        <td>{{ $link->created_at->format('Y-m-d') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="has-text-centered">No links found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</section>
@endsection
