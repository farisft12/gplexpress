@extends('layouts.app')

@section('title', 'Detail Paket - GPL Express')
@section('page-title', 'Detail Paket')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="mb-6 lg:mb-8">
        <a href="{{ route('courier.my-packages') }}" class="text-[#F4C430] hover:underline mb-4 inline-block text-sm lg:text-base">← Kembali</a>
        <h1 class="text-xl lg:text-2xl font-bold text-gray-900 break-words">Detail Paket - Resi: {{ $shipment->resi_number }}</h1>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <!-- Status Card -->
        <div class="bg-white rounded-xl shadow-md p-6 border-t-4 border-[#F4C430]">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Status Pengiriman</h3>
            <div class="space-y-3">
                <div>
                    <span class="text-sm text-gray-600">Status:</span>
                    <span class="ml-2 px-3 py-1 text-sm font-semibold rounded-full 
                        @if($shipment->status === 'diterima') bg-green-100 text-green-800
                        @elseif($shipment->status === 'dalam_pengiriman') bg-blue-100 text-blue-800
                        @elseif($shipment->status === 'diproses') bg-yellow-100 text-yellow-800
                        @else bg-gray-100 text-gray-800
                        @endif">
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
                    @if($shipment->cod_status === 'belum_lunas')
                        <div>
                            <span class="text-sm text-gray-600">Nilai COD:</span>

                            <span class="ml-2 font-medium text-gray-900">Rp {{ number_format($shipment->total_cod_collectible, 0, ',', '.') }}</span>

                        </div>
                    @endif
                @endif
            </div>
        </div>

        <!-- Info Card -->
        <div class="bg-white rounded-xl shadow-md p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Informasi Paket</h3>
            <div class="space-y-2 text-sm">
                <div><span class="text-gray-600">Tipe:</span> <span class="font-medium">{{ $shipment->type === 'cod' ? 'COD' : 'Non-COD' }}</span></div>
                @if($shipment->isCOD())

                    <div><span class="text-gray-600">Nilai COD:</span> <span class="font-medium">Rp {{ number_format($shipment->total_cod_collectible, 0, ',', '.') }}</span></div>

                    @if($shipment->payment_method)
                        <div><span class="text-gray-600">Metode Bayar:</span> <span class="font-medium">{{ strtoupper($shipment->payment_method) }}</span></div>
                    @endif
                @endif
                <div><span class="text-gray-600">Jenis Paket:</span> <span class="font-medium">{{ $shipment->package_type }}</span></div>
                <div><span class="text-gray-600">Berat:</span> <span class="font-medium">{{ $shipment->weight }} kg</span></div>
                <div><span class="text-gray-600">Cabang Asal:</span> <span class="font-medium">{{ $shipment->originBranch->name ?? 'N/A' }}</span></div>
                <div><span class="text-gray-600">Cabang Tujuan:</span> <span class="font-medium">{{ $shipment->destinationBranch->name ?? 'N/A' }}</span></div>
                <div><span class="text-gray-600">Dibuat:</span> <span class="font-medium">{{ $shipment->created_at->format('d/m/Y H:i') }}</span></div>
                @if($shipment->assigned_at)
                    <div><span class="text-gray-600">Ditugaskan:</span> <span class="font-medium">{{ $shipment->assigned_at->format('d/m/Y H:i') }}</span></div>
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

                @if($shipment->sender_phone)
                    <div><span class="text-sm text-gray-600">HP:</span> <span class="font-medium">{{ $shipment->sender_phone }}</span></div>
                @elseif($shipment->external_resi_number)
                    <div><span class="text-sm text-gray-600">Resi Ekspedisi:</span> <span class="font-medium">{{ $shipment->external_resi_number }}</span></div>
                @endif

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




