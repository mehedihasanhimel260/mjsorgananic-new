@extends('affiliates.layouts.app')

@section('content')
<section class="section main-section">
    <div class="card">
        <header class="card-header">
            <p class="card-header-title">
                <span class="icon"><i class="mdi mdi-account-circle-outline"></i></span>
                Affiliate Profile
            </p>
        </header>
        <div class="card-content">
            <form method="POST" action="{{ route('affiliates.account.profile.update') }}">
                @csrf
                @method('PATCH')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="field">
                        <label class="label">Name</label>
                        <div class="control">
                            <input type="text" name="name" class="input" value="{{ old('name', $affiliate->name) }}" required>
                        </div>
                    </div>
                    <div class="field">
                        <label class="label">Phone</label>
                        <div class="control">
                            <input type="text" name="phone" class="input" value="{{ old('phone', $affiliate->phone) }}" required>
                        </div>
                    </div>
                    <div class="field md:col-span-2">
                        <label class="label">Email</label>
                        <div class="control">
                            <input type="email" name="email" class="input" value="{{ old('email', $affiliate->email) }}" required>
                        </div>
                    </div>
                    <div class="field md:col-span-2">
                        <label class="label">Affiliate Code</label>
                        <div class="control">
                            <input type="text" class="input" value="{{ $affiliate->affiliate_code }}" readonly>
                        </div>
                    </div>
                </div>

                <div class="field grouped mt-6">
                    <div class="control">
                        <button type="submit" class="button green">Update Profile</button>
                    </div>
                    <div class="control">
                        <a href="{{ route('affiliates.dashboard') }}" class="button red">Cancel</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</section>
@endsection
