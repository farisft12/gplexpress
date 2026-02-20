<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar - GPL Express</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-50 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <!-- Logo & Header -->
        <div class="text-center">
            <a href="{{ route('home') }}" class="inline-flex items-center justify-center space-x-3 mb-6">
                <div class="w-12 h-12 bg-gradient-to-br from-[#F4C430] to-[#E6B020] rounded-xl shadow-lg flex items-center justify-center">
                    <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
                <div class="text-left">
                    <h2 class="text-2xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-[#F4C430] to-[#E6B020]">Daftar Akun</h2>
                    <p class="text-sm text-slate-500">Buat akun baru untuk mengakses sistem</p>
                </div>
            </a>
        </div>

        <!-- Register Form -->
        <div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-xl p-8 border border-gray-100">
            <form class="space-y-6" method="POST" action="{{ route('register') }}" x-data="registerForm()">
                @csrf
                
                <!-- Name -->
                <div>
                    <label for="name" class="block text-sm font-medium text-slate-700 mb-2">
                        Nama Lengkap
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                        </div>
                        <input id="name" name="name" type="text" autocomplete="name" required
                            class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-[#F4C430] focus:border-[#F4C430] transition-all bg-white/50"
                            placeholder="Nama Lengkap"
                            value="{{ old('name') }}">
                    </div>
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-medium text-slate-700 mb-2">
                        Email
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"></path>
                            </svg>
                        </div>
                        <input id="email" name="email" type="email" autocomplete="email" required
                            class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-[#F4C430] focus:border-[#F4C430] transition-all bg-white/50"
                            placeholder="nama@email.com"
                            value="{{ old('email') }}">
                    </div>
                    @error('email')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Phone -->
                <div>
                    <label for="phone" class="block text-sm font-medium text-slate-700 mb-2">
                        No. Telepon
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                            </svg>
                        </div>
                        <input id="phone" name="phone" type="tel" autocomplete="tel" required
                            class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-[#F4C430] focus:border-[#F4C430] transition-all bg-white/50"
                            placeholder="081234567890"
                            value="{{ old('phone') }}">
                    </div>
                    <p class="mt-2 text-xs text-slate-500">
                        Kode verifikasi akan dikirim ke WhatsApp Anda setelah submit form.
                    </p>
                    @error('phone')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Password -->
                <div>
                    <label for="password" class="block text-sm font-medium text-slate-700 mb-2">
                        Kata Sandi
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                            </svg>
                        </div>
                        <input id="password" name="password" :type="showPassword ? 'text' : 'password'" autocomplete="new-password" required
                            class="block w-full pl-10 pr-10 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-[#F4C430] focus:border-[#F4C430] transition-all bg-white/50"
                            placeholder="••••••••"
                            x-model="password"
                            @input="checkPasswordStrength">
                        <button type="button" @click="showPassword = !showPassword" 
                            class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 transition-colors">
                            <svg x-show="!showPassword" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                            <svg x-show="showPassword" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display: none;">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path>
                            </svg>
                        </button>
                    </div>
                    
                    <!-- Password Strength Indicator -->
                    <div x-show="password.length > 0">
                        <div class="mt-2 space-y-2">
                            <!-- Strength Bars -->
                            <div class="flex gap-1 h-2.5 rounded-full overflow-hidden bg-gray-100">
                                <div class="flex-1 rounded-full transition-all duration-500 ease-out transform origin-center"
                                     :class="strength >= 1 ? strengthColor : 'bg-gray-200'"
                                     :style="strength >= 1 ? 'transform: scaleY(1); opacity: 1;' : 'transform: scaleY(0.2); opacity: 0.3;'"></div>
                                <div class="flex-1 rounded-full transition-all duration-500 ease-out transform origin-center"
                                     :class="strength >= 2 ? strengthColor : 'bg-gray-200'"
                                     :style="strength >= 2 ? 'transform: scaleY(1); opacity: 1;' : 'transform: scaleY(0.2); opacity: 0.3;'"></div>
                                <div class="flex-1 rounded-full transition-all duration-500 ease-out transform origin-center"
                                     :class="strength >= 3 ? strengthColor : 'bg-gray-200'"
                                     :style="strength >= 3 ? 'transform: scaleY(1); opacity: 1;' : 'transform: scaleY(0.2); opacity: 0.3;'"></div>
                                <div class="flex-1 rounded-full transition-all duration-500 ease-out transform origin-center"
                                     :class="strength >= 4 ? strengthColor : 'bg-gray-200'"
                                     :style="strength >= 4 ? 'transform: scaleY(1); opacity: 1;' : 'transform: scaleY(0.2); opacity: 0.3;'"></div>
                                <div class="flex-1 rounded-full transition-all duration-500 ease-out transform origin-center"
                                     :class="strength >= 5 ? strengthColor : 'bg-gray-200'"
                                     :style="strength >= 5 ? 'transform: scaleY(1); opacity: 1;' : 'transform: scaleY(0.2); opacity: 0.3;'"></div>
                                <div class="flex-1 rounded-full transition-all duration-500 ease-out transform origin-center"
                                     :class="strength >= 6 ? strengthColor : 'bg-gray-200'"
                                     :style="strength >= 6 ? 'transform: scaleY(1); opacity: 1;' : 'transform: scaleY(0.2); opacity: 0.3;'"></div>
                            </div>
                            
                            <!-- Strength Text -->
                            <div class="flex items-center justify-between">
                                <p class="text-xs font-semibold transition-all duration-300"
                                   :class="{
                                       'text-red-600': strength <= 2,
                                       'text-yellow-600': strength > 2 && strength <= 4,
                                       'text-green-600': strength > 4 && strength <= 6,
                                       'text-emerald-700': strength > 6
                                   }"
                                   x-text="strengthText || 'Masukkan password'"></p>
                                <div class="flex items-center space-x-1 text-xs">
                                    <svg class="w-4 h-4 transition-all duration-300" 
                                         :class="hasMinLength ? 'text-green-500 scale-110' : 'text-gray-300'" 
                                         fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                    </svg>
                                    <span :class="hasMinLength ? 'text-green-600 font-medium' : 'text-gray-400'" 
                                          class="transition-all duration-300">8+ karakter</span>
                                </div>
                            </div>
                            
                            <!-- Requirements -->
                            <div class="grid grid-cols-2 gap-2 text-xs">
                                <div class="flex items-center space-x-1.5 transition-all duration-300"
                                     :class="hasLowercase ? 'text-green-600' : 'text-gray-400'">
                                    <svg class="w-3.5 h-3.5 transition-all duration-300" 
                                         :class="hasLowercase ? 'scale-110' : ''"
                                         fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                    </svg>
                                    <span>Huruf kecil</span>
                                </div>
                                <div class="flex items-center space-x-1.5 transition-all duration-300"
                                     :class="hasUppercase ? 'text-green-600' : 'text-gray-400'">
                                    <svg class="w-3.5 h-3.5 transition-all duration-300" 
                                         :class="hasUppercase ? 'scale-110' : ''"
                                         fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                    </svg>
                                    <span>Huruf besar</span>
                                </div>
                                <div class="flex items-center space-x-1.5 transition-all duration-300"
                                     :class="hasNumber ? 'text-green-600' : 'text-gray-400'">
                                    <svg class="w-3.5 h-3.5 transition-all duration-300" 
                                         :class="hasNumber ? 'scale-110' : ''"
                                         fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                    </svg>
                                    <span>Angka</span>
                                </div>
                                <div class="flex items-center space-x-1.5 transition-all duration-300"
                                     :class="hasSpecial ? 'text-green-600' : 'text-gray-400'">
                                    <svg class="w-3.5 h-3.5 transition-all duration-300" 
                                         :class="hasSpecial ? 'scale-110' : ''"
                                         fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                    </svg>
                                    <span>Karakter khusus</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    @error('password')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Password Confirmation -->
                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-slate-700 mb-2">
                        Konfirmasi Kata Sandi
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                            </svg>
                        </div>
                        <input id="password_confirmation" name="password_confirmation" :type="showPasswordConfirmation ? 'text' : 'password'" autocomplete="new-password" required
                            class="block w-full pl-10 pr-10 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-[#F4C430] focus:border-[#F4C430] transition-all bg-white/50"
                            placeholder="••••••••">
                        <button type="button" @click="showPasswordConfirmation = !showPasswordConfirmation" 
                            class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 transition-colors">
                            <svg x-show="!showPasswordConfirmation" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                            <svg x-show="showPasswordConfirmation" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display: none;">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Submit Button -->
                <div>
                    <button type="submit"
                        class="w-full flex justify-center py-3 px-4 border border-transparent rounded-xl shadow-lg text-sm font-semibold text-white bg-gradient-to-r from-[#F4C430] to-[#E6B020] hover:from-[#E6B020] hover:to-[#D4A017] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#F4C430] transition-all duration-300 transform hover:-translate-y-0.5">
                        Daftar
                    </button>
                </div>
            </form>
        </div>

        <!-- Back to Login -->
        <div class="text-center">
            <p class="text-sm text-slate-600">
                Sudah punya akun?
                <a href="{{ route('login') }}" class="font-medium text-[#F4C430] hover:text-[#E6B020] transition-colors">
                    Masuk di sini
                </a>
            </p>
            <a href="{{ route('home') }}" class="mt-2 inline-block text-sm text-slate-600 hover:text-[#F4C430] transition-colors">
                ← Kembali ke Beranda
            </a>
        </div>
    </div>

    <script>
        function registerForm() {
            return {
                password: '',
                showPassword: false,
                showPasswordConfirmation: false,
                strength: 0,
                strengthText: '',
                strengthColor: 'bg-gray-200',
                hasMinLength: false,
                hasLowercase: false,
                hasUppercase: false,
                hasNumber: false,
                hasSpecial: false,
                
                checkPasswordStrength() {
                    const pwd = this.password;
                    let strength = 0;
                    
                    // Check requirements
                    this.hasMinLength = pwd.length >= 8;
                    this.hasLowercase = /[a-z]/.test(pwd);
                    this.hasUppercase = /[A-Z]/.test(pwd);
                    this.hasNumber = /[0-9]/.test(pwd);
                    this.hasSpecial = /[^a-zA-Z0-9]/.test(pwd);
                    
                    // Calculate strength
                    if (this.hasMinLength) strength++;
                    if (pwd.length >= 12) strength++;
                    if (this.hasLowercase) strength++;
                    if (this.hasUppercase) strength++;
                    if (this.hasNumber) strength++;
                    if (this.hasSpecial) strength++;
                    
                    this.strength = strength;
                    
                    // Set strength text and color
                    if (strength <= 2) {
                        this.strengthText = 'Lemah';
                        this.strengthColor = 'bg-red-500';
                    } else if (strength <= 4) {
                        this.strengthText = 'Sedang';
                        this.strengthColor = 'bg-yellow-500';
                    } else if (strength <= 6) {
                        this.strengthText = 'Kuat';
                        this.strengthColor = 'bg-green-500';
                    } else {
                        this.strengthText = 'Sangat Kuat';
                        this.strengthColor = 'bg-emerald-600';
                    }
                }
            }
        }
    </script>
</body>
</html>
