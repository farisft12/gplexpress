@extends('layouts.app')

@section('title', 'Dashboard Owner - GPL Express')
@section('page-title', 'Dashboard Owner')

@section('content')
<style>
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    @keyframes slideInRight {
        from {
            opacity: 0;
            transform: translateX(-20px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }
    
    @keyframes pulse {
        0%, 100% {
            opacity: 1;
        }
        50% {
            opacity: 0.5;
        }
    }
    
    .animate-fade-in-up {
        animation: fadeInUp 0.6s ease-out forwards;
    }
    
    .animate-slide-in-right {
        animation: slideInRight 0.6s ease-out forwards;
    }
    
    .card-hover {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    .card-hover:hover {
        transform: translateY(-4px);
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    }
    
    .metric-card {
        position: relative;
        overflow: hidden;
    }
    
    .metric-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, transparent, currentColor, transparent);
        opacity: 0;
        transition: opacity 0.3s;
    }
    
    .metric-card:hover::before {
        opacity: 1;
    }
    
    .number-counter {
        font-variant-numeric: tabular-nums;
    }
    
    .icon-bounce {
        transition: transform 0.3s ease;
    }
    
    .card-hover:hover .icon-bounce {
        transform: scale(1.1) rotate(5deg);
    }
</style>

<div>
    <!-- Header -->
    <div class="mb-6 lg:mb-8 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 animate-fade-in-up">
        <div>
            <h1 class="text-3xl lg:text-4xl font-bold text-gray-900 mb-2">Dashboard Owner</h1>
            <p class="text-sm text-gray-600">Ringkasan operasional dan keuangan</p>
        </div>
        @if($branches->isNotEmpty())
            <form method="GET" action="{{ route('owner.dashboard') }}" class="flex items-center space-x-2">
                <label for="branch_id" class="sr-only">Pilih Cabang</label>
                <select name="branch_id" id="branch_id" onchange="this.form.submit()"
                        class="px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-[#F4C430] focus:border-[#F4C430] transition-all shadow-sm hover:shadow-md">
                    <option value="">Semua Cabang</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>
                            {{ $branch->name }}
                        </option>
                    @endforeach
                </select>
            </form>
        @endif
    </div>

    @if(!request('branch_id'))
        <div class="bg-gradient-to-r from-blue-50 to-indigo-50 border-l-4 border-blue-500 text-blue-800 p-4 mb-6 rounded-lg shadow-sm animate-slide-in-right" role="alert">
            <div class="flex items-start">
                <svg class="w-5 h-5 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                </svg>
                <div>
                    <p class="font-bold">Informasi</p>
                    <p class="text-sm">Anda sedang melihat data agregat dari semua cabang. Pilih cabang dari dropdown di atas untuk melihat data spesifik cabang.</p>
                </div>
            </div>
        </div>
    @endif

    <!-- Overview Metrics -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 lg:gap-6 mb-6">
        <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-[#F4C430] metric-card card-hover animate-fade-in-up" style="animation-delay: 0.1s;">
            <div class="flex items-center justify-between mb-3">
                <p class="text-sm font-medium text-gray-600">Paket Hari Ini</p>
                <div class="w-12 h-12 bg-[#F4C430]/10 rounded-lg flex items-center justify-center icon-bounce">
                    <svg class="w-6 h-6 text-[#F4C430]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                    </svg>
                </div>
            </div>
            <p class="text-3xl font-bold text-gray-900 number-counter" data-count="{{ $metrics['today']['total_paket'] }}">0</p>
            <p class="text-xs text-gray-500 mt-2">Total paket hari ini</p>
        </div>
        
        <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-green-500 metric-card card-hover animate-fade-in-up" style="animation-delay: 0.2s;">
            <div class="flex items-center justify-between mb-3">
                <p class="text-sm font-medium text-gray-600">COD Terkumpul</p>
                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center icon-bounce">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
            <p class="text-3xl font-bold text-gray-900 number-counter" data-amount="{{ $metrics['today']['cod_collected'] }}">Rp 0</p>
            <p class="text-xs text-gray-500 mt-2">Hari ini</p>
        </div>
        
        <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-purple-500 metric-card card-hover animate-fade-in-up" style="animation-delay: 0.3s;">
            <div class="flex items-center justify-between mb-3">
                <p class="text-sm font-medium text-gray-600">Revenue</p>
                <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center icon-bounce">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                    </svg>
                </div>
            </div>
            <p class="text-3xl font-bold {{ $metrics['revenue'] < 0 ? 'text-red-600' : 'text-gray-900' }} number-counter" data-amount="{{ $metrics['revenue'] }}">Rp 0</p>
            <p class="text-xs text-gray-500 mt-2">Total Pendapatan - Biaya Operasional</p>
        </div>
        
        <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-blue-500 metric-card card-hover animate-fade-in-up" style="animation-delay: 0.4s;">
            <div class="flex items-center justify-between mb-3">
                <p class="text-sm font-medium text-gray-600">Total Cabang</p>
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center icon-bounce">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                </div>
            </div>
            <p class="text-3xl font-bold text-gray-900 number-counter" data-count="{{ $metrics['total_branches'] }}">0</p>
            <p class="text-xs text-gray-500 mt-2">Cabang aktif</p>
        </div>
    </div>

    <!-- Weekly & Monthly Summary -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 lg:gap-6 mb-6">
        <div class="bg-white rounded-xl shadow-md p-6 card-hover animate-slide-in-right" style="animation-delay: 0.2s;">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Ringkasan Minggu Ini</h3>
                <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
            </div>
            <div class="space-y-4">
                <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                    <span class="text-gray-600 font-medium">Total Paket</span>
                    <span class="font-bold text-gray-900 text-lg number-counter" data-count="{{ $metrics['this_week']['total_paket'] }}">0</span>
                </div>
                <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                    <span class="text-gray-600 font-medium">COD Terkumpul</span>
                    <span class="font-bold text-gray-900 text-lg number-counter" data-amount="{{ $metrics['this_week']['cod_collected'] }}">Rp 0</span>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-xl shadow-md p-6 card-hover animate-slide-in-right" style="animation-delay: 0.3s;">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Ringkasan Bulan Ini</h3>
                <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                </div>
            </div>
            <div class="space-y-4">
                <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                    <span class="text-gray-600 font-medium">Total Paket</span>
                    <span class="font-bold text-gray-900 text-lg number-counter" data-count="{{ $metrics['this_month']['total_paket'] }}">0</span>
                </div>
                <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                    <span class="text-gray-600 font-medium">COD Terkumpul</span>
                    <span class="font-bold text-gray-900 text-lg number-counter" data-amount="{{ $metrics['this_month']['cod_collected'] }}">Rp 0</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 lg:gap-6 mb-6">
        <a href="{{ route('admin.performance.manager') }}" class="bg-gradient-to-br from-white to-gray-50 rounded-xl shadow-md p-6 hover:shadow-xl transition-all border-l-4 border-[#F4C430] card-hover group animate-fade-in-up" style="animation-delay: 0.4s;">
            <div class="flex items-center justify-between mb-3">
                <div class="w-12 h-12 bg-[#F4C430]/10 rounded-lg flex items-center justify-center group-hover:bg-[#F4C430] transition-colors">
                    <svg class="w-6 h-6 text-[#F4C430] group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                </div>
                <svg class="w-5 h-5 text-gray-400 group-hover:text-[#F4C430] transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </div>
            <h3 class="text-lg font-semibold text-gray-900 mb-2">Performance</h3>
            <p class="text-sm text-gray-600">Lihat performa cabang dan kurir</p>
        </a>
        
        <a href="{{ route('admin.reports.cod') }}" class="bg-gradient-to-br from-white to-gray-50 rounded-xl shadow-md p-6 hover:shadow-xl transition-all border-l-4 border-green-500 card-hover group animate-fade-in-up" style="animation-delay: 0.5s;">
            <div class="flex items-center justify-between mb-3">
                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center group-hover:bg-green-500 transition-colors">
                    <svg class="w-6 h-6 text-green-600 group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
                <svg class="w-5 h-5 text-gray-400 group-hover:text-green-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </div>
            <h3 class="text-lg font-semibold text-gray-900 mb-2">Laporan</h3>
            <p class="text-sm text-gray-600">Lihat laporan COD dan Non-COD</p>
        </a>
        
        <a href="{{ route('admin.finance.index') }}" class="bg-gradient-to-br from-white to-gray-50 rounded-xl shadow-md p-6 hover:shadow-xl transition-all border-l-4 border-blue-500 card-hover group animate-fade-in-up" style="animation-delay: 0.6s;">
            <div class="flex items-center justify-between mb-3">
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center group-hover:bg-blue-500 transition-colors">
                    <svg class="w-6 h-6 text-blue-600 group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <svg class="w-5 h-5 text-gray-400 group-hover:text-blue-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </div>
            <h3 class="text-lg font-semibold text-gray-900 mb-2">Keuangan</h3>
            <p class="text-sm text-gray-600">Kelola settlement dan keuangan</p>
        </a>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Animate number counters
    function animateCounter(element, target, isAmount = false) {
        const duration = 1500;
        const start = 0;
        const increment = target / (duration / 16);
        let current = start;
        
        const timer = setInterval(() => {
            current += increment;
            if (current >= target) {
                current = target;
                clearInterval(timer);
            }
            
            if (isAmount) {
                element.textContent = 'Rp ' + Math.floor(current).toLocaleString('id-ID');
            } else {
                element.textContent = Math.floor(current).toLocaleString('id-ID');
            }
        }, 16);
    }
    
    // Animate all counters
    document.querySelectorAll('.number-counter').forEach(counter => {
        const count = counter.getAttribute('data-count');
        const amount = counter.getAttribute('data-amount');
        
        if (count) {
            animateCounter(counter, parseInt(count));
        } else if (amount) {
            animateCounter(counter, parseFloat(amount), true);
        }
    });
    
    // Add intersection observer for scroll animations
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, observerOptions);
    
    // Observe all animated elements
    document.querySelectorAll('.animate-fade-in-up, .animate-slide-in-right').forEach(el => {
        el.style.opacity = '0';
        observer.observe(el);
    });
});
</script>
@endsection





