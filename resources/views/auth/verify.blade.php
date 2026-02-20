<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi - GPL Express</title>
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
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="text-left">
                    <h2 class="text-2xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-[#F4C430] to-[#E6B020]">Verifikasi Akun</h2>
                    <p class="text-sm text-slate-500">Masukkan kode verifikasi dari WhatsApp</p>
                </div>
            </a>
        </div>

        <!-- Verification Form -->
        <div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-xl p-8 border border-gray-100">
            @if(session('success'))
                <div class="mb-4 p-4 bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-800 rounded-xl">
                    <p class="text-sm text-green-800 dark:text-green-300">{{ session('success') }}</p>
                </div>
            @endif

            @if(session('error'))
                <div class="mb-4 p-4 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 rounded-xl">
                    <p class="text-sm text-red-800 dark:text-red-300">{{ session('error') }}</p>
                </div>
            @endif

            <div class="mb-6 p-4 bg-yellow-50 dark:bg-yellow-900/30 border border-yellow-200 dark:border-yellow-800 rounded-xl">
                <p class="text-sm text-yellow-800 dark:text-yellow-300">
                    Kode verifikasi telah dikirim ke WhatsApp Anda (<strong>{{ $maskedPhone }}</strong>). 
                    Silakan masukkan 6 digit kode verifikasi untuk melanjutkan.
                </p>
            </div>

            <form class="space-y-6" method="POST" action="{{ route('register.verify.submit') }}" x-data="verificationForm()">
                @csrf
                
                <!-- Verification Code -->
                <div>
                    <label for="verification_code" class="block text-sm font-medium text-slate-700 mb-2">
                        Kode Verifikasi (6 digit)
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                            </svg>
                        </div>
                        <input id="verification_code" 
                               name="verification_code" 
                               type="text" 
                               autocomplete="one-time-code" 
                               required
                               maxlength="6"
                               pattern="[0-9]{6}"
                               class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-[#F4C430] focus:border-[#F4C430] transition-all bg-white/50 text-center text-2xl font-mono tracking-widest"
                               placeholder="000000"
                               x-model="code"
                               @input="handleInput"
                               autofocus>
                    </div>
                    @error('verification_code')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Submit Button -->
                <div>
                    <button type="submit"
                        class="w-full flex justify-center py-3 px-4 border border-transparent rounded-xl shadow-lg text-sm font-semibold text-white bg-gradient-to-r from-[#F4C430] to-[#E6B020] hover:from-[#E6B020] hover:to-[#D4A017] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#F4C430] transition-all duration-300 transform hover:-translate-y-0.5">
                        Verifikasi & Daftar
                    </button>
                </div>

                <!-- Resend Code -->
                <div class="text-center">
                    <button type="button" @click="resendCode()" id="resendBtn"
                        class="text-sm text-[#F4C430] hover:text-[#E6B020] transition-colors underline">
                        Kirim Ulang Kode Verifikasi
                    </button>
                    <div id="resendStatus" class="mt-2 text-sm"></div>
                </div>
            </form>
        </div>

        <!-- Back to Register -->
        <div class="text-center">
            <p class="text-sm text-slate-600">
                Belum menerima kode?
                <a href="{{ route('register') }}" class="font-medium text-[#F4C430] hover:text-[#E6B020] transition-colors">
                    Daftar ulang
                </a>
            </p>
            <a href="{{ route('home') }}" class="mt-2 inline-block text-sm text-slate-600 hover:text-[#F4C430] transition-colors">
                ← Kembali ke Beranda
            </a>
        </div>
    </div>

    <script>
        function verificationForm() {
            return {
                code: '',
                
                handleInput(event) {
                    // Only allow numbers
                    let value = event.target.value.replace(/[^0-9]/g, '');
                    
                    // Limit to 6 digits
                    if (value.length > 6) {
                        value = value.substring(0, 6);
                    }
                    
                    this.code = value;
                    event.target.value = value;
                    
                    // Auto-submit when 6 digits entered
                    if (value.length === 6) {
                        event.target.form.submit();
                    }
                },
                
                resendCode() {
                    const resendBtn = document.getElementById('resendBtn');
                    const resendStatus = document.getElementById('resendStatus');
                    let countdown = 0;
                    let countdownInterval = null;

                    if (countdown > 0) {
                        return;
                    }

                    resendBtn.disabled = true;
                    resendBtn.textContent = 'Mengirim...';
                    resendStatus.innerHTML = '<p class="text-blue-600">Mengirim kode verifikasi...</p>';

                    fetch('{{ route("register.resend-verification") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            resendStatus.innerHTML = '<p class="text-green-600">✓ Kode verifikasi telah dikirim ulang</p>';
                            
                            // Start countdown (60 seconds)
                            countdown = 60;
                            resendBtn.textContent = `Kirim Ulang (${countdown}s)`;
                            
                            if (countdownInterval) {
                                clearInterval(countdownInterval);
                            }
                            
                            countdownInterval = setInterval(() => {
                                countdown--;
                                if (countdown > 0) {
                                    resendBtn.textContent = `Kirim Ulang (${countdown}s)`;
                                } else {
                                    resendBtn.disabled = false;
                                    resendBtn.textContent = 'Kirim Ulang Kode Verifikasi';
                                    clearInterval(countdownInterval);
                                    resendStatus.innerHTML = '';
                                }
                            }, 1000);
                        } else {
                            resendStatus.innerHTML = `<p class="text-red-600">${data.message || 'Gagal mengirim kode verifikasi'}</p>`;
                            resendBtn.disabled = false;
                            resendBtn.textContent = 'Kirim Ulang Kode Verifikasi';
                        }
                    })
                    .catch(error => {
                        resendStatus.innerHTML = '<p class="text-red-600">Terjadi kesalahan. Silakan coba lagi.</p>';
                        resendBtn.disabled = false;
                        resendBtn.textContent = 'Kirim Ulang Kode Verifikasi';
                        console.error('Error:', error);
                    });
                }
            }
        }
    </script>
</body>
</html>
