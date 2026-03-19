@extends('layouts.admin_main')

@section('content')
<section class="section main-section">
    <div class="card">
        <header class="card-header">
            <p class="card-header-title">
                <span class="icon"><i class="mdi mdi-truck-delivery"></i></span>
                Delivery Charge Settings
            </p>
        </header>
        <div class="card-content">
            <form method="POST" action="{{ route('admin.delivery-charge.update') }}">
                @csrf
                <div class="field">
                    <label class="label">Inside Dhaka Delivery Charge</label>
                    <div class="control">
                        <input class="input" type="number" step="0.01" min="0" name="inside_dhaka_delivery_charge" value="{{ old('inside_dhaka_delivery_charge', $setting->inside_dhaka_delivery_charge) }}" required>
                    </div>
                </div>
                <div class="field">
                    <label class="label">Out of Dhaka Delivery Charge</label>
                    <div class="control">
                        <input class="input" type="number" step="0.01" min="0" name="outside_dhaka_delivery_charge" value="{{ old('outside_dhaka_delivery_charge', $setting->outside_dhaka_delivery_charge) }}" required>
                    </div>
                </div>
                <div class="field">
                    <label class="label">Custom Delivery Charge</label>
                    <div class="control">
                        <input class="input" type="number" step="0.01" min="0" name="custom_delivery_charge" value="{{ old('custom_delivery_charge', $setting->custom_delivery_charge) }}" required>
                    </div>
                </div>
                <div class="field">
                    <label class="label">Koto takar order korle delivery charge free</label>
                    <div class="control">
                        <input class="input" type="number" step="0.01" min="0" name="free_delivery_min_order_amount" value="{{ old('free_delivery_min_order_amount', $setting->free_delivery_min_order_amount) }}" required>
                    </div>
                </div>
                <hr>
                <div class="field grouped">
                    <div class="control">
                        <button type="submit" class="button green">
                            Update Charges
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</section>
@endsection
