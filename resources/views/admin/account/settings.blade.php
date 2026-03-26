@extends('layouts.admin_main')

@section('content')
<section class="section main-section">
    <div class="card">
        <header class="card-header">
            <p class="card-header-title">
                <span class="icon"><i class="mdi mdi-cog-outline"></i></span>
                Admin Settings
            </p>
        </header>
        <div class="card-content">
            <form method="POST" action="{{ route('admin.account.settings.update') }}">
                @csrf
                @method('PATCH')

                <div class="field">
                    <label class="label">Current Password</label>
                    <div class="control">
                        <input type="password" name="current_password" class="input" required>
                    </div>
                </div>
                <div class="field">
                    <label class="label">New Password</label>
                    <div class="control">
                        <input type="password" name="password" class="input" required>
                    </div>
                </div>
                <div class="field">
                    <label class="label">Confirm New Password</label>
                    <div class="control">
                        <input type="password" name="password_confirmation" class="input" required>
                    </div>
                </div>

                <div class="field grouped mt-6">
                    <div class="control">
                        <button type="submit" class="button green">Update Password</button>
                    </div>
                    <div class="control">
                        <a href="{{ route('admin.dashboard') }}" class="button red">Cancel</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</section>
@endsection
