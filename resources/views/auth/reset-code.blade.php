<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Kode Reset - GPL Express</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="min-h-screen bg-gradient-to-br from-yellow-50 via-amber-50 to-yellow-100 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <!-- Logo & Header -->
        <div class="text-center">
            <a href="{{ route('home') }}" class="inline-flex items-center justify-center space-x-3 mb-6">
                <div class="w-12 h-12 bg-gradient-to-br from-[#F4C430] to-[#E6B020] rounded-xl shadow-lg flex items-center justify-center">
                    <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                    </svg>
                </div>
                <div class="text-left">
                    <h2 class="text-2xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-[#F4C430] to-[#E6B020]">Verifikasi Kode Reset</h2>
                    <p class="text-sm text-slate-500">Masukkan kode yang dikirim ke WhatsApp</p>
                </div>
            </a>
        </div>

        <!-- Reset Code Form -->
        <div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-xl p-8 border border-gray-100">
            @if(session('success'))
                <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-xl">
                    <p class="text-sm text-green-800">{{ session('success') }}</p>
                </div>
            @endif

            @if(session('error'))
                <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-xl">
                    <p class="text-sm text-red-800">{{ session('error') }}</p>
                </div>
            @endif

            <form class="space-y-6" method="POST" action="{{ route('password.verify.code') }}" x-data="{ code: '' }">
                @csrf
                
                <!-- Email (hidden if from session) -->
                <div>
                    <label for="email" class="block text-sm font-medium text-slate-700 mb-2">
                        Email
                    </label>
                    <input id="email" name="email" type="email" required
                        class="block w-full px-3 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-[#F4C430] focus:border-[#F4C430] transition-all bg-white/50"
                        value="{{ session('email') ?? old('email') }}"
                        {{ session('email') ? 'readonly' : '' }}>
                    @error('email')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Verification Code -->
                <div>
                    <label for="code" class="block text-sm font-medium text-slate-700 mb-2">
                        Kode Verifikasi (6 digit)
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                            </svg>
                        </div>
                        <input id="code" 
                               name="code" 
                               type="text" 
                               required
                               maxlength="6"
                               pattern="[0-9]{6}"
                               class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-[#F4C430] focus:border-[#F4C430] transition-all bg-white/50 text-center text-2xl font-mono tracking-widest"
                               placeholder="000000"
                               x-model="code"
                               @input="code = $event.target.value.replace(/[^0-9]/g, '').substring(0, 6)"
                               autofocus>
                    </div>
                    @error('code')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Submit Button -->
                <div>
                    <button type="submit"
                        class="w-full flex justify-center py-3 px-4 border border-transparent rounded-xl shadow-lg text-sm font-semibold text-white bg-gradient-to-r from-[#F4C430] to-[#E6B020] hover:from-[#E6B020] hover:to-[#D4A017] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#F4C430] transition-all duration-300 transform hover:-translate-y-0.5">
                        Verifikasi Kode
                    </button>
                </div>
            </form>
        </div>

        <!-- Back to Forgot Password -->
        <div class="text-center">
            <a href="{{ route('password.forgot') }}" class="text-sm text-slate-600 hover:text-[#F4C430] transition-colors">
                ← Kembali ke Lupa Password
            </a>
        </div>
    </div>
</body>
</html>

