@extends('layouts.app')

@section('title', 'Dashboard Manager - GPL Express')
@section('page-title', 'Dashboard Manager')

@section('content')
<div>
    <!-- Overview Stats -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 lg:gap-6 mb-6 lg:mb-8">
        <!-- Total Paket Hari Ini -->
        <div class="bg-white rounded-xl shadow-sm hover:shadow-md transition-shadow p-4 lg:p-6 border-l-4 border-[#F4C430]">
            <div class="flex items-center justify-between">
                <div class="flex-1">
                    <p class="text-xs lg:text-sm text-gray-600 mb-1">Paket Hari Ini</p>
                    <p class="text-2xl lg:text-3xl font-bold text-gray-900">{{ number_format($metrics['today']['total_paket']) }}</p>
                    <p class="text-xs text-gray-500 mt-1">{{ number_format($metrics['today']['delivered']) }} terkirim</p>
                </div>
                <div class="w-10 h-10 lg:w-12 lg:h-12 bg-[#F4C430]/10 rounded-lg flex items-center justify-center shrink-0 ml-3">
                    <svg class="w-5 h-5 lg:w-6 lg:h-6 text-[#F4C430]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                    </svg>
                </div>
            </div>
        </div>

        <!-- COD Collected Hari Ini -->
        <div class="bg-white rounded-xl shadow-sm hover:shadow-md transition-shadow p-4 lg:p-6 border-l-4 border-green-500">
            <div class="flex items-center justify-between">
                <div class="flex-1">
                    <p class="text-xs lg:text-sm text-gray-600 mb-1">COD Terkumpul Hari Ini</p>
                    <p class="text-2xl lg:text-3xl font-bold text-gray-900">Rp {{ number_format($metrics['today']['cod_collected'], 0, ',', '.') }}</p>
                </div>
                <div class="w-10 h-10 lg:w-12 lg:h-12 bg-green-100 rounded-lg flex items-center justify-center shrink-0 ml-3">
                    <svg class="w-5 h-5 lg:w-6 lg:h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </div>
            </div>
        </div>

        <!-- Pending Settlements -->
        <div class="bg-white rounded-xl shadow-sm hover:shadow-md transition-shadow p-4 lg:p-6 border-l-4 border-yellow-500">
            <div class="flex items-center justify-between">
                <div class="flex-1">
                    <p class="text-xs lg:text-sm text-gray-600 mb-1">Settlement Pending</p>
                    <p class="text-2xl lg:text-3xl font-bold text-gray-900">{{ number_format($metrics['pending_settlements']) }}</p>
                    <a href="{{ route('admin.finance.index') }}" class="text-xs text-[#F4C430] hover:underline mt-1">Lihat detail</a>
                </div>
                <div class="w-10 h-10 lg:w-12 lg:h-12 bg-yellow-100 rounded-lg flex items-center justify-center shrink-0 ml-3">
                    <svg class="w-5 h-5 lg:w-6 lg:h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
        </div>

        <!-- Total Kurir Aktif -->
        <div class="bg-white rounded-xl shadow-sm hover:shadow-md transition-shadow p-4 lg:p-6 border-l-4 border-blue-500">
            <div class="flex items-center justify-between">
                <div class="flex-1">
                    <p class="text-xs lg:text-sm text-gray-600 mb-1">Kurir Aktif</p>
                    <p class="text-2xl lg:text-3xl font-bold text-gray-900">{{ number_format($metrics['total_couriers']) }}</p>
                </div>
                <div class="w-10 h-10 lg:w-12 lg:h-12 bg-blue-100 rounded-lg flex items-center justify-center shrink-0 ml-3">
                    <svg class="w-5 h-5 lg:w-6 lg:h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Period Summary -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 lg:gap-6 mb-6 lg:mb-8">
        <!-- Minggu Ini -->
        <div class="bg-white rounded-xl shadow-sm p-4 lg:p-6">
            <h3 class="text-sm font-semibold text-gray-900 mb-4">Minggu Ini</h3>
            <div class="space-y-3">
                <div>
                    <p class="text-xs text-gray-600">Total Paket</p>
                    <p class="text-lg font-bold text-gray-900">{{ number_format($metrics['this_week']['total_paket']) }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-600">COD Terkumpul</p>
                    <p class="text-lg font-bold text-green-700">Rp {{ number_format($metrics['this_week']['cod_collected'], 0, ',', '.') }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-600">Terkirim</p>
                    <p class="text-lg font-bold text-blue-700">{{ number_format($metrics['this_week']['delivered']) }}</p>
                </div>
            </div>
        </div>

        <!-- Bulan Ini -->
        <div class="bg-white rounded-xl shadow-sm p-4 lg:p-6">
            <h3 class="text-sm font-semibold text-gray-900 mb-4">Bulan Ini</h3>
            <div class="space-y-3">
                <div>
                    <p class="text-xs text-gray-600">Total Paket</p>
                    <p class="text-lg font-bold text-gray-900">{{ number_format($metrics['this_month']['total_paket']) }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-600">COD Terkumpul</p>
                    <p class="text-lg font-bold text-green-700">Rp {{ number_format($metrics['this_month']['cod_collected'], 0, ',', '.') }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-600">Terkirim</p>
                    <p class="text-lg font-bold text-blue-700">{{ number_format($metrics['this_month']['delivered']) }}</p>
                </div>
            </div>
        </div>

        <!-- SLA Achievement -->
        <div class="bg-white rounded-xl shadow-sm p-4 lg:p-6">
            <h3 class="text-sm font-semibold text-gray-900 mb-4">SLA Achievement (Minggu Ini)</h3>
            <div class="space-y-3">
                <div>
                    <p class="text-xs text-gray-600">On-Time Rate</p>
                    <p class="text-lg font-bold text-green-700">{{ number_format($slaMetrics['on_time_percentage'], 1) }}%</p>
                    <p class="text-xs text-gray-500">{{ $slaMetrics['on_time'] }} dari {{ $slaMetrics['total_delivered'] }} paket</p>
                </div>
                <div class="flex gap-4 text-xs">
                    <div>
                        <p class="text-gray-600">Late</p>
                        <p class="font-semibold text-yellow-700">{{ $slaMetrics['late'] }}</p>
                    </div>
                    <div>
                        <p class="text-gray-600">Failed</p>
                        <p class="font-semibold text-red-700">{{ $slaMetrics['failed'] }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Courier Performance -->
        <div class="bg-white rounded-xl shadow-sm p-4 lg:p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Performance Kurir (Minggu Ini)</h3>
                <a href="{{ route('admin.performance.manager') }}" class="text-xs text-[#F4C430] hover:underline">Detail</a>
            </div>
            <div class="space-y-3">
                @forelse($courierPerformance as $perf)
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                        <div class="flex-1">
                            <p class="text-sm font-medium text-gray-900">{{ $perf['courier']->name }}</p>
                            <p class="text-xs text-gray-600">{{ $perf['delivered'] }}/{{ $perf['total_paket'] }} paket</p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-bold {{ $perf['success_rate'] >= 80 ? 'text-green-700' : ($perf['success_rate'] >= 60 ? 'text-yellow-700' : 'text-red-700') }}">
                                {{ number_format($perf['success_rate'], 1) }}%
                            </p>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-gray-500 text-center py-4">Belum ada data kurir</p>
                @endforelse
            </div>
        </div>

        <!-- Recent Settlements -->
        <div class="bg-white rounded-xl shadow-sm p-4 lg:p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Settlement Terbaru</h3>
                <a href="{{ route('admin.finance.index') }}" class="text-xs text-[#F4C430] hover:underline">Lihat Semua</a>
            </div>
            <div class="space-y-3">
                @forelse($recentSettlements as $settlement)
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                        <div class="flex-1">
                            <p class="text-sm font-medium text-gray-900">{{ $settlement->courier->name }}</p>
                            <p class="text-xs text-gray-600">{{ $settlement->created_at->format('d M Y H:i') }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-bold text-gray-900">Rp {{ number_format($settlement->amount, 0, ',', '.') }}</p>
                            <span class="text-xs px-2 py-1 rounded-full {{ $settlement->isConfirmed() ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
                                {{ $settlement->isConfirmed() ? 'Confirmed' : 'Pending' }}
                            </span>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-gray-500 text-center py-4">Belum ada settlement</p>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Zone Distribution & Recent Shipments -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Zone Distribution -->
        <div class="bg-white rounded-xl shadow-sm p-4 lg:p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Distribusi Zone (Minggu Ini)</h3>
            <div class="space-y-2">
                @forelse($zoneDistribution as $dist)
                    <div class="flex items-center justify-between p-2 bg-gray-50 rounded">
                        <span class="text-sm text-gray-900">{{ $dist['zone']->name }}</span>
                        <span class="text-sm font-semibold text-gray-700">{{ $dist['count'] }} paket</span>
                    </div>
                @empty
                    <p class="text-sm text-gray-500 text-center py-4">Belum ada data zone</p>
                @endforelse
            </div>
        </div>

        <!-- Recent Shipments -->
        <div class="bg-white rounded-xl shadow-sm p-4 lg:p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Paket Terbaru</h3>
                <a href="{{ route('admin.shipments.index') }}" class="text-xs text-[#F4C430] hover:underline">Lihat Semua</a>
            </div>
            <div class="space-y-2">
                @forelse($recentShipments as $shipment)
                    <div class="flex items-center justify-between p-2 bg-gray-50 rounded">
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 truncate">{{ $shipment->resi_number }}</p>
                            <p class="text-xs text-gray-600">{{ $shipment->receiver_name }}</p>
                        </div>
                        <div class="text-right ml-2">
                            <span class="text-xs px-2 py-1 rounded-full {{ $shipment->status === 'diterima' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
                                {{ ucfirst(str_replace('_', ' ', $shipment->status)) }}
                            </span>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-gray-500 text-center py-4">Belum ada paket</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection





