@extends('layouts.app')

@section('title', 'Dashboard Kurir - GPL Expres')
@section('page-title', 'Dashboard Kurir')

@section('content')
<div>
    <!-- Stats Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 lg:gap-6 mb-6 lg:mb-8">
        <div class="bg-white rounded-xl shadow-sm hover:shadow-md transition-shadow p-4 lg:p-6 border-l-4 border-[#F4C430]">
            <div class="text-xs lg:text-sm text-gray-600 mb-1">Total Paket</div>
            <div class="text-2xl lg:text-3xl font-bold text-gray-900">{{ $total_today }}</div>
        </div>
        <div class="bg-white rounded-xl shadow-sm hover:shadow-md transition-shadow p-4 lg:p-6 border-l-4 border-green-500">
            <div class="text-xs lg:text-sm text-gray-600 mb-1">Terkirim</div>
            <div class="text-2xl lg:text-3xl font-bold text-gray-900">{{ $delivered_today }}</div>
        </div>
        <div class="bg-white rounded-xl shadow-sm hover:shadow-md transition-shadow p-4 lg:p-6 border-l-4 border-red-500">
            <div class="text-xs lg:text-sm text-gray-600 mb-1">Gagal</div>
            <div class="text-2xl lg:text-3xl font-bold text-gray-900">{{ $failed_today }}</div>
        </div>
        <div class="bg-white rounded-xl shadow-sm hover:shadow-md transition-shadow p-4 lg:p-6 border-l-4 border-blue-500 col-span-2 lg:col-span-1">
            <div class="text-xs lg:text-sm text-gray-600 mb-1">Saldo COD</div>
            <div class="text-xl lg:text-2xl font-bold text-gray-900">Rp {{ number_format($balance, 0, ',', '.') }}</div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-sm p-4 lg:p-6 mb-4 lg:mb-6">
        <div class="flex flex-wrap gap-2 lg:gap-4">
            <button onclick="filterShipments('all')" class="filter-btn px-3 py-2 lg:px-4 text-sm lg:text-base rounded-lg font-medium bg-[#F4C430] text-white">
                Semua
            </button>
            <button onclick="filterShipments('cod')" class="filter-btn px-3 py-2 lg:px-4 text-sm lg:text-base rounded-lg font-medium bg-gray-100 text-gray-700 hover:bg-gray-200">
                COD
            </button>
            <button onclick="filterShipments('non_cod')" class="filter-btn px-3 py-2 lg:px-4 text-sm lg:text-base rounded-lg font-medium bg-gray-100 text-gray-700 hover:bg-gray-200">
                Non-COD
            </button>
        </div>
    </div>

    <!-- Active Shipments -->
    <div class="bg-white rounded-xl shadow-sm p-4 lg:p-6">
        <h2 class="text-lg lg:text-xl font-bold text-gray-900 mb-4 lg:mb-6">Paket Aktif</h2>

        @if($active_shipments->count() > 0)
            <div class="space-y-4" id="shipmentsList">
                @foreach($active_shipments as $shipment)
                    <div class="shipment-item border border-gray-200 rounded-lg p-3 lg:p-4 hover:shadow-md transition-shadow" data-type="{{ $shipment->type }}">
                        <div class="flex flex-col gap-3 lg:gap-4">
                            <div class="flex-1">
                                <div class="flex flex-wrap items-center gap-2 mb-2">
                                    <span class="font-bold text-gray-900 text-sm lg:text-base">{{ $shipment->resi_number }}</span>
                                    @if($shipment->type === 'cod')
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-[#F4C430]/10 text-[#F4C430]">
                                            COD
                                        </span>
                                        @if($shipment->cod_status === 'lunas')
                                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                                Lunas
                                            </span>
                                        @endif
                                    @endif
                                </div>
                                <p class="text-sm lg:text-base text-gray-900 font-medium">{{ $shipment->receiver_name }}</p>
                                <p class="text-xs lg:text-sm text-gray-500">{{ $shipment->receiver_phone }}</p>
                                <p class="text-xs lg:text-sm text-gray-600 mt-1 line-clamp-2">{{ $shipment->receiver_address }}</p>
                                @if($shipment->isCOD())
                                    <p class="text-sm lg:text-base font-semibold text-[#F4C430] mt-2">Rp {{ number_format($shipment->cod_amount, 0, ',', '.') }}</p>
                                @endif
                            </div>
                            <div>
                                <button 
                                    onclick="openUpdateModal({{ $shipment->id }}, '{{ $shipment->resi_number }}', '{{ $shipment->type }}', '{{ $shipment->cod_status }}', {{ $shipment->cod_amount ?? 0 }})"
                                    class="w-full px-4 py-2.5 bg-[#F4C430] text-white rounded-lg font-medium hover:bg-[#E6B020] transition-colors text-sm lg:text-base">
                                    Update Status
                                </button>
                            </div>
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
</div>

<!-- Update Status Modal -->
<div 
    x-data="{ 
        open: false, 
        shipmentId: null, 
        resiNumber: '', 
        type: '', 
        codStatus: '', 
        codAmount: 0,
        showModal(id, resi, shipmentType, status, amount) {
            this.shipmentId = id;
            this.resiNumber = resi;
            this.type = shipmentType;
            this.codStatus = status;
            this.codAmount = amount;
            this.open = true;
        }
    }"
    x-show="open"
    x-transition
    @click.away="open = false"
    class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4"
    style="display: none;"
>
    <div class="bg-white rounded-xl shadow-xl max-w-md w-full p-4 lg:p-6 max-h-[90vh] overflow-y-auto" @click.stop>
        <h3 class="text-lg lg:text-xl font-bold text-gray-900 mb-3 lg:mb-4">Update Status Paket</h3>
        <p class="text-xs lg:text-sm text-gray-600 mb-4 lg:mb-6">Resi: <span x-text="resiNumber" class="font-semibold"></span></p>

        <form method="POST" :action="`/courier/shipments/${shipmentId}/update-status`">
            @csrf

            <div class="mb-4 lg:mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                <select name="status" required class="w-full px-3 py-2.5 lg:px-4 lg:py-3 text-sm lg:text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#F4C430] focus:border-transparent outline-none">
                    <option value="">Pilih Status</option>
                    <option value="terkirim">Terkirim</option>
                    <option value="gagal">Gagal</option>
                    <template x-if="type === 'cod' && codStatus === 'belum_lunas'">
                        <option value="cod_lunas">COD Lunas</option>
                    </template>
                </select>
            </div>

            <div class="mb-4 lg:mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Catatan (Opsional)</label>
                <textarea name="notes" rows="3" class="w-full px-3 py-2.5 lg:px-4 lg:py-3 text-sm lg:text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#F4C430] focus:border-transparent outline-none" placeholder="Catatan pengantaran..."></textarea>
            </div>

            <div class="mb-4 lg:mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Lokasi (Opsional)</label>
                <input type="text" name="location" class="w-full px-3 py-2.5 lg:px-4 lg:py-3 text-sm lg:text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#F4C430] focus:border-transparent outline-none" placeholder="Lokasi saat ini">
            </div>

            <div class="flex flex-col sm:flex-row gap-3">
                <button type="submit" class="flex-1 bg-[#F4C430] text-white px-4 py-2.5 lg:px-6 lg:py-3 rounded-lg font-semibold hover:bg-[#E6B020] transition-colors text-sm lg:text-base">
                    Update
                </button>
                <button type="button" @click="open = false" class="px-4 py-2.5 lg:px-6 lg:py-3 bg-gray-200 text-gray-700 rounded-lg font-semibold hover:bg-gray-300 transition-colors text-sm lg:text-base">
                    Batal
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function filterShipments(type) {
        const items = document.querySelectorAll('.shipment-item');
        const buttons = document.querySelectorAll('.filter-btn');
        
        buttons.forEach(btn => {
            btn.classList.remove('bg-[#F4C430]', 'text-white');
            btn.classList.add('bg-gray-100', 'text-gray-700');
        });
        
        event.target.classList.add('bg-[#F4C430]', 'text-white');
        event.target.classList.remove('bg-gray-100', 'text-gray-700');
        
        items.forEach(item => {
            if (type === 'all' || item.dataset.type === type) {
                item.style.display = 'block';
            } else {
                item.style.display = 'none';
            }
        });
    }

    function openUpdateModal(id, resi, type, codStatus, codAmount) {
        const modal = document.querySelector('[x-data*="showModal"]');
        if (modal && window.Alpine) {
            const component = Alpine.$data(modal);
            component.showModal(id, resi, type, codStatus, codAmount);
        }
    }
</script>
@endsection

