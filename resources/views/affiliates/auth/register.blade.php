@extends('affiliates.layouts.auth')

@section('content')
<section class="section main-section">
  <div class="card">
    <header class="card-header">
      <p class="card-header-title">
        <span class="icon"><i class="mdi mdi-account-plus-outline"></i></span>
        Affiliate Registration
      </p>
    </header>

    <div class="card-content">
      @if ($errors->any())
        <div class="notification is-danger">
          {{ $errors->first() }}
        </div>
      @endif

      <form method="POST" action="{{ route('affiliates.register.submit') }}">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div class="field">
            <label class="label">Name</label>
            <div class="control">
              <input class="input" type="text" name="name" placeholder="Full name" required>
            </div>
          </div>
          <div class="field">
            <label class="label">Phone</label>
            <div class="control">
              <input class="input" type="text" name="phone" placeholder="Phone number" required>
            </div>
          </div>
        </div>

        <div class="field">
          <label class="label">Email</label>
          <div class="control">
            <input class="input" type="email" name="email" placeholder="Email address" required>
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div class="field">
            <label class="label">Password</label>
            <div class="control">
              <input class="input" type="password" name="password" placeholder="Password" required>
            </div>
          </div>
          <div class="field">
            <label class="label">Confirm Password</label>
            <div class="control">
              <input class="input" type="password" name="password_confirmation" placeholder="Confirm password" required>
            </div>
          </div>
        </div>

        <div class="field grouped">
          <div class="control">
            <button type="submit" class="button green">Register</button>
          </div>
          <div class="control">
            <a href="{{ route('affiliates.login') }}" class="button">Back to Login</a>
          </div>
        </div>
      </form>
    </div>
  </div>
</section>
@endsection
