@extends('erp.layouts.app')

@section('title', 'Add Inventory Item')

@section('content')
<div style="padding:24px 0">
    <div style="max-width:800px;margin:0 auto;padding:0 24px">
        <div class="page-header">
            <a href="{{ route('erp.inventory.index') }}" style="color:var(--text-muted);margin-right:16px" data-icon="arrow-left"></a>
            <h1 style="font-size:1.5rem;font-weight:700;color:var(--text-main)">Add Inventory Item</h1>
        </div>

        @if($errors->any())
            <div class="error-message">
                <ul style="margin:0;padding-left:20px">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius-lg);padding:24px">
            <form action="{{ route('erp.inventory.store') }}" method="POST">
                @csrf
                <div class="form-row">
                    <div class="form-group">
                        <label>SKU *</label>
                        <input type="text" name="sku" value="{{ old('sku') }}" required>
                    </div>
                    <div class="form-group">
                        <label>Name *</label>
                        <input type="text" name="name" value="{{ old('name') }}" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Category</label>
                        <input type="text" name="category" value="{{ old('category') }}" placeholder="e.g. Electronics, Accessories">
                    </div>
                    <div class="form-group">
                        <label>Unit</label>
                        <input type="text" name="unit" value="{{ old('unit') }}" placeholder="e.g. pcs, kg, ltr">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Quantity *</label>
                        <input type="number" name="quantity" value="{{ old('quantity', 0) }}" min="0" step="0.01" required>
                    </div>
                    <div class="form-group">
                        <label>Min Stock Level</label>
                        <input type="number" name="min_stock_level" value="{{ old('min_stock_level', 0) }}" min="0">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Purchase Price *</label>
                        <input type="number" name="purchase_price" value="{{ old('purchase_price') }}" min="0" step="0.01" required>
                    </div>
                    <div class="form-group">
                        <label>Selling Price *</label>
                        <input type="number" name="selling_price" value="{{ old('selling_price') }}" min="0" step="0.01" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group" style="flex:1">
                        <label>Vendor</label>
                        <select name="vendor_id">
                            <option value="">Select Vendor</option>
                            @foreach($vendors as $vendor)
                                <option value="{{ $vendor->id }}" {{ old('vendor_id') == $vendor->id ? 'selected' : '' }}>{{ $vendor->company_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group" style="flex:1">
                        <label>Description</label>
                        <textarea name="description" rows="3">{{ old('description') }}</textarea>
                    </div>
                </div>
                <div style="margin-top:24px;display:flex;justify-content:flex-end;gap:12px">
                    <a href="{{ route('erp.inventory.index') }}" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">Create Item</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
