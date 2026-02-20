@extends('layouts.app')

@section('title', 'COD Collection - GPL Express')
@section('page-title', 'COD Collection')

@section('content')
<div>
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6 lg:mb-8">
        <div>
            <h1 class="text-2xl lg:text-3xl font-bold text-gray-900">Pengantaran & COD Collection</h1>
            <p class="text-sm text-gray-600 mt-1">Paket yang ditugaskan untuk Anda antar dan tagih COD</p>
        </div>
    </div>

    <!-- Summary Card -->
    <div class="bg-white rounded-xl shadow-sm p-6 mb-6 border-l-4 border-[#F4C430]">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600 mb-1">Total COD yang Perlu Ditagih</p>
                <p class="text-3xl font-bold text-gray-900">Rp {{ number_format($totalCod, 0, ',', '.') }}</p>
                <p class="text-xs text-gray-500 mt-1">{{ $codShipments->total() }} paket</p>
            </div>
            <div class="w-16 h-16 bg-[#F4C430]/10 rounded-full flex items-center justify-center">
                <svg class="w-8 h-8 text-[#F4C430]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
            </div>
        </div>
    </div>

    <!-- Search Filter -->
    <div class="bg-white rounded-xl shadow-sm p-4 sm:p-6 mb-6">
        <form method="GET" action="{{ route('courier.cod.dashboard') }}" class="flex flex-col sm:flex-row gap-3">
            <div class="flex-1">
                <input type="text" name="search" value="{{ request('search') }}" 
                    placeholder="Cari resi, nama penerima, atau nomor telepon..." 
                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#F4C430] focus:border-transparent outline-none text-sm sm:text-base">
            </div>
            <div class="flex gap-2">
                <button type="submit" class="flex-1 sm:flex-none bg-gray-900 text-white px-6 py-2.5 rounded-lg font-semibold hover:bg-gray-800 transition-colors text-sm sm:text-base">
                    Cari
                </button>
                @if(request('search'))
                    <a href="{{ route('courier.cod.dashboard') }}" class="flex-1 sm:flex-none bg-gray-200 text-gray-700 px-6 py-2.5 rounded-lg font-semibold hover:bg-gray-300 transition-colors text-sm sm:text-base text-center">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- COD Shipments List -->
    <div class="space-y-4">
        @forelse($codShipments as $shipment)
            <div class="bg-white rounded-xl shadow-sm p-4 sm:p-6 border-l-4 border-[#F4C430]">
                <!-- Header: Resi & COD Badge -->
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-2 flex-wrap">
                        <h3 class="text-lg sm:text-xl font-bold text-gray-900">{{ $shipment->resi_number }}</h3>
                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-[#F4C430]/10 text-[#F4C430]">
                            COD
                        </span>
                        @if($shipment->cod_status === 'lunas')
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-700">
                                Lunas
                            </span>
                        @else
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-700">
                                Belum Lunas
                            </span>
                        @endif
                    </div>
                </div>

                <!-- Data COD - Mobile Friendly Grid -->
                <div class="space-y-3 mb-4">
                    <!-- Penerima -->
                    <div class="bg-gray-50 rounded-lg p-3">
                        <p class="text-xs text-gray-500 mb-1">Penerima</p>
                        <p class="text-sm font-semibold text-gray-900">{{ $shipment->receiver_name }}</p>
                        <p class="text-xs text-gray-600 mt-1">{{ $shipment->receiver_phone }}</p>
                    </div>

                    <!-- Alamat -->
                    <div class="bg-gray-50 rounded-lg p-3">
                        <p class="text-xs text-gray-500 mb-1">Alamat</p>
                        <p class="text-sm text-gray-900">{{ $shipment->receiver_address }}</p>
                    </div>

                    <!-- Jumlah COD -->
                    @if($shipment->type === 'cod')
                        <div class="bg-[#F4C430]/5 rounded-lg p-4 border-2 border-[#F4C430]/20">
                            <p class="text-xs text-gray-500 mb-2">Jumlah COD yang Harus Ditagih</p>
                            <p class="text-2xl sm:text-3xl font-bold text-[#F4C430] mb-2">
                                Rp {{ number_format($shipment->total_cod_collectible, 0, ',', '.') }}
                            </p>
                            <div class="flex flex-wrap gap-2 text-xs text-gray-600">
                                <span>COD: Rp {{ number_format($shipment->cod_amount, 0, ',', '.') }}</span>
                                @if($shipment->cod_shipping_cost)
                                    <span>• Ongkir: Rp {{ number_format($shipment->cod_shipping_cost, 0, ',', '.') }}</span>
                                @endif
                                @if($shipment->cod_admin_fee)
                                    <span>• Admin: Rp {{ number_format($shipment->cod_admin_fee, 0, ',', '.') }}</span>
                                @endif
                            </div>
                        </div>
                    @endif

                    <!-- Status & Info -->
                    <div class="flex flex-wrap gap-2 text-xs text-gray-500">
                        @if($shipment->destination_courier_assigned_at)
                            <span>Ditugaskan: {{ \Carbon\Carbon::parse($shipment->destination_courier_assigned_at)->format('d M Y H:i') }}</span>
                        @endif
                        <span>Status: {{ ucfirst(str_replace('_', ' ', $shipment->status)) }}</span>
                    </div>
                </div>

                <!-- Action Button - Bayar COD -->
                @if($shipment->type === 'cod' && $shipment->cod_status === 'belum_lunas' && in_array($shipment->status, ['sampai_di_cabang_tujuan', 'dalam_pengiriman']))
                    <button onclick="openPaymentModal({{ $shipment->id }}, {{ json_encode($shipment->resi_number) }}, {{ $shipment->total_cod_collectible }})"
                            class="w-full bg-green-600 text-white px-4 py-3 sm:py-4 rounded-lg font-semibold text-base sm:text-lg hover:bg-green-700 transition-colors shadow-md">
                        <div class="flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                            Bayar COD
                        </div>
                    </button>
                @elseif($shipment->cod_status === 'lunas')
                    <div class="w-full bg-green-100 text-green-700 px-4 py-3 rounded-lg font-semibold text-center">
                        COD Sudah Lunas
                    </div>
                @endif
            </div>
        @empty
            <div class="bg-white rounded-xl shadow-sm p-12 text-center">
                <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                </svg>
                <p class="text-gray-500 text-lg">Tidak ada paket COD yang perlu ditagih</p>
                @if(request('search'))
                    <a href="{{ route('courier.cod.dashboard') }}" class="text-[#F4C430] hover:underline mt-2 inline-block">
                        Reset filter
                    </a>
                @endif
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($codShipments->hasPages())
        <div class="mt-6">
            {{ $codShipments->links() }}
        </div>
    @endif
</div>

<!-- Payment Modal -->
<div id="codPaymentModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 p-4" style="display: none;">
    <div class="bg-white rounded-xl shadow-xl max-w-md w-full">
        <div class="p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-bold text-gray-900">Input Pembayaran COD</h3>
                <button onclick="closePaymentModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <form id="codPaymentForm" method="POST">
                @csrf
                <div class="mb-4">
                    <p class="text-sm text-gray-600">Nomor Resi</p>
                    <p class="text-lg font-semibold text-gray-900" id="paymentResiNumber"></p>
                </div>
                
                <div class="mb-4">
                    <p class="text-sm text-gray-600">Jumlah yang Harus Ditagih</p>
                    <p class="text-2xl font-bold text-[#F4C430]" id="paymentAmount">Rp 0</p>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Jumlah yang Diterima</label>
                    <input type="number" name="amount" id="paymentAmountInput" step="0.01" min="0" required readonly
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-50 text-gray-600 cursor-not-allowed">
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Catatan (Opsional)</label>
                    <textarea name="notes" rows="3" 
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#F4C430] focus:border-transparent outline-none"
                        placeholder="Catatan tambahan..."></textarea>
                </div>

                <div class="flex gap-3">
                    <button type="button" onclick="closePaymentModal()" 
                        class="flex-1 bg-gray-200 text-gray-700 px-4 py-3 rounded-lg font-semibold hover:bg-gray-300 transition-colors">
                        Batal
                    </button>
                    <button type="submit" 
                        class="flex-1 bg-green-600 text-white px-4 py-3 rounded-lg font-semibold hover:bg-green-700 transition-colors">
                        Konfirmasi Pembayaran
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let currentShipmentId = null;
let currentExpectedAmount = 0;

function openPaymentModal(shipmentId, resiNumber, expectedAmount) {
    currentShipmentId = shipmentId;
    currentExpectedAmount = expectedAmount;
    
    const modal = document.getElementById('codPaymentModal');
    const form = document.getElementById('codPaymentForm');
    const resiElement = document.getElementById('paymentResiNumber');
    const amountElement = document.getElementById('paymentAmount');
    const amountInput = document.getElementById('paymentAmountInput');
    
    resiElement.textContent = resiNumber;
    amountElement.textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(expectedAmount);
    amountInput.value = expectedAmount;
    form.action = `/courier/shipments/${shipmentId}/cod/payment`;
    
    modal.classList.remove('hidden');
    modal.style.display = 'flex';
    modal.classList.add('items-center', 'justify-center');
}

function closePaymentModal() {
    const modal = document.getElementById('codPaymentModal');
    modal.classList.add('hidden');
    modal.style.display = 'none';
    modal.classList.remove('items-center', 'justify-center');
    currentShipmentId = null;
    currentExpectedAmount = 0;
}

// Close modal when clicking outside
document.getElementById('codPaymentModal')?.addEventListener('click', function(e) {
    if (e.target.id === 'codPaymentModal') {
        closePaymentModal();
    }
});
</script>
@endsection
