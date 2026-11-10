<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - Demo ERP</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="{{ asset('css/style.css') }}" rel="stylesheet">
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <h1>Forgot Password</h1>
                <p>Enter your email to receive a verification code</p>
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

            <form class="login-form" method="POST" action="{{ route('erp.password.forgot.post') }}">
                @csrf
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" placeholder="admin@yourstechhub.com" required autofocus>
                </div>
                <button type="submit" class="login-btn">Send Verification Code</button>
            </form>

            <div class="login-footer">
                <a href="{{ route('erp.login') }}" style="font-size:.9375rem; color:var(--text-muted); font-weight:600; text-decoration:none;">Back to Login</a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
