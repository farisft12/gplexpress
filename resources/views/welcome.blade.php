<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="GPL Express - Solusi ekspedisi cepat & terpercaya. Platform logistik modern dengan tracking real-time dan layanan profesional.">

    <title>GPL Express - Solusi Ekspedisi Cepat & Terpercaya</title>

        <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Styles -->
            @vite(['resources/css/app.css', 'resources/js/app.js'])
    
            <style>
        body {
            font-family: 'Inter', 'Poppins', sans-serif;
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        @keyframes glow {
            0%, 100% {
                box-shadow: 0 0 20px rgba(244, 196, 48, 0.3);
            }
            50% {
                box-shadow: 0 0 30px rgba(244, 196, 48, 0.5);
            }
        }
        
        @keyframes float {
            0%, 100% {
                transform: translateY(0px);
            }
            50% {
                transform: translateY(-10px);
            }
        }
        
        .animate-fade-in-up {
            animation: fadeInUp 0.8s ease-out;
        }
        
        .animate-glow {
            animation: glow 2s ease-in-out infinite;
        }
        
        .animate-float {
            animation: float 3s ease-in-out infinite;
        }
        
        .glass-effect {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .gradient-yellow {
            background: linear-gradient(135deg, #F4C430 0%, #E6B020 100%);
        }
        
        .gradient-dark {
            background: linear-gradient(135deg, #0F172A 0%, #1E293B 100%);
        }
        
        .hover-lift {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .hover-lift:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        }
        
        .yellow-accent-line {
            position: relative;
        }
        
        .yellow-accent-line::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            width: 4px;
            height: 100%;
            background: linear-gradient(180deg, #F4C430 0%, #E6B020 100%);
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        
        .yellow-accent-line:hover::before {
            opacity: 1;
        }
            </style>
    </head>
<body class="bg-white text-gray-900 antialiased overflow-x-hidden">
    <!-- Navigation -->
    <nav 
        x-data="{ scrolled: false, mobileMenuOpen: false }"
        @scroll.window="scrolled = window.scrollY > 20"
        class="fixed top-0 left-0 right-0 z-50 transition-all duration-300"
        :class="scrolled ? 'bg-white/95 backdrop-blur-md shadow-lg' : 'bg-transparent'"
    >
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-20">
                <div class="flex items-center">
                    <h1 class="text-2xl font-bold text-[#F4C430]">GPL Express</h1>
                </div>
                <div class="hidden md:flex items-center space-x-8">
                    <a href="#fitur" class="text-gray-700 hover:text-[#F4C430] transition-colors font-medium">Fitur</a>
                    <a href="#tentang" class="text-gray-700 hover:text-[#F4C430] transition-colors font-medium">Tentang</a>
                    <a href="#kontak" class="text-gray-700 hover:text-[#F4C430] transition-colors font-medium">Kontak</a>
                    <a href="{{ route('login') }}" class="text-gray-700 hover:text-[#F4C430] transition-colors font-medium">Masuk</a>
                    <a href="{{ route('register') }}" class="gradient-yellow text-white px-6 py-2.5 rounded-lg font-semibold hover:shadow-lg hover:shadow-[#F4C430]/30 transition-all">
                        Daftar
                    </a>
                </div>

                <!-- Mobile Menu Button -->
                <div class="md:hidden">
                    <button 
                        @click="mobileMenuOpen = !mobileMenuOpen"
                        class="text-gray-700"
                        aria-label="Toggle menu"
                    >
                        <svg x-show="!mobileMenuOpen" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                        <svg x-show="mobileMenuOpen" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Mobile Menu -->
            <div 
                x-show="mobileMenuOpen"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 -translate-y-1"
                x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 translate-y-0"
                x-transition:leave-end="opacity-0 -translate-y-1"
                @click.away="mobileMenuOpen = false"
                class="md:hidden border-t border-gray-200 py-4"
            >
                <div class="flex flex-col space-y-3">
                    <a href="#fitur" class="text-gray-700 hover:text-[#F4C430] transition-colors">Fitur</a>
                    <a href="#tentang" class="text-gray-700 hover:text-[#F4C430] transition-colors">Tentang</a>
                    <a href="#kontak" class="text-gray-700 hover:text-[#F4C430] transition-colors">Kontak</a>
                    <a href="{{ route('login') }}" class="text-gray-700 hover:text-[#F4C430] transition-colors">Masuk</a>
                    <a href="{{ route('register') }}" class="gradient-yellow text-white px-6 py-2.5 rounded-lg font-semibold text-center hover:shadow-lg hover:shadow-[#F4C430]/30 transition-all">
                        Daftar
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section id="lacak" class="relative min-h-screen flex items-center justify-center overflow-hidden gradient-dark">
        <!-- Abstract Background Shapes -->
        <div class="absolute inset-0 overflow-hidden">
            <div class="absolute top-20 right-20 w-96 h-96 bg-[#F4C430]/10 rounded-full blur-3xl animate-float"></div>
            <div class="absolute bottom-20 left-20 w-96 h-96 bg-[#F4C430]/5 rounded-full blur-3xl animate-float" style="animation-delay: 1s;"></div>
        </div>
        
        <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-32">
            <div class="animate-fade-in-up text-center mb-12">
                <h1 class="text-5xl md:text-6xl lg:text-7xl font-bold text-white mb-6 leading-tight">
                    Solusi Ekspedisi<br/>
                    <span class="text-[#F4C430]">Cepat & Terpercaya</span>
                </h1>
                <p class="text-xl md:text-2xl text-gray-300 mb-8 max-w-3xl mx-auto">
                    Platform logistik modern dengan teknologi tracking real-time dan layanan profesional untuk kebutuhan pengiriman Anda
                </p>
            </div>

            <!-- Floating Search Card -->
            <div class="max-w-2xl mx-auto animate-fade-in-up" style="animation-delay: 0.3s;">
                <div class="bg-white rounded-2xl shadow-2xl p-6 sm:p-8 hover-lift">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="w-12 h-12 bg-[#F4C430]/10 rounded-xl flex items-center justify-center">
                            <svg class="w-6 h-6 text-[#F4C430]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                        <h2 class="text-2xl font-bold text-gray-900">Lacak Paket Anda</h2>
                    </div>

                    <form method="GET" action="{{ route('tracking.index') }}">
                        <input type="hidden" name="from" value="home">
                        <div class="space-y-4">
                            @if ($errors->has('resi_number'))
                                <div class="bg-red-50 border-l-4 border-red-500 text-red-700 px-4 py-4 rounded-lg shadow-sm animate-fade-in">
                                    <div class="flex items-start">
                                        <svg class="w-6 h-6 text-red-500 mr-3 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        <div>
                                            <strong class="font-semibold block mb-1">Paket Tidak Ditemukan</strong>
                                            <p class="text-sm">{{ $errors->first('resi_number') }}</p>
                                        </div>
                                    </div>
                                </div>
                            @endif
                            
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none">
                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                </div>
                                <input 
                                    type="text" 
                                    name="resi_number"
                                    value="{{ old('resi_number') }}"
                                    placeholder="Masukkan nomor resi (Contoh: GPL20240116123456)"
                                    class="w-full pl-12 pr-4 py-4 border-2 rounded-xl focus:ring-2 focus:ring-[#F4C430] focus:border-[#F4C430] outline-none text-base sm:text-lg transition-all @class(['border-red-500' => $errors->has('resi_number'), 'border-gray-200' => !$errors->has('resi_number')])"
                                    required
                                >
                            </div>

                            <button 
                                type="submit"
                                class="w-full gradient-yellow text-white py-4 rounded-xl font-bold text-lg hover:shadow-2xl hover:shadow-[#F4C430]/40 transition-all transform hover:scale-[1.02] flex items-center justify-center gap-2"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                                Lacak Paket
                            </button>
                        </div>
                    </form>

                    <!-- Tips -->
                    <div class="mt-6 pt-6 border-t border-gray-200">
                        
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Additional CTA -->
            
        
        <!-- Scroll Indicator -->
        <div class="absolute bottom-10 left-1/2 transform -translate-x-1/2 animate-bounce">
            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path>
            </svg>
        </div>
    </section>

    <!-- Feature Section -->
    <section id="fitur" class="py-24 bg-[#F8FAFC]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-4xl md:text-5xl font-bold text-gray-900 mb-4">Fitur Unggulan</h2>
                <p class="text-xl text-gray-600 max-w-2xl mx-auto">Teknologi terdepan untuk pengalaman pengiriman terbaik</p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Feature 1 -->
                <div class="bg-white p-8 rounded-2xl shadow-lg hover-lift yellow-accent-line">
                    <div class="w-16 h-16 gradient-yellow rounded-xl flex items-center justify-center mb-6 transform hover:scale-110 transition-transform">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">COD Cash & QRIS</h3>
                    <p class="text-gray-600">Pembayaran fleksibel dengan uang tunai atau QRIS saat paket diterima</p>
                </div>

                <!-- Feature 2 -->
                <div class="bg-white p-8 rounded-2xl shadow-lg hover-lift yellow-accent-line">
                    <div class="w-16 h-16 gradient-yellow rounded-xl flex items-center justify-center mb-6 transform hover:scale-110 transition-transform">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Tracking Real-time</h3>
                    <p class="text-gray-600">Pantau perjalanan paket secara real-time dari awal hingga sampai tujuan</p>
                </div>

                <!-- Feature 3 -->
                <div class="bg-white p-8 rounded-2xl shadow-lg hover-lift yellow-accent-line">
                    <div class="w-16 h-16 gradient-yellow rounded-xl flex items-center justify-center mb-6 transform hover:scale-110 transition-transform">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Kurir Terverifikasi</h3>
                    <p class="text-gray-600">Tim kurir profesional dan terverifikasi untuk keamanan pengiriman</p>
                </div>

                <!-- Feature 4 -->
                <div class="bg-white p-8 rounded-2xl shadow-lg hover-lift yellow-accent-line">
                    <div class="w-16 h-16 gradient-yellow rounded-xl flex items-center justify-center mb-6 transform hover:scale-110 transition-transform">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Paket Aman</h3>
                    <p class="text-gray-600">Paket sampai dengan aman dan cepat</p>
                </div>
            </div>
        </div>
    </section>

    
    <!-- Trust Section -->
    <section id="tentang" class="py-24 bg-gradient-to-br from-[#F8FAFC] to-gray-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-4xl md:text-5xl font-bold text-gray-900 mb-4">Kepercayaan Anda, Prioritas Kami</h2>
                <div class="w-24 h-1 gradient-yellow mx-auto rounded-full"></div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Stat 1 -->
                <div 
                    x-data="{ count: 0, target: 10000 }"
                    x-init="
                        const observer = new IntersectionObserver((entries) => {
                            entries.forEach(entry => {
                                if (entry.isIntersecting) {
                                    const duration = 2000;
                                    const increment = target / (duration / 16);
                                    const timer = setInterval(() => {
                                        count += increment;
                                        if (count >= target) {
                                            count = target;
                                            clearInterval(timer);
                                        }
                                    }, 16);
                                    observer.disconnect();
                                }
                            });
                        });
                        observer.observe($el);
                    "
                    class="bg-white p-10 rounded-2xl shadow-lg text-center border-t-4 border-[#F4C430]"
                >
                    <div class="text-5xl font-bold text-[#F4C430] mb-2" x-text="Math.floor(count).toLocaleString()">0</div>
                    <div class="text-lg text-gray-700 font-semibold mb-2">+ Pengiriman</div>
                    <p class="text-gray-600">Ribuan paket telah kami antarkan dengan aman</p>
                </div>

                <!-- Stat 2 -->
                <div class="bg-white p-10 rounded-2xl shadow-lg text-center border-t-4 border-[#F4C430]">
                    <div class="text-5xl font-bold text-[#F4C430] mb-2">COD</div>
                    <div class="text-lg text-gray-700 font-semibold mb-2">Cash & QRIS</div>
                    <p class="text-gray-600">Pembayaran fleksibel untuk kemudahan transaksi</p>
                </div>

                <!-- Stat 3 -->
                <div class="bg-white p-10 rounded-2xl shadow-lg text-center border-t-4 border-[#F4C430]">
                    <div class="text-5xl font-bold text-[#F4C430] mb-2">-</div>
                    <div class="text-lg text-gray-700 font-semibold mb-2">Cabang Tervirikasi</div>
                    <p class="text-gray-600">Cabang Kami sudah sampai ke luar negeri</p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-24 gradient-dark relative overflow-hidden">
        <!-- Pattern Overlay -->
        <div class="absolute inset-0 opacity-10" style="background-image: radial-gradient(circle, #F4C430 1px, transparent 1px); background-size: 50px 50px;"></div>
        
        <div class="relative z-10 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-4xl md:text-5xl font-bold text-white mb-6">
                Mulai Kirim & Lacak Paket Sekarang
            </h2>
            <p class="text-xl text-gray-300 mb-10 max-w-2xl mx-auto">
                Bergabunglah dengan ribuan pelanggan yang telah mempercayakan pengiriman mereka kepada GPL Express
            </p>
            <a href="#lacak" class="inline-block gradient-yellow text-white px-10 py-5 rounded-xl font-bold text-lg hover:shadow-2xl hover:shadow-[#F4C430]/40 transition-all transform hover:scale-105">
                Mulai Sekarang
            </a>
        </div>
    </section>

    <!-- Footer -->
    <footer id="kontak" class="bg-[#0F172A] text-gray-300 py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8 mb-12">
                <!-- Logo & About -->
                <div class="col-span-1 md:col-span-2">
                    <h3 class="text-2xl font-bold text-[#F4C430] mb-4">GPL Express</h3>
                    <p class="text-gray-400 mb-4">
                        Platform logistik modern dengan teknologi terdepan untuk memberikan pengalaman pengiriman terbaik.
                    </p>
                </div>

                <!-- Kontak -->
                <div>
                    <h4 class="text-white font-semibold mb-4">Kontak</h4>
                    <ul class="space-y-2 text-gray-400">
                        <li>Email: info@gplexpress.com</li>
                        <li>Telepon: (021) 1234-5678</li>
                        <li>WhatsApp: +62 812-3456-7890</li>
                    </ul>
                </div>

                <!-- Alamat -->
                <div>
                    <h4 class="text-white font-semibold mb-4">Alamat</h4>
                    <p class="text-gray-400">
                        Jl. Contoh No. 123<br>
                        Jakarta Selatan, DKI Jakarta<br>
                        Indonesia 12345
                    </p>
                </div>
            </div>

            <div class="border-t border-gray-800 pt-8">
                <div class="flex flex-col md:flex-row justify-between items-center">
                    <p class="text-gray-400 text-sm">
                        &copy; {{ date('Y') }} GPL Express. All rights reserved.
                    </p>
                    <div class="flex space-x-6 mt-4 md:mt-0">
                        <a href="#" class="text-gray-400 hover:text-[#F4C430] transition-colors">Privacy Policy</a>
                        <a href="#" class="text-gray-400 hover:text-[#F4C430] transition-colors">Terms of Service</a>
                    </div>
                </div>
            </div>
        </div>
    </footer>
    </body>
</html>
