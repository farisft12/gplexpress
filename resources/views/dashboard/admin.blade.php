@extends('layouts.app')

@section('title', 'Dashboard Admin - GPL Express')
@section('page-title', 'Dashboard Admin')

@section('content')
<div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 lg:gap-6 mb-6 lg:mb-8">
        <!-- Total Paket Hari Ini -->
        <div class="bg-white rounded-xl shadow-sm hover:shadow-md transition-shadow p-4 lg:p-6 border-l-4 border-[#F4C430]">
            <div class="flex items-center justify-between">
                <div class="flex-1">
                    <p class="text-xs lg:text-sm text-gray-600 mb-1">Total Paket Hari Ini</p>
                    <p class="text-2xl lg:text-3xl font-bold text-gray-900">{{ number_format($total_paket_hari_ini) }}</p>
                </div>
                <div class="w-10 h-10 lg:w-12 lg:h-12 bg-[#F4C430]/10 rounded-lg flex items-center justify-center flex-shrink-0 ml-3">
                    <svg class="w-5 h-5 lg:w-6 lg:h-6 text-[#F4C430]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                    </svg>
                </div>
            </div>
        </div>

        <!-- Total COD Hari Ini -->
        <div class="bg-white rounded-xl shadow-sm hover:shadow-md transition-shadow p-4 lg:p-6 border-l-4 border-green-500">
            <div class="flex items-center justify-between">
                <div class="flex-1">
                    <p class="text-xs lg:text-sm text-gray-600 mb-1">Total COD Hari Ini</p>
                    <p class="text-2xl lg:text-3xl font-bold text-gray-900">Rp {{ number_format($total_cod_hari_ini, 0, ',', '.') }}</p>
                </div>
                <div class="w-10 h-10 lg:w-12 lg:h-12 bg-green-100 rounded-lg flex items-center justify-center flex-shrink-0 ml-3">
                    <svg class="w-5 h-5 lg:w-6 lg:h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </div>
            </div>
        </div>

        <!-- Paket Dalam Pengantaran -->
        <div class="bg-white rounded-xl shadow-sm hover:shadow-md transition-shadow p-4 lg:p-6 border-l-4 border-blue-500">
            <div class="flex items-center justify-between">
                <div class="flex-1">
                    <p class="text-xs lg:text-sm text-gray-600 mb-1">Dalam Pengantaran</p>
                    <p class="text-2xl lg:text-3xl font-bold text-gray-900">{{ number_format($paket_dalam_pengantaran) }}</p>
                </div>
                <div class="w-10 h-10 lg:w-12 lg:h-12 bg-blue-100 rounded-lg flex items-center justify-center flex-shrink-0 ml-3">
                    <svg class="w-5 h-5 lg:w-6 lg:h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                </div>
            </div>
        </div>

        <!-- Paket Gagal -->
        <div class="bg-white rounded-xl shadow-sm hover:shadow-md transition-shadow p-4 lg:p-6 border-l-4 border-red-500">
            <div class="flex items-center justify-between">
                <div class="flex-1">
                    <p class="text-xs lg:text-sm text-gray-600 mb-1">Paket Gagal</p>
                    <p class="text-2xl lg:text-3xl font-bold text-gray-900">{{ number_format($paket_gagal) }}</p>
                </div>
                <div class="w-10 h-10 lg:w-12 lg:h-12 bg-red-100 rounded-lg flex items-center justify-center flex-shrink-0 ml-3">
                    <svg class="w-5 h-5 lg:w-6 lg:h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 lg:gap-6 mt-6 lg:mt-8">
        <a href="{{ route('admin.shipments.create') }}" class="bg-white rounded-xl shadow-sm hover:shadow-md p-4 lg:p-6 border-2 border-dashed border-gray-300 hover:border-[#F4C430] transition-all group">
            <div class="flex items-center gap-3 lg:gap-4">
                <div class="w-10 h-10 lg:w-12 lg:h-12 bg-[#F4C430]/10 rounded-lg flex items-center justify-center group-hover:bg-[#F4C430]/20 transition-colors flex-shrink-0">
                    <svg class="w-5 h-5 lg:w-6 lg:h-6 text-[#F4C430]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <h3 class="font-semibold text-gray-900 text-sm lg:text-base">Buat Paket Baru</h3>
                    <p class="text-xs lg:text-sm text-gray-600 mt-1">Tambahkan paket pengiriman baru</p>
                </div>
            </div>
        </a>

        <a href="{{ route('admin.shipments.assign.form') }}" class="bg-white rounded-xl shadow-sm hover:shadow-md p-4 lg:p-6 border-2 border-dashed border-gray-300 hover:border-[#F4C430] transition-all group">
            <div class="flex items-center gap-3 lg:gap-4">
                <div class="w-10 h-10 lg:w-12 lg:h-12 bg-[#F4C430]/10 rounded-lg flex items-center justify-center group-hover:bg-[#F4C430]/20 transition-colors flex-shrink-0">
                    <svg class="w-5 h-5 lg:w-6 lg:h-6 text-[#F4C430]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <h3 class="font-semibold text-gray-900 text-sm lg:text-base">Assign Paket ke Kurir</h3>
                    <p class="text-xs lg:text-sm text-gray-600 mt-1">Tugaskan paket ke kurir</p>
                </div>
            </div>
        </a>
    </div>

    <!-- Recent Shipments -->
    <div class="mt-6 lg:mt-8">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-2 mb-4">
            <h2 class="text-lg lg:text-xl font-bold text-gray-900">Paket Terbaru</h2>
            <a href="{{ route('admin.shipments.index') }}" class="text-[#F4C430] hover:underline text-sm font-medium">
                Lihat Semua →
            </a>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4 lg:p-6">
            <p class="text-gray-600 text-center py-4 text-sm lg:text-base">
                <a href="{{ route('admin.shipments.index') }}" class="text-[#F4C430] hover:underline font-medium">
                    Lihat semua paket di Manajemen Paket
                </a>
            </p>
        </div>
    </div>
</div>
@endsection

