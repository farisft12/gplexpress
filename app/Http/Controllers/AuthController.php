<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\AuditLog;
use App\Models\PhoneVerification;
use App\Services\FonnteService;
use App\Services\User\AuthService;
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
    protected AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }
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
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        try {
            $user = $this->authService->login(
                $credentials,
                $request->boolean('remember'),
                $request->ip()
            );

            $request->session()->regenerate();
            
            return redirect()->intended(route('dashboard'));
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            return back()->withErrors(['email' => $e->getMessage()]);
        }
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

        try {
            $result = $this->authService->registerStep1($validated, $request->ip());

            // Store registration data in session
            $request->session()->put('registration_data', [
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $result['phone'],
                'password' => $validated['password'],
            ]);

            // Redirect to verification page
            return redirect()->route('register.verify')->with('success', 'Kode verifikasi telah dikirim ke WhatsApp Anda.');
        } catch (\Exception $e) {
            return back()->withErrors([
                'phone' => $e->getMessage(),
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
            'verification_code' => ['required', 'string', 'size:6', 'regex:/^[0-9]{6}$/'],
        ]);

        $registrationData = $request->session()->get('registration_data');

        try {
            $user = $this->authService->registerStep2(
                $registrationData,
                $validated['verification_code'],
                $request->ip()
            );

            // Clear registration data from session
            $request->session()->forget('registration_data');

            // Redirect to login with success message
            return redirect()->route('login')->with('success', 'Registrasi berhasil! Silakan login dengan email dan password Anda.');
        } catch (\Exception $e) {
            return back()->withErrors([
                'verification_code' => $e->getMessage(),
            ]);
        }
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

        try {
            $result = $this->authService->resendVerificationCode($phone, $request->ip());
            
            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
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

        try {
            $phone = $this->authService->normalizePhone($validated['phone']);
            $result = $this->authService->sendVerificationCode($phone, $request->ip(), true);
            
            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }


    /**
     * Handle logout request
     */
    public function logout(Request $request)
    {
        $user = Auth::user();
        
        if ($user) {
            $this->authService->logout($user);
        }

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}

