@extends('layouts.admin_main')

@section('content')
<section class="section main-section">
    <div class="card">
        <header class="card-header">
            <p class="card-header-title">
                <span class="icon"><i class="mdi mdi-account-edit"></i></span>
                Edit User
            </p>
        </header>
        <div class="card-content">
            <form method="POST" action="{{ route('admin.users.update', $user->id) }}">
                @csrf
                @method('PATCH')

                <div class="field">
                    <label class="label">Name</label>
                    <div class="control">
                        <input class="input" type="text" name="name" value="{{ old('name', $user->name) }}" required>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="field">
                        <label class="label">Phone</label>
                        <div class="control">
                            <input class="input" type="text" name="phone" value="{{ old('phone', $user->phone) }}" required>
                        </div>
                    </div>
                    <div class="field">
                        <label class="label">Alternative Phone</label>
                        <div class="control">
                            <input class="input" type="text" name="alternative_phone" value="{{ old('alternative_phone', $user->alternative_phone) }}">
                        </div>
                    </div>
                </div>

                <div class="field">
                    <label class="label">Email</label>
                    <div class="control">
                        <input class="input" type="email" name="email" value="{{ old('email', $user->email) }}">
                    </div>
                </div>

                <div class="field">
                    <label class="label">IP Address</label>
                    <div class="control">
                        <input class="input" type="text" name="ip_address" value="{{ old('ip_address', $user->ip_address) }}">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="field">
                        <label class="label">IP Division</label>
                        <div class="control">
                            <input class="input" type="text" name="ip_division" value="{{ old('ip_division', $user->ip_division) }}">
                        </div>
                    </div>
                    <div class="field">
                        <label class="label">IP District</label>
                        <div class="control">
                            <input class="input" type="text" name="ip_district" value="{{ old('ip_district', $user->ip_district) }}">
                        </div>
                    </div>
                    <div class="field">
                        <label class="label">IP Thana</label>
                        <div class="control">
                            <input class="input" type="text" name="ip_thana" value="{{ old('ip_thana', $user->ip_thana) }}">
                        </div>
                    </div>
                    <div class="field">
                        <label class="label">IP Postcode</label>
                        <div class="control">
                            <input class="input" type="text" name="ip_postcode" value="{{ old('ip_postcode', $user->ip_postcode) }}">
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="field">
                        <label class="label">GPS Latitude</label>
                        <div class="control">
                            <input class="input" type="number" step="0.0000001" name="gps_lat" value="{{ old('gps_lat', $user->gps_lat) }}">
                        </div>
                    </div>
                    <div class="field">
                        <label class="label">GPS Longitude</label>
                        <div class="control">
                            <input class="input" type="number" step="0.0000001" name="gps_lng" value="{{ old('gps_lng', $user->gps_lng) }}">
                        </div>
                    </div>
                </div>

                <div class="field">
                    <label class="label">GPS Address</label>
                    <div class="control">
                        <textarea class="textarea" name="gps_address">{{ old('gps_address', $user->gps_address) }}</textarea>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="field">
                        <label class="label">Saved Division</label>
                        <div class="control">
                            <input class="input" type="text" name="saved_division" value="{{ old('saved_division', $user->saved_division) }}">
                        </div>
                    </div>
                    <div class="field">
                        <label class="label">Saved District</label>
                        <div class="control">
                            <input class="input" type="text" name="saved_district" value="{{ old('saved_district', $user->saved_district) }}">
                        </div>
                    </div>
                    <div class="field">
                        <label class="label">Saved Thana</label>
                        <div class="control">
                            <input class="input" type="text" name="saved_thana" value="{{ old('saved_thana', $user->saved_thana) }}">
                        </div>
                    </div>
                    <div class="field">
                        <label class="label">Saved Postcode</label>
                        <div class="control">
                            <input class="input" type="text" name="saved_postcode" value="{{ old('saved_postcode', $user->saved_postcode) }}">
                        </div>
                    </div>
                </div>

                <div class="field">
                    <label class="label">Saved Address</label>
                    <div class="control">
                        <textarea class="textarea" name="saved_address">{{ old('saved_address', $user->saved_address) }}</textarea>
                    </div>
                </div>

                <hr>
                <div class="field grouped">
                    <div class="control">
                        <button type="submit" class="button green">Update</button>
                    </div>
                    <div class="control">
                        <a href="{{ route('admin.users.index') }}" class="button red">Cancel</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</section>
@endsection
