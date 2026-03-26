<div class="field">
    <label class="label">Product</label>
    <div class="control">
        <div class="select">
            <select name="product_id" required>
                <option value="">Select Product</option>
                @foreach ($products as $product)
                <option value="{{ $product->id }}" {{ (string) old('product_id', $commission?->product_id ?? $selectedProductId) === (string) $product->id ? 'selected' : '' }}>
                    {{ $product->name }} - {{ $product->selling_price }}
                </option>
                @endforeach
            </select>
        </div>
    </div>
</div>
<div class="field">
    <label class="label">Commission Type</label>
    <div class="control">
        <div class="select">
            <select name="commission_type" required>
                <option value="fixed" {{ old('commission_type', $commission?->commission_type) === 'fixed' ? 'selected' : '' }}>Fixed</option>
                <option value="percent" {{ old('commission_type', $commission?->commission_type) === 'percent' ? 'selected' : '' }}>Percent</option>
            </select>
        </div>
    </div>
</div>
<div class="field">
    <label class="label">Commission Value</label>
    <div class="control">
        <input class="input" type="number" step="0.01" min="0" name="commission_value" value="{{ old('commission_value', $commission?->commission_value) }}" placeholder="Enter commission value" required>
    </div>
</div>
<div class="field">
    <label class="label">Status</label>
    <div class="control">
        <div class="select">
            <select name="status" required>
                <option value="active" {{ old('status', $commission?->status ?? 'inactive') === 'active' ? 'selected' : '' }}>Active</option>
                <option value="inactive" {{ old('status', $commission?->status ?? 'inactive') === 'inactive' ? 'selected' : '' }}>Inactive</option>
            </select>
        </div>
    </div>
    <p class="help">Same product-er jonno notun active rule save korle purono active rule inactive hoye jabe.</p>
</div>
<hr>
<div class="field grouped">
    <div class="control">
        <button type="submit" class="button green">
            {{ $commission ? 'Update Commission' : 'Create Commission' }}
        </button>
    </div>
    <div class="control">
        <a href="{{ route('admin.product-commissions.index') }}" class="button red">
            Cancel
        </a>
    </div>
</div>
