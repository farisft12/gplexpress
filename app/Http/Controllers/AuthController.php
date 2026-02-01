<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\AuditLog;
use App\Models\PhoneVerification;
use App\Services\FonnteService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Show login form
     */
    public function showLoginForm()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        
        return view('auth.login');
    }

    /**
     * Handle login request
     */
    public function login(Request $request)
    {
        // Rate limiting
        $key = 'login.' . $request->ip();
        
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            throw ValidationException::withMessages([
                'email' => "Terlalu banyak percobaan login. Silakan coba lagi dalam {$seconds} detik.",
            ]);
        }

        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            
            RateLimiter::clear($key);
            
            $user = Auth::user();
            
            // Check if user is active
            if (!$user->isActive()) {
                // Audit log: Failed login (inactive account)
                AuditLog::log('login_failed', 'security', null, null, null, null, [
                    'reason' => 'inactive_account',
                    'email' => $credentials['email'],
                ], 'Login attempt with inactive account');
                
                Log::channel('security')->warning('Login attempt with inactive account', [
                    'email' => $credentials['email'],
                    'ip' => $request->ip(),
                ]);
                
                Auth::logout();
                return back()->withErrors([
                    'email' => 'Akun Anda tidak aktif. Silakan hubungi administrator.',
                ]);
            }
            
            // Audit log: Successful login
            AuditLog::log('login', 'security', $user->id, null, null, null, [
                'email' => $user->email,
                'role' => $user->role,
            ], 'User logged in successfully');
            
            Log::channel('security')->info('User logged in', [
                'user_id' => $user->id,
                'email' => $user->email,
                'ip' => $request->ip(),
            ]);
            
            return redirect()->intended(route('dashboard'));
        }
        
        // Audit log: Failed login (invalid credentials)
        AuditLog::log('login_failed', 'security', null, null, null, null, [
            'reason' => 'invalid_credentials',
            'email' => $credentials['email'],
        ], 'Login attempt with invalid credentials');
        
        Log::channel('security')->warning('Failed login attempt', [
            'email' => $credentials['email'],
            'ip' => $request->ip(),
        ]);

        RateLimiter::hit($key);

        throw ValidationException::withMessages([
            'email' => 'Email atau password tidak valid.',
        ]);
    }

    /**
     * Show registration form
     */
    public function showRegisterForm()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        
        return view('auth.register');
    }

    /**
     * Handle registration request
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'phone' => ['required', 'string', 'regex:/^(\+62|62|0)[0-9]{9,13}$/', 'unique:users,phone'],
            'password' => ['required', 'confirmed', Password::min(8)
                ->letters()
                ->mixedCase()
                ->numbers()
                ->symbols()],
        ]);

        $phone = $this->normalizePhone($validated['phone']);

        // Check if phone already exists
        if (User::where('phone', $phone)->exists()) {
            return back()->withErrors([
                'phone' => 'Nomor WhatsApp ini sudah terdaftar.',
            ])->withInput();
        }

        // Store registration data in session
        $request->session()->put('registration_data', [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $phone,
            'password' => $validated['password'],
        ]);

        // Send verification code
        try {
            $verification = PhoneVerification::createVerification($phone, $request->ip());

            // Send via Fonnte
            $fonnteService = app(FonnteService::class);
            if (!$fonnteService->isConfigured()) {
                return back()->withErrors([
                    'phone' => 'Layanan verifikasi WhatsApp sedang tidak tersedia. Silakan hubungi administrator.',
                ])->withInput();
            }

            $message = "Kode verifikasi GPL Expres Anda: {$verification->code}\n\nKode ini berlaku selama 10 menit.\n\nJangan bagikan kode ini kepada siapapun.";
            $result = $fonnteService->sendMessage($phone, $message);

            if (!$result['success']) {
                return back()->withErrors([
                    'phone' => 'Gagal mengirim kode verifikasi. Silakan coba lagi.',
                ])->withInput();
            }

            Log::info('Phone verification code sent for registration', [
                'phone' => $phone,
                'ip' => $request->ip(),
            ]);

            // Redirect to verification page
            return redirect()->route('register.verify')->with('success', 'Kode verifikasi telah dikirim ke WhatsApp Anda.');
        } catch (\Exception $e) {
            Log::error('Phone verification error during registration', [
                'phone' => $phone,
                'error' => $e->getMessage(),
            ]);

            return back()->withErrors([
                'phone' => 'Terjadi kesalahan saat mengirim kode verifikasi. Silakan coba lagi.',
            ])->withInput();
        }
    }

    /**
     * Show phone verification form
     */
    public function showVerifyForm(Request $request)
    {
        // Check if registration data exists in session
        if (!$request->session()->has('registration_data')) {
            return redirect()->route('register')->withErrors([
                'error' => 'Sesi registrasi telah kedaluwarsa. Silakan daftar kembali.',
            ]);
        }

        $registrationData = $request->session()->get('registration_data');
        $phone = $registrationData['phone'];
        
        // Mask phone number for display
        $maskedPhone = substr($phone, 0, 4) . '****' . substr($phone, -4);

        return view('auth.verify', compact('phone', 'maskedPhone'));
    }

    /**
     * Verify phone and complete registration
     */
    public function verify(Request $request)
    {
        // Check if registration data exists in session
        if (!$request->session()->has('registration_data')) {
            return redirect()->route('register')->withErrors([
                'error' => 'Sesi registrasi telah kedaluwarsa. Silakan daftar kembali.',
            ]);
        }

        $validated = $request->validate([
            'verification_code' => ['required', 'string', 'size:6'],
        ]);

        $registrationData = $request->session()->get('registration_data');
        $phone = $registrationData['phone'];

        // Verify phone number
        if (!PhoneVerification::verify($phone, $validated['verification_code'])) {
            return back()->withErrors([
                'verification_code' => 'Kode verifikasi tidak valid atau sudah kedaluwarsa.',
            ]);
        }

        // Use transaction to ensure data integrity
        $user = DB::transaction(function () use ($registrationData, $phone, $request) {
            $user = User::create([
                'name' => $registrationData['name'],
                'email' => $registrationData['email'],
                'phone' => $phone,
                'password' => Hash::make($registrationData['password']),
            'role' => 'user', // Default role, bisa diubah di database
            'status' => 'active',
        ]);

            // Audit log
            AuditLog::log('user_registered', 'security', $user->id, null, null, null, [
                'email' => $user->email,
                'phone' => $user->phone,
            ], 'User registered successfully');

            return $user;
        });

        // Clear registration data from session
        $request->session()->forget('registration_data');

        Log::channel('security')->info('User registered', [
            'user_id' => $user->id,
            'email' => $user->email,
            'phone' => $user->phone,
            'ip' => $request->ip(),
        ]);

        // Redirect to login with success message
        return redirect()->route('login')->with('success', 'Registrasi berhasil! Silakan login dengan email dan password Anda.');
    }

    /**
     * Resend verification code
     */
    public function resendVerificationCode(Request $request)
    {
        // Check if registration data exists in session
        if (!$request->session()->has('registration_data')) {
            return response()->json([
                'success' => false,
                'message' => 'Sesi registrasi telah kedaluwarsa. Silakan daftar kembali.',
            ], 400);
        }

        $registrationData = $request->session()->get('registration_data');
        $phone = $registrationData['phone'];

        // Rate limiting: max 3 attempts per phone per hour
        $key = 'phone_verification:' . $phone;
        if (RateLimiter::tooManyAttempts($key, 3)) {
            $seconds = RateLimiter::availableIn($key);
            return response()->json([
                'success' => false,
                'message' => "Terlalu banyak percobaan. Silakan coba lagi dalam {$seconds} detik.",
            ], 429);
        }

        try {
            // Create verification record
            $verification = PhoneVerification::createVerification($phone, $request->ip());

            // Send via Fonnte
            $fonnteService = app(FonnteService::class);
            if (!$fonnteService->isConfigured()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Layanan verifikasi WhatsApp sedang tidak tersedia. Silakan hubungi administrator.',
                ], 503);
            }

            $message = "Kode verifikasi GPL Expres Anda: {$verification->code}\n\nKode ini berlaku selama 10 menit.\n\nJangan bagikan kode ini kepada siapapun.";
            $result = $fonnteService->sendMessage($phone, $message);

            if ($result['success']) {
                RateLimiter::hit($key, 3600); // 1 hour window
                
                Log::info('Phone verification code resent', [
                    'phone' => $phone,
                    'ip' => $request->ip(),
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Kode verifikasi telah dikirim ulang ke WhatsApp Anda.',
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal mengirim kode verifikasi: ' . ($result['message'] ?? 'Unknown error'),
                ], 500);
            }
        } catch (\Exception $e) {
            Log::error('Phone verification resend error', [
                'phone' => $phone,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengirim kode verifikasi.',
            ], 500);
        }
    }

    /**
     * Send phone verification code
     */
    public function sendVerificationCode(Request $request)
    {
        $validated = $request->validate([
            'phone' => ['required', 'string', 'regex:/^(\+62|62|0)[0-9]{9,13}$/'],
        ]);

        $phone = $this->normalizePhone($validated['phone']);

        // Check if phone already exists
        if (User::where('phone', $phone)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Nomor WhatsApp ini sudah terdaftar.',
            ], 422);
        }

        // Rate limiting: max 3 attempts per phone per hour
        $key = 'phone_verification:' . $phone;
        if (RateLimiter::tooManyAttempts($key, 3)) {
            $seconds = RateLimiter::availableIn($key);
            return response()->json([
                'success' => false,
                'message' => "Terlalu banyak percobaan. Silakan coba lagi dalam {$seconds} detik.",
            ], 429);
        }

        try {
            // Create verification record
            $verification = PhoneVerification::createVerification($phone, $request->ip());

            // Send via Fonnte
            $fonnteService = app(FonnteService::class);
            if (!$fonnteService->isConfigured()) {
                Log::error('Fonnte not configured for phone verification');
                return response()->json([
                    'success' => false,
                    'message' => 'Layanan verifikasi WhatsApp sedang tidak tersedia. Silakan hubungi administrator.',
                ], 503);
            }

            $message = "Kode verifikasi GPL Expres Anda: {$verification->code}\n\nKode ini berlaku selama 10 menit.\n\nJangan bagikan kode ini kepada siapapun.";
            $result = $fonnteService->sendMessage($phone, $message);

            if ($result['success']) {
                RateLimiter::hit($key, 3600); // 1 hour window
                
                Log::info('Phone verification code sent', [
                    'phone' => $phone,
                    'ip' => $request->ip(),
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Kode verifikasi telah dikirim ke WhatsApp Anda.',
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal mengirim kode verifikasi: ' . ($result['message'] ?? 'Unknown error'),
                ], 500);
            }
        } catch (\Exception $e) {
            Log::error('Phone verification error', [
                'phone' => $phone,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengirim kode verifikasi.',
            ], 500);
        }
    }

    /**
     * Normalize phone number to standard format (62xxxxxxxxxx)
     */
    protected function normalizePhone(string $phone): string
    {
        // Remove all non-numeric characters
        $phone = preg_replace('/[^0-9]/', '', $phone);

        // If starts with 0, replace with 62 (Indonesia country code)
        if (substr($phone, 0, 1) === '0') {
            $phone = '62' . substr($phone, 1);
        }

        // If doesn't start with country code, add 62
        if (substr($phone, 0, 2) !== '62') {
            $phone = '62' . $phone;
        }

        return $phone;
    }

    /**
     * Handle logout request
     */
    public function logout(Request $request)
    {
        $user = Auth::user();
        
        // Audit log: Logout
        if ($user) {
            AuditLog::log('logout', 'security', $user->id, null, null, null, [
                'email' => $user->email,
            ], 'User logged out');
            
            Log::channel('security')->info('User logged out', [
                'user_id' => $user->id,
                'email' => $user->email,
                'ip' => $request->ip(),
            ]);
        }
        
        Auth::logout();
        
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect()->route('login');
    }
}

