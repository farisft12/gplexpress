<?php

namespace App\Services\User;

use App\Models\User;
use App\Models\AuditLog;
use App\Models\PhoneVerification;
use App\Services\FonnteService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AuthService
{
    protected FonnteService $fonnteService;

    public function __construct(FonnteService $fonnteService)
    {
        $this->fonnteService = $fonnteService;
    }

    /**
     * Handle login
     */
    public function login(array $credentials, bool $remember, string $ip): User
    {
        // Rate limiting
        $key = 'login.' . $ip;
        
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            throw ValidationException::withMessages([
                'email' => "Terlalu banyak percobaan login. Silakan coba lagi dalam {$seconds} detik.",
            ]);
        }

        if (!Auth::attempt($credentials, $remember)) {
            // Audit log: Failed login
            AuditLog::log('login_failed', 'security', null, null, null, null, [
                'reason' => 'invalid_credentials',
                'email' => $credentials['email'],
            ], 'Login attempt with invalid credentials');
            
            Log::channel('security')->warning('Failed login attempt', [
                'email' => $credentials['email'],
                'ip' => $ip,
            ]);

            RateLimiter::hit($key);

            throw ValidationException::withMessages([
                'email' => 'Email atau password tidak valid.',
            ]);
        }

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
                'ip' => $ip,
            ]);
            
            Auth::logout();
            
            throw ValidationException::withMessages([
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
            'ip' => $ip,
        ]);

        RateLimiter::clear($key);

        return $user;
    }

    /**
     * Handle logout
     */
    public function logout(User $user): void
    {
        // Audit log
        AuditLog::log('logout', 'security', $user->id, null, null, null, [
            'email' => $user->email,
        ], 'User logged out');

        Auth::logout();
    }

    /**
     * Register new user (step 1: collect data and send verification)
     */
    public function registerStep1(array $data, string $ip): array
    {
        $phone = $this->normalizePhone($data['phone']);

        // Check if phone already exists
        if (User::where('phone', $phone)->exists()) {
            throw new \Exception('Nomor WhatsApp ini sudah terdaftar.');
        }

        // Create verification record
        $verification = PhoneVerification::createVerification($phone, $ip);

        // Send via Fonnte
        if (!$this->fonnteService->isConfigured()) {
            throw new \Exception('Layanan verifikasi WhatsApp sedang tidak tersedia. Silakan hubungi administrator.');
        }

        $message = "Kode verifikasi GPL Express Anda: {$verification->code}\n\nKode ini berlaku selama 10 menit.\n\nJangan bagikan kode ini kepada siapapun.";
        $result = $this->fonnteService->sendMessage($phone, $message);

        if (!$result['success']) {
            throw new \Exception('Gagal mengirim kode verifikasi. Silakan coba lagi.');
        }

        Log::info('Phone verification code sent for registration', [
            'phone' => $phone,
            'ip' => $ip,
        ]);

        return [
            'phone' => $phone,
            'verification_id' => $verification->id,
        ];
    }

    /**
     * Complete registration (step 2: verify code and create user)
     */
    public function registerStep2(array $registrationData, string $verificationCode, string $ip): User
    {
        $phone = $registrationData['phone'];

        // Verify phone number
        if (!PhoneVerification::verify($phone, $verificationCode)) {
            throw new \Exception('Kode verifikasi tidak valid atau sudah kedaluwarsa.');
        }

        // Create user
        $user = DB::transaction(function () use ($registrationData, $phone, $ip) {
            $user = User::create([
                'name' => $registrationData['name'],
                'email' => $registrationData['email'],
                'phone' => $phone,
                'password' => Hash::make($registrationData['password']),
                'role' => 'user',
                'status' => 'active',
            ]);

            // Audit log
            AuditLog::log('user_registered', 'security', $user->id, null, null, null, [
                'email' => $user->email,
                'phone' => $user->phone,
            ], 'User registered successfully');

            return $user;
        });

        Log::channel('security')->info('User registered', [
            'user_id' => $user->id,
            'email' => $user->email,
            'phone' => $user->phone,
            'ip' => $ip,
        ]);

        return $user;
    }

    /**
     * Send verification code (for registration or other purposes)
     */
    public function sendVerificationCode(string $phone, string $ip, bool $checkExisting = true): array
    {
        // Check if phone already exists (for registration)
        if ($checkExisting && User::where('phone', $phone)->exists()) {
            throw new \Exception('Nomor WhatsApp ini sudah terdaftar.');
        }

        // Rate limiting: max 3 attempts per phone per hour
        $key = 'phone_verification:' . $phone;
        if (RateLimiter::tooManyAttempts($key, 3)) {
            $seconds = RateLimiter::availableIn($key);
            throw new \Exception("Terlalu banyak percobaan. Silakan coba lagi dalam {$seconds} detik.");
        }

        // Create verification record
        $verification = PhoneVerification::createVerification($phone, $ip);

        // Send via Fonnte
        if (!$this->fonnteService->isConfigured()) {
            throw new \Exception('Layanan verifikasi WhatsApp sedang tidak tersedia. Silakan hubungi administrator.');
        }

        $message = "Kode verifikasi GPL Express Anda: {$verification->code}\n\nKode ini berlaku selama 10 menit.\n\nJangan bagikan kode ini kepada siapapun.";
        $result = $this->fonnteService->sendMessage($phone, $message);

        if ($result['success']) {
            RateLimiter::hit($key, 3600);
            
            Log::info('Phone verification code sent', [
                'phone' => $phone,
                'ip' => $ip,
            ]);

            return [
                'success' => true,
                'message' => 'Kode verifikasi telah dikirim ke WhatsApp Anda.',
            ];
        }

        throw new \Exception('Gagal mengirim kode verifikasi: ' . ($result['message'] ?? 'Unknown error'));
    }

    /**
     * Resend verification code (alias for sendVerificationCode with checkExisting=false)
     */
    public function resendVerificationCode(string $phone, string $ip): array
    {
        $result = $this->sendVerificationCode($phone, $ip, false);
        $result['message'] = 'Kode verifikasi telah dikirim ulang ke WhatsApp Anda.';
        return $result;
    }

    /**
     * Normalize phone number
     */
    public function normalizePhone(string $phone): string
    {
        // Remove all non-numeric characters
        $phone = preg_replace('/[^0-9]/', '', $phone);

        // If starts with 0, replace with 62
        if (substr($phone, 0, 1) === '0') {
            $phone = '62' . substr($phone, 1);
        }

        // If doesn't start with country code, add 62
        if (substr($phone, 0, 2) !== '62') {
            $phone = '62' . $phone;
        }

        return $phone;
    }
}

