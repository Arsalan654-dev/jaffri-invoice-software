@extends('erp.layouts.app')

@section('title', 'Create User')

@section('content')
<div style="max-width:640px;margin:0 auto">
    <div class="page-header">
        <h2>Create User</h2>
    </div>

    <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius-lg);padding:24px">
        <form action="{{ route('erp.admin.users.store') }}" method="POST">
            @csrf

            <div style="display:flex;flex-direction:column;gap:20px">
                <div class="form-group">
                    <label>Name *</label>
                    <input type="text" name="name" value="{{ old('name') }}" required @error('name') style="border-color:var(--danger)" @enderror>
                    @error('name')
                        <span class="error-message" style="text-align:left">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label>Email *</label>
                    <input type="email" name="email" value="{{ old('email') }}" required @error('email') style="border-color:var(--danger)" @enderror>
                    @error('email')
                        <span class="error-message" style="text-align:left">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label>Phone</label>
                    <input type="text" name="phone" value="{{ old('phone') }}" @error('phone') style="border-color:var(--danger)" @enderror>
                    @error('phone')
                        <span class="error-message" style="text-align:left">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label>Password *</label>
                    <input type="password" name="password" required @error('password') style="border-color:var(--danger)" @enderror>
                    @error('password')
                        <span class="error-message" style="text-align:left">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group" style="margin-top:16px;">
                    <label>Confirm Password *</label>
                    <input type="password" name="password_confirmation" required>
                </div>

                <div class="form-group">
                    <label>Role *</label>
                    <select name="role" required @error('role') style="border-color:var(--danger)" @enderror>
                        <option value="">Select Role</option>
                        @foreach($roles as $role)
                            <option value="{{ $role->slug }}" {{ old('role') == $role->slug ? 'selected' : '' }}>
                                {{ $role->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('role')
                        <span class="error-message" style="text-align:left">{{ $message }}</span>
                    @enderror
                </div>

                <div style="display:flex;align-items:center;gap:8px">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', 1) ? 'checked' : '' }}>
                    <label style="text-transform:none;font-size:0.875rem;color:var(--text-main)">Active</label>
                </div>
            </div>

            <div class="modal-actions" style="margin-top:24px">
                <a href="{{ route('erp.admin.users.index') }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">Create User</button>
            </div>
        </form>
    </div>
</div>
@endsection
