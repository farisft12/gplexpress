@extends('layouts.app')

@section('title', 'Laporan Keuangan - GPL Express')
@section('page-title', 'Laporan Keuangan')

@section('content')
<style>
    .no-scrollbar::-webkit-scrollbar { display: none; }
    .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    
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
    
    .animate-fade-in-up {
        animation: fadeInUp 0.6s ease-out forwards;
        opacity: 0;
    }
    
    .animate-slide-in-right {
        animation: slideInRight 0.6s ease-out forwards;
        opacity: 0;
    }
    
    .finance-card {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
    }
    
    .finance-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
        transition: left 0.5s;
    }
    
    .finance-card:hover::before {
        left: 100%;
    }
    
    .finance-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    }
    
    .icon-bounce {
        transition: transform 0.3s ease;
    }
    
    .finance-card:hover .icon-bounce {
        transform: scale(1.1) rotate(5deg);
    }
    
    .number-counter {
        font-variant-numeric: tabular-nums;
    }
</style>
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
        <div>
            <h1 class="text-2xl lg:text-3xl font-bold text-gray-900">Laporan Keuangan</h1>
            <p class="text-sm text-gray-600 mt-2">Dashboard keuangan dan laporan kurir aktif</p>
        </div>
        @if(Auth::user()->isOwner() || Auth::user()->isAdmin())
            <a href="{{ route('admin.finance.operational-costs.create') }}" class="bg-[#F4C430] text-white px-4 py-2.5 lg:px-6 lg:py-3 rounded-lg font-semibold hover:bg-[#E6B020] transition-colors text-sm lg:text-base whitespace-nowrap">
                + Tambah Biaya Operasional
            </a>
        @endif
    </div>

    <!-- Date Range Filter -->
    <div class="bg-white rounded-xl shadow-md p-6 mb-6">
        <form method="GET" action="{{ route('admin.finance.index') }}" class="flex flex-wrap gap-4 items-end">
            @if(Auth::user()->isOwner() && isset($branches))
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Cabang</label>
                    <select name="branch_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#F4C430] focus:border-transparent outline-none">
                        <option value="">Semua Cabang</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>
                                {{ $branch->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif
            <div class="flex-1 min-w-[150px]">
                <label class="block text-sm font-medium text-gray-700 mb-2">Dari Tanggal</label>
                <input type="date" name="date_from" value="{{ $dateFrom }}" 
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#F4C430] focus:border-transparent outline-none">
            </div>
            <div class="flex-1 min-w-[150px]">
                <label class="block text-sm font-medium text-gray-700 mb-2">Sampai Tanggal</label>
                <input type="date" name="date_to" value="{{ $dateTo }}" 
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#F4C430] focus:border-transparent outline-none">
            </div>
            <div class="flex items-end">
                <button type="submit" class="bg-[#F4C430] text-white px-6 py-2 rounded-lg font-semibold hover:bg-[#E6B020] transition-colors">
                    Filter
                </button>
            </div>
        </form>
    </div>

    <!-- Summary Cards -->
    <div class="flex gap-4 w-full overflow-x-auto no-scrollbar mb-6">
        <!-- COD Collections -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-5 finance-card w-[240px] shrink-0 animate-fade-in-up" style="animation-delay: 0.1s;">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-sm font-semibold text-gray-700">COD Terkumpul</h3>
                <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center icon-bounce">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
            <p class="text-2xl font-bold text-gray-900 number-counter" data-amount="{{ $summary['cod_collections']['total_collected'] }}">Rp 0</p>
        </div>

        <!-- Revenue -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-5 finance-card w-[240px] shrink-0 animate-fade-in-up" style="animation-delay: 0.2s;">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-sm font-semibold text-gray-700">Revenue</h3>
                <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center icon-bounce">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                    </svg>
                </div>
            </div>
            <p class="text-2xl font-bold number-counter {{ $summary['revenue']['total'] < 0 ? 'text-red-600' : 'text-gray-900' }}" data-amount="{{ $summary['revenue']['total'] }}" data-is-negative="{{ $summary['revenue']['total'] < 0 ? 'true' : 'false' }}">Rp 0</p>
            <p class="text-[7px] text-gray-500 mt-1">Total Pendapatan - Biaya Operasional</p>
        </div>

        <!-- Total Pendapatan -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-5 finance-card w-[240px] shrink-0 animate-fade-in-up" style="animation-delay: 0.3s;">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-sm font-semibold text-gray-700">Total Pendapatan</h3>
                <div class="w-10 h-10 bg-orange-100 rounded-lg flex items-center justify-center icon-bounce">
                    <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
            <p class="text-2xl font-bold text-gray-900 number-counter" data-amount="{{ $summary['total_pendapatan']['total'] }}">Rp 0</p>
        </div>

        <!-- Biaya Operasional -->        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-5 finance-card w-[240px] shrink-0 animate-fade-in-up" style="animation-delay: 0.4s;">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-sm font-semibold text-gray-700">Biaya Operasional</h3>
                <div class="w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center icon-bounce">
                    <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
            </div>
            <p class="text-2xl font-bold text-gray-900 number-counter" data-amount="{{ $summary['operational_costs']['total'] }}">Rp 0</p>
        </div>

        <!-- Outstanding COD -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-5 finance-card w-[240px] shrink-0 animate-fade-in-up" style="animation-delay: 0.5s;">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-sm font-semibold text-gray-700">COD Belum Lunas</h3>
                <div class="w-10 h-10 bg-yellow-100 rounded-lg flex items-center justify-center icon-bounce">
                    <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
            <p class="text-2xl font-bold text-gray-900 number-counter" data-amount="{{ $summary['outstanding_cod']['total_amount'] }}">Rp 0</p>
        </div>
    </div>

    <!-- Operational Costs Table -->
    <div class="bg-white rounded-xl shadow-md overflow-hidden mb-6">
        <div class="p-6 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Data Biaya Operasional</h3>
            <p class="text-sm text-gray-600 mt-1">Daftar biaya operasional berdasarkan periode yang dipilih</p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Uraian</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cabang</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Tarif</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dibuat Oleh</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dibuat Pada</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($operationalCosts as $cost)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $cost->date->format('d M Y') }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900">
                                {{ $cost->description }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $cost->branch ? $cost->branch->name : '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-semibold text-gray-900">
                                Rp {{ number_format($cost->amount, 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $cost->createdBy->name }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $cost->created_at->format('d M Y H:i') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm">
                                @can('update', $cost)
                                    <a href="{{ route('admin.finance.operational-costs.edit', $cost) }}" 
                                       class="inline-flex items-center px-3 py-1.5 bg-[#F4C430] text-white rounded-lg text-xs font-semibold hover:bg-[#E6B020] transition-colors">
                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                        Edit
                                    </a>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                                Tidak ada data biaya operasional untuk periode yang dipilih
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($operationalCosts->hasPages())
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $operationalCosts->links() }}
            </div>
        @endif
    </div>

    <!-- Charts -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 lg:gap-6 mb-6">
        <!-- Revenue Trends -->
        <div class="bg-white rounded-xl shadow-md p-4 lg:p-6 overflow-hidden">
            <h3 class="text-base lg:text-lg font-semibold text-gray-900 mb-3 lg:mb-4">Trend Revenue</h3>
            <div class="relative w-full" style="height: 300px; max-height: 300px;">
                <canvas id="revenueTrendsChart"></canvas>
            </div>
        </div>

        <!-- COD vs Non-COD -->
        <div class="bg-white rounded-xl shadow-md p-4 lg:p-6 overflow-hidden">
            <h3 class="text-base lg:text-lg font-semibold text-gray-900 mb-3 lg:mb-4">COD vs Non-COD</h3>
            <div class="relative w-full" style="height: 300px; max-height: 300px;">
                <canvas id="codVsNonCodChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Active Courier Reports -->
    <div class="bg-white rounded-xl shadow-md overflow-hidden mb-6">
        <div class="p-6 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Laporan Kurir Aktif</h3>
            <p class="text-sm text-gray-600 mt-1">Daftar kurir aktif dengan saldo dan performa</p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Kurir</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cabang</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Saldo</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Total Paket</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Terkirim</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Success Rate</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">COD 7 Hari</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($activeCouriers as $courier)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ $courier['name'] }}</div>
                                <div class="text-xs text-gray-500">{{ $courier['email'] }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $courier['branch'] }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-semibold {{ $courier['balance'] > 0 ? 'text-green-600' : 'text-gray-500' }}">
                                Rp {{ number_format($courier['balance'], 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-900">
                                {{ $courier['total_packages'] }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-green-600">
                                {{ $courier['delivered'] }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $courier['success_rate'] >= 90 ? 'bg-green-100 text-green-700' : ($courier['success_rate'] >= 70 ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 text-red-700') }}">
                                    {{ $courier['success_rate'] }}%
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-semibold text-gray-900">
                                Rp {{ number_format($courier['recent_cod'], 0, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                                Tidak ada data kurir aktif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
    // Revenue Trends Chart
    const revenueCtx = document.getElementById('revenueTrendsChart').getContext('2d');
    new Chart(revenueCtx, {
        type: 'line',
        data: {
            labels: @json($chartData['revenue_trends']['labels']),
            datasets: [{
                label: 'COD Revenue',
                data: @json($chartData['revenue_trends']['cod_data']),
                borderColor: 'rgb(34, 197, 94)',
                backgroundColor: 'rgba(34, 197, 94, 0.1)',
                tension: 0.4
            }, {
                label: 'Non-COD Revenue',
                data: @json($chartData['revenue_trends']['non_cod_data']),
                borderColor: 'rgb(59, 130, 246)',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.dataset.label + ': Rp ' + new Intl.NumberFormat('id-ID').format(context.parsed.y);
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return 'Rp ' + new Intl.NumberFormat('id-ID').format(value);
                        }
                    }
                }
            }
        }
    });

    // COD vs Non-COD Chart
    const codVsNonCodCtx = document.getElementById('codVsNonCodChart').getContext('2d');
    new Chart(codVsNonCodCtx, {
        type: 'doughnut',
        data: {
            labels: ['COD', 'Non-COD'],
            datasets: [{
                data: [@json($chartData['cod_vs_non_cod']['cod']), @json($chartData['cod_vs_non_cod']['non_cod'])],
                backgroundColor: [
                    'rgb(34, 197, 94)',
                    'rgb(59, 130, 246)'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.parsed || 0;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                            return label + ': Rp ' + new Intl.NumberFormat('id-ID').format(value) + ' (' + percentage + '%)';
                        }
                    }
                }
            }
        }
    });

    // Number counter animation
    document.addEventListener('DOMContentLoaded', function() {
        function formatAmount(amount) {
            const absAmount = Math.abs(amount);
            if (absAmount >= 1000000000) {
                return 'Rp ' + (absAmount / 1000000000).toFixed(1).replace('.', ',') + 'M';
            } else if (absAmount >= 1000000) {
                return 'Rp ' + (absAmount / 1000000).toFixed(1).replace('.', ',') + 'Jt';
            } else if (absAmount >= 1000) {
                return 'Rp ' + Math.floor(absAmount / 1000).toLocaleString('id-ID') + 'Rb';
            } else {
                return 'Rp ' + Math.floor(absAmount).toLocaleString('id-ID');
            }
        }
        
        function animateCounter(element, target, isNegative = false) {
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
                
                const sign = isNegative && current > 0 ? '-' : '';
                element.textContent = sign + formatAmount(current);
            }, 16);
        }
        
        // Animate all counters
        document.querySelectorAll('.number-counter').forEach(counter => {
            const amount = parseFloat(counter.getAttribute('data-amount'));
            const isNegative = counter.getAttribute('data-is-negative') === 'true';
            
            if (!isNaN(amount)) {
                animateCounter(counter, Math.abs(amount), isNegative);
            }
        });
        
        // Intersection Observer for scroll animations
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
        
        document.querySelectorAll('.animate-fade-in-up, .animate-slide-in-right').forEach(el => {
            observer.observe(el);
        });
    });

</script>
@endsection
