@extends('layouts.app')

@section('title', 'Dashboard Kurir - GPL Expres')

@section('content')
<div>
    <h1 class="text-3xl font-bold text-gray-900 mb-8">Dashboard Kurir</h1>

    <!-- Stats Card -->
    <div class="bg-white rounded-xl shadow-md p-6 mb-8 border-t-4 border-[#F4C430]">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600 mb-1">Jumlah Paket Hari Ini</p>
                <p class="text-4xl font-bold text-gray-900">{{ number_format($jumlah_paket_hari_ini) }}</p>
            </div>
            <div class="w-16 h-16 bg-[#F4C430]/10 rounded-lg flex items-center justify-center">
                <svg class="w-8 h-8 text-[#F4C430]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                </svg>
            </div>
        </div>
    </div>

    <!-- Paket Aktif Section -->
    <div class="bg-white rounded-xl shadow-md p-6">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-xl font-bold text-gray-900">Paket Aktif</h2>
            <button class="px-4 py-2 bg-[#F4C430] text-white rounded-lg font-semibold hover:bg-[#E6B020] transition-colors text-sm">
                Refresh
            </button>
        </div>

        @if(count($paket_aktif) > 0)
            <div class="space-y-4">
                @foreach($paket_aktif as $paket)
                    <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                        <div class="flex items-center justify-between">
                            <div class="flex-1">
                                <p class="font-semibold text-gray-900">Resi: {{ $paket['resi'] }}</p>
                                <p class="text-sm text-gray-600 mt-1">{{ $paket['tujuan'] }}</p>
                            </div>
                            <div class="ml-4">
                                <span class="px-3 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-700">
                                    {{ $paket['status'] }}
                                </span>
                            </div>
                        </div>
                        <div class="mt-4 flex gap-2">
                            <button class="flex-1 px-4 py-2 bg-green-500 text-white rounded-lg font-medium hover:bg-green-600 transition-colors text-sm">
                                Update Status
                            </button>
                            <button class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg font-medium hover:bg-gray-200 transition-colors text-sm">
                                Detail
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-12">
                <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                </svg>
                <p class="text-gray-600">Tidak ada paket aktif saat ini</p>
            </div>
        @endif
    </div>

    <!-- Info Message -->
    <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
        <p class="text-sm text-blue-800">
            <strong>Catatan:</strong> Daftar paket aktif akan muncul setelah tabel paket dibuat di Phase berikutnya.
        </p>
    </div>
</div>
@endsection







