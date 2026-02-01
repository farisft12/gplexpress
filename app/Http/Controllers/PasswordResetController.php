<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PasswordResetController extends Controller
{
    /**
     * Show forgot password form
     */
    public function showForgotPasswordForm()
    {
        return view('auth.forgot-password');
    }

    /**
     * Send password reset link
     */
    public function sendResetLink(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        // Rate limiting: max 3 attempts per IP per hour
        $key = 'password.reset.' . $request->ip();
        if (RateLimiter::tooManyAttempts($key, 3)) {
            $seconds = RateLimiter::availableIn($key);
            throw ValidationException::withMessages([
                'email' => "Terlalu banyak permintaan reset password. Silakan coba lagi dalam {$seconds} detik.",
            ]);
        }

        // Check if user exists
        $user = User::where('email', $request->email)->first();
        
        if (!$user) {
            // Don't reveal if email exists or not (security best practice)
            RateLimiter::hit($key, 3600);
            return back()->with('success', 'Jika email terdaftar, link reset password telah dikirim.');
        }

        // Check if user has phone for WhatsApp reset
        if ($user->phone) {
            // Send reset code via WhatsApp using Fonnte
            $resetCode = Str::random(6);
            
            // Store reset code in session (or use cache with expiration)
            cache()->put('password_reset_code:' . $user->id, [
                'code' => $resetCode,
                'email' => $user->email,
                'expires_at' => now()->addMinutes(15),
            ], now()->addMinutes(15));

            // Send via Fonnte
            try {
                $fonnteService = app(\App\Services\FonnteService::class);
                if ($fonnteService->isConfigured()) {
                    $message = "Kode reset password Anda: {$resetCode}\n\nKode ini berlaku selama 15 menit.\n\nJika Anda tidak meminta reset password, abaikan pesan ini.";
                    $fonnteService->sendMessage($user->phone, $message);
                }
            } catch (\Exception $e) {
                \Log::error('Failed to send password reset code via WhatsApp: ' . $e->getMessage());
            }

            RateLimiter::hit($key, 3600);
            return redirect()->route('password.reset.code')
                ->with('email', $user->email)
                ->with('success', 'Kode reset password telah dikirim ke WhatsApp Anda.');
        }

        // Fallback to email if no phone
        $status = Password::sendResetLink(
            $request->only('email')
        );

        RateLimiter::hit($key, 3600);

        if ($status === Password::RESET_LINK_SENT) {
            return back()->with('success', 'Link reset password telah dikirim ke email Anda.');
        }

        return back()->withErrors(['email' => 'Gagal mengirim link reset password.']);
    }

    /**
     * Show reset code form (for WhatsApp reset)
     */
    public function showResetCodeForm()
    {
        if (!session('email')) {
            return redirect()->route('password.forgot');
        }

        return view('auth.reset-code');
    }

    /**
     * Verify reset code
     */
    public function verifyResetCode(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
            'code' => ['required', 'string', 'size:6'],
        ]);

        $user = User::where('email', $request->email)->first();
        
        if (!$user) {
            return back()->withErrors(['code' => 'Email tidak ditemukan.']);
        }

        $cached = cache()->get('password_reset_code:' . $user->id);
        
        if (!$cached || $cached['code'] !== $request->code) {
            return back()->withErrors(['code' => 'Kode reset password tidak valid atau telah kedaluwarsa.']);
        }

        if (now()->isAfter($cached['expires_at'])) {
            cache()->forget('password_reset_code:' . $user->id);
            return back()->withErrors(['code' => 'Kode reset password telah kedaluwarsa.']);
        }

        // Store token in session for password reset form
        session(['password_reset_token' => Str::random(60), 'password_reset_user_id' => $user->id]);
        cache()->forget('password_reset_code:' . $user->id);

        return redirect()->route('password.reset');
    }

    /**
     * Show reset password form
     */
    public function showResetForm(Request $request)
    {
        // Check if we have a token from email reset or session token
        $token = $request->route('token');
        $sessionToken = session('password_reset_token');

        if (!$token && !$sessionToken) {
            return redirect()->route('password.forgot')
                ->withErrors(['error' => 'Token reset password tidak valid atau telah kedaluwarsa.']);
        }

        return view('auth.reset-password', [
            'token' => $token ?? $sessionToken,
            'email' => $request->email ?? session('password_reset_email') ?? old('email'),
        ]);
    }

    /**
     * Reset password
     */
    public function reset(Request $request)
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', \Illuminate\Validation\Rules\Password::min(8)
                ->letters()
                ->mixedCase()
                ->numbers()
                ->symbols()],
        ]);

        // Check if it's a session-based reset (from WhatsApp code)
        if (session('password_reset_token') === $request->token && session('password_reset_user_id')) {
            $user = User::find(session('password_reset_user_id'));
            
            if ($user && $user->email === $request->email) {
                $user->password = Hash::make($request->password);
                $user->save();

                session()->forget(['password_reset_token', 'password_reset_user_id', 'password_reset_email']);

                return redirect()->route('login')
                    ->with('success', 'Password berhasil direset. Silakan login dengan password baru.');
            }
        }

        // Fallback to Laravel's password reset
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->password = Hash::make($password);
                $user->save();
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return redirect()->route('login')
                ->with('success', 'Password berhasil direset. Silakan login dengan password baru.');
        }

        return back()->withErrors(['email' => 'Token reset password tidak valid atau telah kedaluwarsa.']);
    }
}
