@extends('layouts.admin')

@section('page-title', 'Dashboard')
@section('page-description', 'Ringkasan keseluruhan sistem GPL Express')

@section('content')
<div class="space-y-6">
    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Total Shipments -->
        <div class="grid-item card-hover enhanced-hover bg-white rounded-xl shadow-sm p-6 border border-gray-100 group">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm font-medium">Total Pengiriman</p>
                    <p class="text-3xl font-bold text-gray-900 mt-2 group-hover:text-blue-600 transition-colors">1,234</p>
                    <p class="text-green-600 text-sm mt-1">
                        <i class="fas fa-arrow-up mr-1"></i>
                        +12% dari bulan lalu
                    </p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center group-hover:bg-blue-200 transition-colors animate-float">
                    <i class="fas fa-box text-blue-600 text-xl group-hover:scale-110 transition-transform"></i>
                </div>
            </div>
        </div>
        
        <!-- Delivered -->
        <div class="grid-item card-hover enhanced-hover bg-white rounded-xl shadow-sm p-6 border border-gray-100 group">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm font-medium">Terkirim</p>
                    <p class="text-3xl font-bold text-gray-900 mt-2 group-hover:text-green-600 transition-colors">1,089</p>
                    <p class="text-green-600 text-sm mt-1">
                        <i class="fas fa-arrow-up mr-1"></i>
                        +8% dari bulan lalu
                    </p>
                </div>
                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center group-hover:bg-green-200 transition-colors animate-float" style="animation-delay: 0.5s;">
                    <i class="fas fa-check-circle text-green-600 text-xl group-hover:scale-110 transition-transform"></i>
                </div>
            </div>
        </div>
        
        <!-- In Transit -->
        <div class="grid-item card-hover enhanced-hover bg-white rounded-xl shadow-sm p-6 border border-gray-100 group">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm font-medium">Dalam Perjalanan</p>
                    <p class="text-3xl font-bold text-gray-900 mt-2 group-hover:text-yellow-600 transition-colors">89</p>
                    <p class="text-yellow-600 text-sm mt-1">
                        <i class="fas fa-clock mr-1"></i>
                        Aktif saat ini
                    </p>
                </div>
                <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center group-hover:bg-yellow-200 transition-colors animate-float" style="animation-delay: 1s;">
                    <i class="fas fa-truck text-yellow-600 text-xl group-hover:scale-110 transition-transform"></i>
                </div>
            </div>
        </div>
        
        <!-- Revenue -->
        <div class="grid-item card-hover enhanced-hover bg-white rounded-xl shadow-sm p-6 border border-gray-100 group">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm font-medium">Pendapatan</p>
                    <p class="text-3xl font-bold text-gray-900 mt-2 group-hover:text-purple-600 transition-colors">Rp 45.2M</p>
                    <p class="text-green-600 text-sm mt-1">
                        <i class="fas fa-arrow-up mr-1"></i>
                        +15% dari bulan lalu
                    </p>
                </div>
                <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center group-hover:bg-purple-200 transition-colors animate-float" style="animation-delay: 1.5s;">
                    <i class="fas fa-dollar-sign text-purple-600 text-xl group-hover:scale-110 transition-transform"></i>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Charts Row -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Shipment Chart -->
        <div class="card-hover bg-white rounded-xl shadow-sm p-6 border border-gray-100">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-semibold text-gray-900">Tren Pengiriman</h3>
                <select class="text-sm border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    <option>7 Hari Terakhir</option>
                    <option>30 Hari Terakhir</option>
                    <option>3 Bulan Terakhir</option>
                </select>
            </div>
            <div class="h-64 flex items-center justify-center bg-gray-50 rounded-lg">
                <div class="text-center">
                    <i class="fas fa-chart-line text-4xl text-gray-400 mb-4"></i>
                    <p class="text-gray-600">Chart akan ditampilkan di sini</p>
                    <p class="text-sm text-gray-500 mt-2">Integrasi dengan Chart.js atau library lainnya</p>
                </div>
            </div>
        </div>
        
        <!-- Performance Metrics -->
        <div class="card-hover bg-white rounded-xl shadow-sm p-6 border border-gray-100">
            <h3 class="text-lg font-semibold text-gray-900 mb-6">Metrik Performa</h3>
            
            <div class="space-y-4">
                <!-- Delivery Rate -->
                <div>
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-sm font-medium text-gray-700">Tingkat Pengiriman</span>
                        <span class="text-sm text-gray-900 font-semibold">94.5%</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="bg-green-500 h-2 rounded-full" style="width: 94.5%"></div>
                    </div>
                </div>
                
                <!-- On Time Delivery -->
                <div>
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-sm font-medium text-gray-700">Ketepatan Waktu</span>
                        <span class="text-sm text-gray-900 font-semibold">87.2%</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="bg-blue-500 h-2 rounded-full" style="width: 87.2%"></div>
                    </div>
                </div>
                
                <!-- Customer Satisfaction -->
                <div>
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-sm font-medium text-gray-700">Kepuasan Pelanggan</span>
                        <span class="text-sm text-gray-900 font-semibold">92.8%</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="bg-purple-500 h-2 rounded-full" style="width: 92.8%"></div>
                    </div>
                </div>
                
                <!-- System Uptime -->
                <div>
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-sm font-medium text-gray-700">Uptime Sistem</span>
                        <span class="text-sm text-gray-900 font-semibold">99.9%</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="bg-indigo-500 h-2 rounded-full" style="width: 99.9%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Recent Activity and Quick Actions -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Recent Shipments -->
        <div class="lg:col-span-2 card-hover bg-white rounded-xl shadow-sm border border-gray-100">
            <div class="p-6 border-b border-gray-100">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900">Pengiriman Terbaru</h3>
                    <a href="#" class="text-indigo-600 hover:text-indigo-700 text-sm font-medium">
                        Lihat Semua
                        <i class="fas fa-arrow-right ml-1"></i>
                    </a>
                </div>
            </div>
            
            <div class="p-6">
                <div class="space-y-4">
                    <!-- Shipment Item -->
                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                        <div class="flex items-center space-x-4">
                            <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-box text-blue-600"></i>
                            </div>
                            <div>
                                <p class="font-medium text-gray-900">#GPL001234</p>
                                <p class="text-sm text-gray-600">Jakarta → Surabaya</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                Dalam Perjalanan
                            </span>
                            <p class="text-sm text-gray-500 mt-1">2 jam yang lalu</p>
                        </div>
                    </div>
                    
                    <!-- Shipment Item -->
                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                        <div class="flex items-center space-x-4">
                            <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-check-circle text-green-600"></i>
                            </div>
                            <div>
                                <p class="font-medium text-gray-900">#GPL001233</p>
                                <p class="text-sm text-gray-600">Bandung → Jakarta</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                Terkirim
                            </span>
                            <p class="text-sm text-gray-500 mt-1">5 jam yang lalu</p>
                        </div>
                    </div>
                    
                    <!-- Shipment Item -->
                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                        <div class="flex items-center space-x-4">
                            <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-plus text-purple-600"></i>
                            </div>
                            <div>
                                <p class="font-medium text-gray-900">#GPL001232</p>
                                <p class="text-sm text-gray-600">Medan → Palembang</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                Baru Dibuat
                            </span>
                            <p class="text-sm text-gray-500 mt-1">1 hari yang lalu</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="space-y-6">
            <!-- Quick Actions Card -->
            <div class="card-hover bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Aksi Cepat</h3>
                
                <div class="space-y-3">
                    <button class="w-full bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-3 rounded-lg font-medium transition-colors animate-pulse-hover">
                        <i class="fas fa-plus mr-2"></i>
                        Buat Pengiriman Baru
                    </button>
                    
                    <button class="w-full bg-white hover:bg-gray-50 text-gray-700 border border-gray-300 px-4 py-3 rounded-lg font-medium transition-colors">
                        <i class="fas fa-search mr-2"></i>
                        Tracking Paket
                    </button>
                    
                    <button class="w-full bg-white hover:bg-gray-50 text-gray-700 border border-gray-300 px-4 py-3 rounded-lg font-medium transition-colors">
                        <i class="fas fa-file-alt mr-2"></i>
                        Generate Laporan
                    </button>
                </div>
            </div>
            
            <!-- System Status -->
            <div class="card-hover bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Status Sistem</h3>
                
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-2">
                            <div class="w-3 h-3 bg-green-500 rounded-full animate-pulse"></div>
                            <span class="text-sm text-gray-700">API Server</span>
                        </div>
                        <span class="text-xs text-green-600 font-medium">Online</span>
                    </div>
                    
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-2">
                            <div class="w-3 h-3 bg-green-500 rounded-full animate-pulse"></div>
                            <span class="text-sm text-gray-700">Database</span>
                        </div>
                        <span class="text-xs text-green-600 font-medium">Online</span>
                    </div>
                    
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-2">
                            <div class="w-3 h-3 bg-yellow-500 rounded-full animate-pulse"></div>
                            <span class="text-sm text-gray-700">Payment Gateway</span>
                        </div>
                        <span class="text-xs text-yellow-600 font-medium">Maintenance</span>
                    </div>
                    
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-2">
                            <div class="w-3 h-3 bg-green-500 rounded-full animate-pulse"></div>
                            <span class="text-sm text-gray-700">SMS Service</span>
                        </div>
                        <span class="text-xs text-green-600 font-medium">Online</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Recent Alerts -->
    <div class="card-hover bg-white rounded-xl shadow-sm border border-gray-100">
        <div class="p-6 border-b border-gray-100">
            <h3 class="text-lg font-semibold text-gray-900">Notifikasi & Peringatan</h3>
        </div>
        
        <div class="p-6">
            <div class="space-y-4">
                <!-- Alert Item -->
                <div class="flex items-start space-x-4 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                    <div class="w-8 h-8 bg-yellow-100 rounded-lg flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-exclamation-triangle text-yellow-600"></i>
                    </div>
                    <div class="flex-1">
                        <p class="font-medium text-gray-900">Delay Pengiriman</p>
                        <p class="text-sm text-gray-600 mt-1">5 paket mengalami keterlambatan di rute Jakarta-Surabaya</p>
                        <p class="text-xs text-yellow-600 mt-2">30 menit yang lalu</p>
                    </div>
                    <button class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                
                <!-- Alert Item -->
                <div class="flex items-start space-x-4 p-4 bg-green-50 border border-green-200 rounded-lg">
                    <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-check-circle text-green-600"></i>
                    </div>
                    <div class="flex-1">
                        <p class="font-medium text-gray-900">Sistem Update Berhasil</p>
                        <p class="text-sm text-gray-600 mt-1">Update sistem tracking v2.1.3 telah berhasil diterapkan</p>
                        <p class="text-xs text-green-600 mt-2">2 jam yang lalu</p>
                    </div>
                    <button class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                
                <!-- Alert Item -->
                <div class="flex items-start space-x-4 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                    <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-info-circle text-blue-600"></i>
                    </div>
                    <div class="flex-1">
                        <p class="font-medium text-gray-900">Maintenance Terjadwal</p>
                        <p class="text-sm text-gray-600 mt-1">Maintenance server dijadwalkan pada 10 September 2025, 02:00 - 04:00 WIB</p>
                        <p class="text-xs text-blue-600 mt-2">1 hari yang lalu</p>
                    </div>
                    <button class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Additional custom styles for dashboard */
    .stat-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }
    
    .progress-bar {
        transition: width 1s ease-in-out;
    }
    
    /* Pulse animation for status indicators */
    @keyframes pulse-dot {
        0%, 100% {
            opacity: 1;
        }
        50% {
            opacity: 0.5;
        }
    }
    
    .animate-pulse {
        animation: pulse-dot 2s infinite;
    }
</style>

<script>
    // Auto refresh data every 30 seconds
    setInterval(() => {
        // Here you would typically make AJAX calls to refresh data
        console.log('Refreshing dashboard data...');
    }, 30000);
    
    // Chart initialization (when implementing with Chart.js)
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize charts here
        console.log('Dashboard loaded');
    });
    
    // Dismiss alerts
    document.querySelectorAll('.alert button').forEach(button => {
        button.addEventListener('click', function() {
            this.closest('.alert').style.opacity = '0';
            this.closest('.alert').style.transform = 'translateX(100%)';
            setTimeout(() => {
                this.closest('.alert').remove();
            }, 300);
        });
    });
</script>
@endsection
<!-- asdasd kdnaaaaaaaaaaa
 <!- -->