<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify OTP - Jaffri Invoice Software</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="{{ asset('css/style.css') }}" rel="stylesheet">
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <h1>Verify Code</h1>
                <p>Enter the 6-digit code sent to your email</p>
            </div>

            @if($errors->any())
                <div class="error-message" style="display:block">
                    {{ $errors->first() }}
                </div>
            @endif

            <form class="login-form" method="POST" action="{{ route('erp.password.verify-otp.post') }}">
                @csrf
                <input type="hidden" name="email" value="{{ session('otp_email', old('email', '')) }}">
                <div class="form-group">
                    <label for="otp">Verification Code</label>
                    <input type="text" id="otp" name="otp" placeholder="000000" required autofocus maxlength="6" pattern="[0-9]{6}" style="text-align:center; font-size:1.5rem; letter-spacing:8px;">
                </div>
                <button type="submit" class="login-btn">Verify Code</button>
            </form>

            <div class="login-footer">
                <a href="{{ route('erp.password.forgot') }}" style="font-size:.9375rem; color:var(--text-muted); font-weight:600; text-decoration:none;">Resend Code</a>
                <a href="{{ route('erp.login') }}" style="font-size:.9375rem; color:var(--text-muted); font-weight:600; text-decoration:none;">Back to Login</a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
