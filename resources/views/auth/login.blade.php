<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - GPL Expres</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
 <body class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-50 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8" x-data="{ showPassword: false }">
    
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
                    <h2 class="text-2xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-[#F4C430] to-[#E6B020]">Masuk</h2>
                    <p class="text-sm text-slate-500">Masuk ke akun Anda</p>
                </div>
            </a>
        </div>

        <!-- Login Form -->
        <div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-xl p-8 border border-gray-100">
            @if(session('success'))
                <div class="mb-6 p-4 bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-800 rounded-xl">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-green-800 dark:text-green-300">{{ session('success') }}</p>
                        </div>
                    </div>
                </div>
            @endif

            @if(session('error'))
                <div class="mb-6 p-4 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 rounded-xl">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-red-800 dark:text-red-300">{{ session('error') }}</p>
                        </div>
                    </div>
                </div>
            @endif

            <form class="space-y-6" method="POST" action="{{ route('login') }}">
                @csrf

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
                        <input id="password" name="password" :type="showPassword ? 'text' : 'password'" autocomplete="current-password" required
                            class="block w-full pl-10 pr-10 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-[#F4C430] focus:border-[#F4C430] transition-all bg-white/50"
                            placeholder="••••••••">
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
                    @error('password')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Remember Me & Forgot Password -->
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <input id="remember" name="remember" type="checkbox"
                            class="h-4 w-4 text-[#F4C430] focus:ring-[#F4C430] border-gray-300 rounded">
                        <label for="remember" class="ml-2 block text-sm text-slate-600">
                        Ingat saya
                    </label>
                    </div>
                    <div class="text-sm">
                        <a href="{{ route('password.forgot') }}" class="font-medium text-[#F4C430] hover:text-[#E6B020] transition-colors">
                            Lupa kata sandi?
                        </a>
                    </div>
                </div>

                <!-- Submit Button -->
                <div>
                    <button type="submit"
                        class="w-full flex justify-center py-3 px-4 border border-transparent rounded-xl shadow-lg text-sm font-semibold text-white bg-gradient-to-r from-[#F4C430] to-[#E6B020] hover:from-[#E6B020] hover:to-[#D4A017] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#F4C430] transition-all duration-300 transform hover:-translate-y-0.5">
                    Masuk
                </button>
                </div>
            </form>
        </div>

        <!-- Back to Home -->
        <div class="text-center">
            <p class="text-sm text-slate-600">
                Belum punya akun? 
                <a href="{{ route('register') }}" class="font-medium text-[#F4C430] hover:text-[#E6B020] transition-colors">
                    Daftar
                </a>
            </p>
            <a href="{{ route('home') }}" class="mt-2 inline-block text-sm text-slate-600 hover:text-[#F4C430] transition-colors">
                ← Kembali ke Beranda
            </a>
        </div>
    </div>
</body>
</html>
