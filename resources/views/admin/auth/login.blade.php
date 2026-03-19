@extends('layouts.admin_login')

@section('content')

<section class="section main-section">
  <div class="card">
    <header class="card-header">
      <p class="card-header-title">
        <span class="icon"><i class="mdi mdi-lock"></i></span>
        Admin Login
      </p>
    </header>

    <div class="card-content">

      {{-- Error message --}}
      @if ($errors->any())
        <div class="notification is-danger">
          {{ $errors->first() }}
        </div>
      @endif

      <form method="POST" action="{{ route('admin.login.submit') }}">
        @csrf

        <div class="field spaced">
          <label class="label">Email or Phone</label>
          <div class="control icons-left">
            <input class="input"
                   type="text"
                   name="login"
                   value="{{ old('login') }}"
                   placeholder="email or phone"
                   required>
            <span class="icon is-small left">
              <i class="mdi mdi-account"></i>
            </span>
          </div>
        </div>

        <div class="field spaced">
          <label class="label">Password</label>
          <div class="control icons-left">
            <input class="input"
                   type="password"
                   name="password"
                   placeholder="Password"
                   required>
            <span class="icon is-small left">
              <i class="mdi mdi-lock"></i>
            </span>
          </div>
        </div>

        <div class="field spaced">
          <label class="checkbox">
            <input type="checkbox" name="remember" value="1">
            <span class="check"></span>
            <span class="control-label">Remember me</span>
          </label>
        </div>

        <hr>

        <div class="field grouped">
          <div class="control">
            <button type="submit" class="button blue">
              Login
            </button>
          </div>
          <div class="control">
            <a href="{{ url('/') }}" class="button">
              Back
            </a>
          </div>
        </div>

      </form>
    </div>
  </div>
</section>
@endsection
