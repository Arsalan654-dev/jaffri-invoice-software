@extends('erp.layouts.app')

@section('title', 'Edit Vendor')

@section('content')
<div style="padding:24px 0">
    <div style="max-width:800px;margin:0 auto;padding:0 24px">
        <div class="page-header">
            <a href="{{ route('erp.vendors.index') }}" style="color:var(--text-muted);margin-right:16px" data-icon="arrow-left"></a>
            <h1 style="font-size:1.5rem;font-weight:700;color:var(--text-main)">Edit Vendor: {{ $vendor->company_name }}</h1>
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
            <form action="{{ route('erp.vendors.update', $vendor) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="form-row">
                    <div class="form-group">
                        <label>Company Name *</label>
                        <input type="text" name="company_name" value="{{ old('company_name', $vendor->company_name) }}" required>
                    </div>
                    <div class="form-group">
                        <label>Contact Person</label>
                        <input type="text" name="contact_person" value="{{ old('contact_person', $vendor->contact_person) }}">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Email *</label>
                        <input type="email" name="email" value="{{ old('email', $vendor->email) }}" required>
                    </div>
                    <div class="form-group">
                        <label>Phone</label>
                        <input type="text" name="phone" value="{{ old('phone', $vendor->phone) }}">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group" style="flex:1">
                        <label>Address</label>
                        <textarea name="address" rows="2">{{ old('address', $vendor->address) }}</textarea>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>City</label>
                        <input type="text" name="city" value="{{ old('city', $vendor->city) }}">
                    </div>
                    <div class="form-group">
                        <label>Country</label>
                        <input type="text" name="country" value="{{ old('country', $vendor->country) }}">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Tax Number</label>
                        <input type="text" name="tax_number" value="{{ old('tax_number', $vendor->tax_number) }}">
                    </div>
                    <div class="form-group">
                        <label>Payment Terms</label>
                        <input type="text" name="payment_terms" value="{{ old('payment_terms', $vendor->payment_terms) }}" placeholder="e.g. Net 30">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group" style="flex:1">
                        <label>Notes</label>
                        <textarea name="notes" rows="3">{{ old('notes', $vendor->notes) }}</textarea>
                    </div>
                </div>
                <div style="margin-top:24px;display:flex;justify-content:flex-end;gap:12px">
                    <a href="{{ route('erp.vendors.index') }}" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">Update Vendor</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
