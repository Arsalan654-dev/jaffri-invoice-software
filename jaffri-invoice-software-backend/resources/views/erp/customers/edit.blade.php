@extends('erp.layouts.app')

@section('title', 'Edit Customer')

@section('content')
<div style="padding:24px 0">
    <div style="max-width:800px;margin:0 auto;padding:0 24px">
        <div class="page-header">
            <a href="{{ route('erp.customers.index') }}" style="color:var(--text-muted);margin-right:16px" data-icon="arrow-left"></a>
            <h1 style="font-size:1.5rem;font-weight:700;color:var(--text-main)">Edit Customer: {{ $customer->company_name }}</h1>
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
            <form action="{{ route('erp.customers.update', $customer) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="form-row">
                    <div class="form-group">
                        <label>Company Name *</label>
                        <input type="text" name="company_name" value="{{ old('company_name', $customer->company_name) }}" required>
                    </div>
                    <div class="form-group">
                        <label>Contact Person</label>
                        <input type="text" name="contact_person" value="{{ old('contact_person', $customer->contact_person) }}">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Email *</label>
                        <input type="email" name="email" value="{{ old('email', $customer->email) }}" required>
                    </div>
                    <div class="form-group">
                        <label>Phone</label>
                        <input type="text" name="phone" value="{{ old('phone', $customer->phone) }}">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group" style="flex:1">
                        <label>Address</label>
                        <textarea name="address" rows="2">{{ old('address', $customer->address) }}</textarea>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>City</label>
                        <input type="text" name="city" value="{{ old('city', $customer->city) }}">
                    </div>
                    <div class="form-group">
                        <label>Country</label>
                        <input type="text" name="country" value="{{ old('country', $customer->country) }}">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Tax Number</label>
                        <input type="text" name="tax_number" value="{{ old('tax_number', $customer->tax_number) }}">
                    </div>
                    <div class="form-group">
                        <label>Payment Terms</label>
                        <input type="text" name="payment_terms" value="{{ old('payment_terms', $customer->payment_terms) }}" placeholder="e.g. Net 30">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Notes</label>
                        <textarea name="notes" rows="3">{{ old('notes', $customer->notes) }}</textarea>
                    </div>
                    <div class="form-group">
                        <label>Login Password</label>
                        <input type="password" name="password" value="" minlength="8" placeholder="Leave blank to keep current">
                        <input type="password" name="password_confirmation" value="" minlength="8" placeholder="Confirm password" style="margin-top:8px">
                        <span style="font-size:0.75rem;color:var(--text-muted)">Set a new password for the customer's login user.</span>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Role <span style="font-weight:400;text-transform:none;color:var(--text-muted)">(identity only - permissions are set per user)</span></label>
                        <select name="role">
                            <option value="">No role (simple user)</option>
                            @foreach(($roles ?? []) as $role)
                                <option value="{{ $role->slug }}" {{ old('role', $customer->user?->roles->first()?->slug) == $role->slug ? 'selected' : '' }}>{{ $role->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div style="margin-top:24px;display:flex;justify-content:flex-end;gap:12px">
                    <a href="{{ route('erp.customers.index') }}" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">Update Customer</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
