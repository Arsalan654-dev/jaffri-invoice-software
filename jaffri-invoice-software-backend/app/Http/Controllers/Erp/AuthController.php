<?php

namespace App\Http\Controllers\Erp;

use App\Http\Controllers\Controller;
use App\Models\LoginAttempt;
use App\Models\SecurityEvent;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('erp.auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $remembered = $request->boolean('remember');

        if (Auth::attempt($credentials, $remembered)) {
            $request->session()->regenerate();

            $user = Auth::user();

            $this->recordLoginAttempt($request, $user->email, true);

            if (! $user->is_active) {
                SecurityEvent::create([
                    'user_id' => $user->id,
                    'event' => 'login_blocked',
                    'ip_address' => $request->ip(),
                    'details' => 'Inactive account attempted login.',
                ]);
                Auth::logout();

                return back()->withErrors(['email' => 'Your account has been deactivated.']);
            }

            SecurityEvent::create([
                'user_id' => $user->id,
                'event' => 'login',
                'ip_address' => $request->ip(),
                'details' => 'Successful login.',
            ]);

            return redirect()->intended(route('erp.dashboard'));
        }

        $this->recordLoginAttempt($request, $request->input('email'), false);

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        $user = Auth::user();

        if ($user) {
            SecurityEvent::create([
                'user_id' => $user->id,
                'event' => 'logout',
                'ip_address' => $request->ip(),
                'details' => 'User logged out.',
            ]);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('erp.login');
    }

    public function showForgotPassword()
    {
        return view('erp.auth.forgot-password');
    }

    public function sendOtp(Request $request)
    {
        $request->validate(['email' => ['required', 'email']]);

        $user = User::where('email', $request->email)->first();

        if (! $user) {
            return back()->withErrors(['email' => 'No account found with that email address.']);
        }

        $otp = Str::random(6);
        // Ensure OTP is numeric
        $otp = str_pad(mt_rand(0, 999999), 6, '0', STR_PAD_LEFT);

        DB::table('password_resets')->updateOrInsert(
            ['email' => $request->email],
            [
                'token' => Hash::make($otp),
                'created_at' => now(),
            ]
        );

        // In production, send email with OTP. For now, store in session and show on page.
        session(['otp_email' => $request->email]);

        $status = config('app.env') === 'local'
            ? 'Verification code sent! (Dev mode: your code is '.$otp.')'
            : 'Verification code sent to your email address.';

        return redirect()->route('erp.password.verify-otp')->with('status', $status);
    }

    public function showVerifyOtp()
    {
        return view('erp.auth.verify-otp');
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
            'otp' => ['required', 'string', 'digits:6'],
        ]);

        $record = DB::table('password_resets')
            ->where('email', $request->email)
            ->where('created_at', '>=', now()->subMinutes(15))
            ->first();

        if (! $record || ! Hash::check($request->otp, $record->token)) {
            return back()->withErrors(['otp' => 'Invalid or expired verification code.']);
        }

        // OTP is valid - allow reset
        $resetToken = Str::random(60);

        DB::table('password_resets')
            ->where('email', $request->email)
            ->update(['token' => $resetToken]);

        return redirect()->route('erp.password.reset', ['token' => $resetToken, 'email' => $request->email]);
    }

    public function showResetPassword(Request $request, string $token)
    {
        return view('erp.auth.reset-password', [
            'token' => $token,
            'email' => $request->query('email', ''),
        ]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $record = DB::table('password_resets')
            ->where('email', $request->email)
            ->where('token', $request->token)
            ->first();

        if (! $record) {
            return back()->withErrors(['email' => 'Invalid reset token.']);
        }

        $user = User::where('email', $request->email)->first();
        if (! $user) {
            return back()->withErrors(['email' => 'User not found.']);
        }

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        DB::table('password_resets')->where('email', $request->email)->delete();

        return redirect()->route('erp.login')->with('status', 'Password reset successfully! You can now login.');
    }

    public function profile()
    {
        return view('erp.auth.profile', ['user' => Auth::user()]);
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'phone' => ['nullable', 'string', 'max:20'],
        ]);

        $user->update($validated);

        return back()->with('success', 'Profile updated successfully.');
    }

    public function showChangePassword()
    {
        return view('erp.auth.profile');
    }

    private function recordLoginAttempt(Request $request, string $email, bool $success): void
    {
        LoginAttempt::create([
            'email' => $email,
            'ip_address' => $request->ip(),
            'success' => $success,
            'user_agent' => $request->userAgent(),
        ]);
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = Auth::user();
        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return back()->with('success', 'Password changed successfully.');
    }
}
