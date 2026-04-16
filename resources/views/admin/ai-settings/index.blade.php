@extends('layouts.admin_main')

@section('content')
<section class="section main-section">
    <div class="card has-table">
        <header class="card-header">
            <p class="card-header-title">
                <span class="icon"><i class="mdi mdi-robot-outline"></i></span>
                AI API Settings
            </p>
        </header>
        <div class="card-content">
            <table>
                <thead>
                    <tr>
                        <th>SI</th>
                        <th>Title</th>
                        <th>Model Name</th>
                        <th>Status</th>
                        <th>Edit</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($settings as $setting)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td data-label="Title">{{ $setting->title }}</td>
                        <td data-label="Model Name">{{ $setting->model_name ?: 'N/A' }}</td>
                        <td data-label="Status">
                            <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $setting->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                                {{ $setting->is_active ? 'On' : 'Off' }}
                            </span>
                        </td>
                        <td class="actions-cell">
                            <div class="buttons right nowrap">
                                <a href="{{ route('admin.ai-settings.edit', $setting->id) }}" class="button small blue" type="button">
                                    <span class="icon"><i class="mdi mdi-pencil"></i></span>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="has-text-centered">No AI setting found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card mt-6">
        <header class="card-header">
            <p class="card-header-title">
                <span class="icon"><i class="mdi mdi-cron"></i></span>
                cPanel Cron Guide
            </p>
        </header>
        <div class="card-content">
            <p class="mb-3">Use this cron command in cPanel so AI replies from chat widget and Facebook messages are processed every minute.</p>
            <div class="notification is-light">
                <code>* * * * * curl -s {{ route('queue.work') }}</code>
            </div>
            <p class="text-sm text-gray-600">This URL trigger runs the queue worker endpoint that processes pending AI reply jobs.</p>
        </div>
    </div>
</section>
@endsection
