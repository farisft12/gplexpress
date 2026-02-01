@extends('layouts.app')

@section('title', 'Dashboard Owner - GPL Expres')
@section('page-title', 'Dashboard Owner')

@section('content')
<div>
    <div class="mb-6 lg:mb-8 flex items-center justify-between">
        <h1 class="text-2xl lg:text-3xl font-bold text-gray-900">Dashboard Owner</h1>
        @if($branches->isNotEmpty())
            <form method="GET" action="{{ route('owner.dashboard') }}" class="flex items-center space-x-2">
                <label for="branch_id" class="sr-only">Pilih Cabang</label>
                <select name="branch_id" id="branch_id" onchange="this.form.submit()"
                        class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-blue-500 focus:border-blue-500">
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
        <div class="bg-blue-50 border-l-4 border-blue-400 text-blue-700 p-4 mb-6 rounded-lg" role="alert">
            <p class="font-bold">Informasi</p>
            <p>Anda sedang melihat data agregat dari semua cabang. Pilih cabang dari dropdown di atas untuk melihat data spesifik cabang.</p>
        </div>
    @endif

    <!-- Overview Metrics -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 lg:gap-6 mb-6">
        <div class="bg-white rounded-xl shadow-sm p-5 border-l-4 border-[#F4C430]">
            <p class="text-sm text-gray-600">Paket Hari Ini</p>
            <p class="text-2xl font-bold text-gray-900">{{ number_format($metrics['today']['total_paket']) }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-5 border-l-4 border-green-500">
            <p class="text-sm text-gray-600">COD Terkumpul Hari Ini</p>
            <p class="text-2xl font-bold text-gray-900">Rp {{ number_format($metrics['today']['cod_collected'], 0, ',', '.') }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-5 border-l-4 border-yellow-500">
            <p class="text-sm text-gray-600">Settlement Pending</p>
            <p class="text-2xl font-bold text-gray-900">{{ number_format($metrics['pending_settlements']) }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-5 border-l-4 border-blue-500">
            <p class="text-sm text-gray-600">Total Cabang</p>
            <p class="text-2xl font-bold text-gray-900">{{ number_format($metrics['total_branches']) }}</p>
        </div>
    </div>

    <!-- Weekly & Monthly Summary -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 lg:gap-6 mb-6">
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Ringkasan Minggu Ini</h3>
            <div class="space-y-3">
                <div class="flex justify-between items-center">
                    <span class="text-gray-600">Total Paket</span>
                    <span class="font-medium text-gray-900">{{ number_format($metrics['this_week']['total_paket']) }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-600">COD Terkumpul</span>
                    <span class="font-medium text-gray-900">Rp {{ number_format($metrics['this_week']['cod_collected'], 0, ',', '.') }}</span>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Ringkasan Bulan Ini</h3>
            <div class="space-y-3">
                <div class="flex justify-between items-center">
                    <span class="text-gray-600">Total Paket</span>
                    <span class="font-medium text-gray-900">{{ number_format($metrics['this_month']['total_paket']) }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-600">COD Terkumpul</span>
                    <span class="font-medium text-gray-900">Rp {{ number_format($metrics['this_month']['cod_collected'], 0, ',', '.') }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 lg:gap-6 mb-6">
        <a href="{{ route('admin.shipments.index') }}" class="bg-white rounded-xl shadow-sm p-6 hover:shadow-md transition-shadow border-l-4 border-[#F4C430]">
            <h3 class="text-lg font-semibold text-gray-900 mb-2">Manajemen Paket</h3>
            <p class="text-sm text-gray-600">Kelola semua paket dari semua cabang</p>
        </a>
        <a href="{{ route('admin.reports.cod') }}" class="bg-white rounded-xl shadow-sm p-6 hover:shadow-md transition-shadow border-l-4 border-green-500">
            <h3 class="text-lg font-semibold text-gray-900 mb-2">Laporan</h3>
            <p class="text-sm text-gray-600">Lihat laporan COD dan Non-COD</p>
        </a>
        <a href="{{ route('admin.finance.index') }}" class="bg-white rounded-xl shadow-sm p-6 hover:shadow-md transition-shadow border-l-4 border-blue-500">
            <h3 class="text-lg font-semibold text-gray-900 mb-2">Keuangan</h3>
            <p class="text-sm text-gray-600">Kelola settlement dan keuangan</p>
        </a>
    </div>
</div>
@endsection





