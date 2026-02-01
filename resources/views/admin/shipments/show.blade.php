@extends('layouts.app')

@section('title', 'Detail Paket - GPL Expres')
@section('page-title', 'Detail Paket')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="mb-6 lg:mb-8">
        <a href="{{ route('admin.shipments.index') }}" class="text-[#F4C430] hover:underline mb-4 inline-block text-sm lg:text-base">← Kembali</a>
        <h1 class="text-xl lg:text-2xl font-bold text-gray-900 break-words">Detail Paket - Resi: {{ $shipment->resi_number }}</h1>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <!-- Status Card -->
        <div class="bg-white rounded-xl shadow-md p-6 border-t-4 border-[#F4C430]">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Status Pengiriman</h3>
            <div class="space-y-3">
                <div>
                    <span class="text-sm text-gray-600">Status:</span>
                    <span class="ml-2 px-3 py-1 text-sm font-semibold rounded-full bg-blue-100 text-blue-800">
                        {{ ucfirst(str_replace('_', ' ', $shipment->status)) }}
                    </span>
                </div>
                @if($shipment->isCOD())
                    <div>
                        <span class="text-sm text-gray-600">COD Status:</span>
                        <span class="ml-2 px-3 py-1 text-sm font-semibold rounded-full {{ $shipment->cod_status === 'lunas' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                            {{ $shipment->cod_status === 'lunas' ? 'Lunas' : 'Belum Lunas' }}
                        </span>
                    </div>
                @endif
                @if($shipment->courier)
                    <div>
                        <span class="text-sm text-gray-600">Kurir:</span>
                        <span class="ml-2 font-medium text-gray-900">{{ $shipment->courier->name }}</span>
                    </div>
                @endif
            </div>
        </div>

        <!-- Info Card -->
        <div class="bg-white rounded-xl shadow-md p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Informasi Paket</h3>
            <div class="space-y-2 text-sm">
                <div><span class="text-gray-600">Tipe:</span> <span class="font-medium">{{ $shipment->type === 'cod' ? 'COD' : 'Non-COD' }}</span></div>
                @if($shipment->isCOD())
                    <div><span class="text-gray-600">Nilai COD:</span> <span class="font-medium">Rp {{ number_format($shipment->cod_amount, 0, ',', '.') }}</span></div>
                    <div><span class="text-gray-600">Metode Bayar:</span> <span class="font-medium">{{ strtoupper($shipment->payment_method) }}</span></div>
                @endif
                <div><span class="text-gray-600">Dibuat:</span> <span class="font-medium">{{ $shipment->created_at->format('d/m/Y H:i') }}</span></div>
                @if($shipment->assigned_at)
                    <div><span class="text-gray-600">Ditugaskan:</span> <span class="font-medium">{{ $shipment->assigned_at->format('d/m/Y H:i') }}</span></div>
                @endif
            </div>
        </div>
    </div>

    <!-- Cabang Asal & Tujuan -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <!-- Cabang Asal -->
        <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-blue-500">
            <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
                Cabang Asal
            </h3>
            <div class="space-y-2">
                @if($shipment->originBranch)
                    <div>
                        <span class="text-sm text-gray-600">Nama:</span>
                        <span class="ml-2 font-medium text-gray-900">{{ $shipment->originBranch->name }}</span>
                    </div>
                    <div>
                        <span class="text-sm text-gray-600">Kode:</span>
                        <span class="ml-2 font-medium text-gray-700">{{ $shipment->originBranch->code }}</span>
                    </div>
                @else
                    <p class="text-sm text-gray-500">Cabang asal tidak tersedia</p>
                @endif
            </div>
        </div>

        <!-- Cabang Tujuan -->
        <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-green-500">
            <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                Cabang Tujuan
            </h3>
            <div class="space-y-2">
                @if($shipment->destinationBranch)
                    <div>
                        <span class="text-sm text-gray-600">Nama:</span>
                        <span class="ml-2 font-medium text-gray-900">{{ $shipment->destinationBranch->name }}</span>
                    </div>
                    <div>
                        <span class="text-sm text-gray-600">Kode:</span>
                        <span class="ml-2 font-medium text-gray-700">{{ $shipment->destinationBranch->code }}</span>
                    </div>
                    @if($shipment->status === 'sampai_di_cabang_tujuan' && $shipment->isCOD() && $shipment->cod_status === 'belum_lunas')
                        <div class="mt-3 pt-3 border-t border-gray-200">
                            <p class="text-xs text-gray-600 mb-2">Paket sudah sampai di cabang tujuan</p>
                            <a href="{{ route('admin.shipments.index') }}?direction=incoming&status=sampai_di_cabang_tujuan" 
                               class="inline-block text-xs bg-green-600 text-white px-3 py-1.5 rounded-lg hover:bg-green-700 transition-colors">
                                Lihat Paket Masuk
                            </a>
                        </div>
                    @endif
                @else
                    <p class="text-sm text-gray-500">Cabang tujuan tidak tersedia</p>
                @endif
            </div>
        </div>
    </div>

    <!-- Pengirim & Penerima -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <!-- Pengirim -->
        <div class="bg-white rounded-xl shadow-md p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Pengirim</h3>
            <div class="space-y-2">
                <div><span class="text-sm text-gray-600">Nama:</span> <span class="font-medium">{{ $shipment->sender_name }}</span></div>
                <div><span class="text-sm text-gray-600">HP:</span> <span class="font-medium">{{ $shipment->sender_phone }}</span></div>
                <div><span class="text-sm text-gray-600">Alamat:</span> <p class="font-medium mt-1">{{ $shipment->sender_address }}</p></div>
            </div>
        </div>

        <!-- Penerima -->
        <div class="bg-white rounded-xl shadow-md p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Penerima</h3>
            <div class="space-y-2">
                <div><span class="text-sm text-gray-600">Nama:</span> <span class="font-medium">{{ $shipment->receiver_name }}</span></div>
                <div><span class="text-sm text-gray-600">HP:</span> <span class="font-medium">{{ $shipment->receiver_phone }}</span></div>
                <div><span class="text-sm text-gray-600">Alamat:</span> <p class="font-medium mt-1">{{ $shipment->receiver_address }}</p></div>
            </div>
        </div>
    </div>

    <!-- Status History -->
    <div class="bg-white rounded-xl shadow-md p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Riwayat Status</h3>
        <div class="space-y-4">
            @forelse($shipment->statusHistories as $history)
                <div class="flex gap-4 pb-4 border-b border-gray-200 last:border-0">
                    <div class="flex-shrink-0 w-2 h-2 rounded-full bg-[#F4C430] mt-2"></div>
                    <div class="flex-1">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="font-medium text-gray-900">{{ ucfirst(str_replace('_', ' ', $history->status)) }}</p>
                                @if($history->notes)
                                    <p class="text-sm text-gray-600 mt-1">{{ $history->notes }}</p>
                                @endif
                                @if($history->location)
                                    <p class="text-xs text-gray-500 mt-1">📍 {{ $history->location }}</p>
                                @endif
                            </div>
                            <div class="text-right">
                                <p class="text-xs text-gray-500">{{ $history->created_at->format('d/m/Y H:i') }}</p>
                                @if($history->updater)
                                    <p class="text-xs text-gray-400">{{ $history->updater->name }}</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <p class="text-gray-500 text-center py-4">Belum ada riwayat status</p>
            @endforelse
        </div>
    </div>
</div>
@endsection

