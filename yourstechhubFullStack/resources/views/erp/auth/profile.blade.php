@extends('erp.layouts.app')

@section('title', 'Profile')

@section('content')
<div style="max-width:800px;margin:0 auto">
    <div class="page-header">
        <div>
            <h2>Profile</h2>
            <p style="color:var(--text-muted);font-size:0.875rem;margin-top:4px">Manage your account settings</p>
        </div>
    </div>

    <!-- Profile Information -->
    <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius-lg);margin-bottom:24px">
        <div style="padding:20px 24px;border-bottom:1px solid var(--border)">
            <h3 style="font-size:1.125rem;font-weight:700;color:var(--text-main)">Personal Information</h3>
        </div>
        <div style="padding:24px">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <form method="POST" action="{{ route('erp.profile.update') }}" style="display:flex;flex-direction:column;gap:20px">
                @csrf
                @method('PUT')

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
                    <div class="form-group">
                        <label>Full name</label>
                        <input type="text" name="name" value="{{ old('name', auth()->user()->name) }}" required @error('name') style="border-color:var(--danger)" @enderror>
                        @error('name')
                            <span class="error-message" style="text-align:left">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label>Email address</label>
                        <input type="email" name="email" value="{{ old('email', auth()->user()->email) }}" required @error('email') style="border-color:var(--danger)" @enderror>
                        @error('email')
                            <span class="error-message" style="text-align:left">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label>Phone number</label>
                        <input type="text" name="phone" value="{{ old('phone', auth()->user()->phone ?? '') }}" placeholder="+92 300 1234567" @error('phone') style="border-color:var(--danger)" @enderror>
                        @error('phone')
                            <span class="error-message" style="text-align:left">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label>Role</label>
                        <input type="text" readonly value="{{ auth()->user()->role->name ?? 'N/A' }}" style="background:var(--bg-main);color:var(--text-muted);cursor:not-allowed">
                    </div>
                </div>

                <div style="display:flex;justify-content:flex-end">
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Change Password -->
    <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius-lg)">
        <div style="padding:20px 24px;border-bottom:1px solid var(--border)">
            <h3 style="font-size:1.125rem;font-weight:700;color:var(--text-main)">Change Password</h3>
        </div>
        <div style="padding:24px">
            @if(session('password_success'))
                <div class="alert alert-success">{{ session('password_success') }}</div>
            @endif

            <form method="POST" action="{{ route('erp.password.change') }}" style="display:flex;flex-direction:column;gap:20px">
                @csrf

                <div class="form-group">
                    <label>Current password</label>
                    <div style="position:relative">
                        <input type="password" id="current_password" name="current_password" required placeholder="Enter current password" @error('current_password') style="border-color:var(--danger);padding-right:48px" @enderror style="padding-right:48px">
                        <button type="button" onclick="togglePassword('current_password', this)" style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;color:var(--text-muted);cursor:pointer;padding:0">
                            <span data-icon="eye"></span>
                        </button>
                    </div>
                    @error('current_password')
                        <span class="error-message" style="text-align:left">{{ $message }}</span>
                    @enderror
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
                    <div class="form-group">
                        <label>New password</label>
                        <div style="position:relative">
                            <input type="password" id="new_password" name="new_password" required placeholder="Enter new password" @error('new_password') style="border-color:var(--danger);padding-right:48px" @enderror style="padding-right:48px">
                            <button type="button" onclick="togglePassword('new_password', this)" style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;color:var(--text-muted);cursor:pointer;padding:0">
                                <span data-icon="eye"></span>
                            </button>
                        </div>
                        @error('new_password')
                            <span class="error-message" style="text-align:left">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label>Confirm new password</label>
                        <div style="position:relative">
                            <input type="password" id="new_password_confirmation" name="new_password_confirmation" required placeholder="Confirm new password" style="padding-right:48px">
                            <button type="button" onclick="togglePassword('new_password_confirmation', this)" style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;color:var(--text-muted);cursor:pointer;padding:0">
                                <span data-icon="eye"></span>
                            </button>
                        </div>
                    </div>
                </div>

                <div style="display:flex;justify-content:flex-end">
                    <button type="submit" class="btn btn-primary">Update Password</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function togglePassword(inputId, btn) {
    var input = document.getElementById(inputId);
    if (!input) return;
    var isPassword = input.type === 'password';
    input.type = isPassword ? 'text' : 'password';
    var iconSpan = btn.querySelector('span[data-icon]');
    if (iconSpan) {
        iconSpan.setAttribute('data-icon', isPassword ? 'eye-off' : 'eye');
        injectIcons(iconSpan);
    }
}
</script>
@endpush
