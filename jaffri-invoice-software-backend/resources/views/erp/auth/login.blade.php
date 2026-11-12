<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Demo ERP</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="{{ asset('css/style.css') }}" rel="stylesheet">
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <h1>Demo Company ERP</h1>
                <p>Sign in to your account</p>
            </div>

            @if($errors->any())
                <div class="error-message" style="display:block">
                    {{ $errors->first() }}
                </div>
            @endif

            @if(session('status'))
                <div class="error-message" style="display:block; background: #ecfdf5; border-color: #a7f3d0; color: #065f46;">
                    {{ session('status') }}
                </div>
            @endif

            <form class="login-form" method="POST" action="{{ route('erp.login.post') }}" autocomplete="off">
                @csrf
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" placeholder="admin@yourstechhub.com" required autofocus>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;" required>
                </div>
                <div style="display:flex; align-items:center; justify-content:space-between;">
                    <label style="display:flex; align-items:center; gap:8px; cursor:pointer; font-size:.875rem; color:var(--text-muted);">
                        <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }} style="width:16px; height:16px;">
                        Remember me
                    </label>
                    <a href="{{ route('erp.password.forgot') }}" style="font-size:.875rem; color:var(--primary); font-weight:600; text-decoration:none;">Forgot password?</a>
                </div>
                <button type="submit" class="login-btn">Sign In</button>
            </form>

            <div class="login-footer">
                <div class="demo-credentials">Demo: admin@yourstechhub.com / password</div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
