@extends('layouts.app')

@section('title', 'Edit Status Paket - GPL Expres')
@section('page-title', 'Edit Status Paket')

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
    <!-- Header -->
    <div class="mb-6 lg:mb-8">
        <a href="{{ route('admin.shipments.index') }}" class="inline-flex items-center text-[#F4C430] hover:text-[#E6B020] mb-4 text-sm lg:text-base transition-colors">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Kembali
        </a>
        <h1 class="text-2xl lg:text-3xl font-bold text-gray-900">Edit Status Paket</h1>
        <p class="text-sm text-gray-600 mt-1">Nomor Resi: <span class="font-semibold">{{ $shipment->resi_number }}</span></p>
    </div>

    <!-- Current Status Info -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 sm:p-6 mb-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600 mb-1">Status Saat Ini</p>
                @php
                    $statusColors = [
                        'pickup' => 'bg-yellow-100 text-yellow-800',
                        'diproses' => 'bg-blue-100 text-blue-800',
                        'dalam_pengiriman' => 'bg-purple-100 text-purple-800',
                        'sampai_di_cabang_tujuan' => 'bg-orange-100 text-orange-800',
                        'diterima' => 'bg-green-100 text-green-800',
                    ];
                @endphp
                <span class="px-3 py-1 text-sm font-semibold rounded-full {{ $statusColors[$shipment->status] ?? 'bg-gray-100 text-gray-800' }}">
                    {{ ucfirst(str_replace('_', ' ', $shipment->status)) }}
                </span>
                @if($shipment->type === 'cod')
                    <p class="text-sm text-gray-600 mt-2">
                        Status COD: 
                        <span class="font-semibold {{ $shipment->cod_status === 'lunas' ? 'text-green-600' : 'text-red-600' }}">
                            {{ $shipment->cod_status === 'lunas' ? 'Lunas' : 'Belum Lunas' }}
                        </span>
                    </p>
                @endif
            </div>
            <div class="text-right">
                <p class="text-sm text-gray-600 mb-1">Kurir</p>
                <p class="text-sm font-medium text-gray-900">{{ $shipment->courier ? $shipment->courier->name : '-' }}</p>
            </div>
        </div>
        @if($shipment->type === 'cod' && $shipment->cod_status !== 'lunas' && $shipment->status === 'sampai_di_cabang_tujuan')
            <div class="mt-4 p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                <p class="text-sm text-yellow-800">
                    <strong>⚠ Peringatan:</strong> Paket COD harus lunas terlebih dahulu sebelum status dapat diubah menjadi "Diterima". 
                    Silakan lakukan pembayaran COD terlebih dahulu.
                </p>
            </div>
        @endif
    </div>

    <!-- Edit Status Form -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 sm:p-6 lg:p-8">
        <form method="POST" action="{{ route('admin.shipments.update-status', $shipment->id) }}" class="space-y-6">
            @csrf

            <div>
                <label for="status" class="block text-sm font-medium text-gray-700 mb-2">
                    Status Baru <span class="text-red-500">*</span>
                </label>
                <select id="status" name="status" required
                    class="w-full px-4 py-2.5 sm:py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#F4C430] focus:border-[#F4C430] outline-none transition-colors text-sm sm:text-base @error('status') border-red-500 @enderror">
                    <option value="">Pilih Status</option>
                    @php
                        $allowedStatuses = [
                            'pickup' => ['diproses'],
                            'diproses' => ['dalam_pengiriman'],
                            'dalam_pengiriman' => ['sampai_di_cabang_tujuan'],
                            'sampai_di_cabang_tujuan' => ['diterima'],
                            'diterima' => [],
                        ];
                        $nextStatuses = $allowedStatuses[$shipment->status] ?? [];
                    @endphp
                    @if(in_array('pickup', $nextStatuses) || $shipment->status === 'pickup')
                        <option value="pickup" {{ old('status', $shipment->status) === 'pickup' ? 'selected' : '' }}>Pickup</option>
                    @endif
                    @if(in_array('diproses', $nextStatuses) || $shipment->status === 'diproses')
                        <option value="diproses" {{ old('status', $shipment->status) === 'diproses' ? 'selected' : '' }}>Diproses</option>
                    @endif
                    @if(in_array('dalam_pengiriman', $nextStatuses) || $shipment->status === 'dalam_pengiriman')
                        <option value="dalam_pengiriman" {{ old('status', $shipment->status) === 'dalam_pengiriman' ? 'selected' : '' }}>Dalam Pengiriman</option>
                    @endif
                    @if(in_array('sampai_di_cabang_tujuan', $nextStatuses) || $shipment->status === 'sampai_di_cabang_tujuan')
                        <option value="sampai_di_cabang_tujuan" {{ old('status', $shipment->status) === 'sampai_di_cabang_tujuan' ? 'selected' : '' }}>Sampai di Cabang Tujuan</option>
                    @endif
                    @if(in_array('diterima', $nextStatuses) || $shipment->status === 'diterima')
                        @if($shipment->type === 'cod' && $shipment->cod_status !== 'lunas' && $shipment->status !== 'diterima')
                            <option value="diterima" disabled>Diterima (COD harus lunas terlebih dahulu)</option>
                        @else
                            <option value="diterima" {{ old('status', $shipment->status) === 'diterima' ? 'selected' : '' }}>Diterima</option>
                        @endif
                    @endif
                </select>
                @error('status')
                    <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-2 text-xs text-gray-500">
                    @if($shipment->status === 'pickup')
                        Status dapat diubah ke: <strong>Diproses</strong>
                    @elseif($shipment->status === 'diproses')
                        Status dapat diubah ke: <strong>Dalam Pengiriman</strong>
                    @elseif($shipment->status === 'dalam_pengiriman')
                        Status dapat diubah ke: <strong>Sampai di Cabang Tujuan</strong>
                    @elseif($shipment->status === 'sampai_di_cabang_tujuan')
                        Status dapat diubah ke: <strong>Diterima</strong>
                        @if($shipment->type === 'cod' && $shipment->cod_status !== 'lunas')
                            <span class="text-red-600 font-semibold">⚠ Paket COD harus lunas terlebih dahulu!</span>
                        @endif
                    @else
                        Status ini tidak dapat diubah lagi.
                    @endif
                </p>
            </div>

            @if($shipment->type === 'cod')
                <div id="paymentMethodField" style="display: {{ ($shipment->status === 'diterima') ? 'block' : 'none' }};">
                    <label for="payment_method" class="block text-sm font-medium text-gray-700 mb-2">
                        Metode Pembayaran <span class="text-red-500">*</span>
                    </label>
                    <select id="payment_method" name="payment_method"
                        class="w-full px-4 py-2.5 sm:py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#F4C430] focus:border-[#F4C430] outline-none transition-colors text-sm sm:text-base @error('payment_method') border-red-500 @enderror">
                        <option value="">Pilih metode pembayaran</option>
                        <option value="cash" {{ old('payment_method', $shipment->payment_method) === 'cash' ? 'selected' : '' }}>Cash</option>
                        <option value="qris" {{ old('payment_method', $shipment->payment_method) === 'qris' ? 'selected' : '' }}>QRIS</option>
                    </select>
                    @error('payment_method')
                        <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-2 text-xs text-gray-500">
                        Metode pembayaran untuk paket COD yang sudah diterima.
                    </p>
                </div>
            @endif

            <div>
                <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">
                    Catatan (Opsional)
                </label>
                <textarea id="notes" name="notes" rows="3"
                    class="w-full px-4 py-2.5 sm:py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#F4C430] focus:border-[#F4C430] outline-none transition-colors text-sm sm:text-base resize-none @error('notes') border-red-500 @enderror"
                    placeholder="Tambahkan catatan perubahan status...">{{ old('notes') }}</textarea>
                @error('notes')
                    <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Status History -->
            @if($shipment->statusHistories->count() > 0)
                <div class="border-t border-gray-200 pt-6">
                    <h3 class="text-sm font-semibold text-gray-900 mb-4">Riwayat Status</h3>
                    <div class="space-y-3">
                        @foreach($shipment->statusHistories->take(5) as $history)
                            <div class="flex items-start gap-3 p-3 bg-gray-50 rounded-lg">
                                <div class="flex-shrink-0">
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $statusColors[$history->status] ?? 'bg-gray-100 text-gray-800' }}">
                                        {{ ucfirst(str_replace('_', ' ', $history->status)) }}
                                    </span>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm text-gray-900">{{ $history->notes ?? '-' }}</p>
                                    <p class="text-xs text-gray-500 mt-1">
                                        {{ $history->created_at->format('d/m/Y H:i') }}
                                        @if($history->updater)
                                            • oleh {{ $history->updater->name }}
                                        @endif
                                    </p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Submit Buttons -->
            <div class="flex flex-col sm:flex-row gap-3 sm:gap-4 justify-end pt-6 border-t border-gray-200">
                <a href="{{ route('admin.shipments.index') }}" 
                   class="w-full sm:w-auto px-6 py-2.5 sm:py-3 bg-gray-100 text-gray-700 rounded-lg font-semibold hover:bg-gray-200 transition-colors text-center text-sm sm:text-base">
                    Batal
                </a>
                <button type="submit" 
                        class="w-full sm:w-auto px-6 py-2.5 sm:py-3 bg-[#F4C430] text-white rounded-lg font-semibold hover:bg-[#E6B020] transition-colors text-sm sm:text-base shadow-sm">
                    Simpan Status
                </button>
            </div>
        </form>
    </div>
</div>

@if($shipment->type === 'cod')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const statusSelect = document.getElementById('status');
        const paymentMethodField = document.getElementById('paymentMethodField');
        const paymentMethodSelect = document.getElementById('payment_method');
        const form = document.querySelector('form');
        const codStatus = '{{ $shipment->cod_status }}';
        
        if (statusSelect && paymentMethodField && paymentMethodSelect && form) {
            statusSelect.addEventListener('change', function() {
                // Show payment method field only if status is being changed to diterima
                const selectedStatus = this.value;
                
                if (selectedStatus === 'diterima') {
                    // Check if COD is already paid
                    if (codStatus !== 'lunas') {
                        alert('Paket COD harus lunas terlebih dahulu sebelum status dapat diubah menjadi diterima. Silakan lakukan pembayaran COD terlebih dahulu.');
                        this.value = '{{ $shipment->status }}'; // Reset to current status
                        return;
                    }
                    paymentMethodField.style.display = 'block';
                    paymentMethodSelect.setAttribute('required', 'required');
                } else {
                    paymentMethodField.style.display = 'none';
                    paymentMethodSelect.removeAttribute('required');
                }
            });
            
            // Prevent form submission if COD is not paid and status is diterima
            form.addEventListener('submit', function(e) {
                if (statusSelect.value === 'diterima' && codStatus !== 'lunas') {
                    e.preventDefault();
                    alert('Paket COD harus lunas terlebih dahulu sebelum status dapat diubah menjadi diterima. Silakan lakukan pembayaran COD terlebih dahulu.');
                    return false;
                }
            });
            
            // Initial check - show if current status is diterima
            if (statusSelect.value === 'diterima') {
                paymentMethodField.style.display = 'block';
                paymentMethodSelect.setAttribute('required', 'required');
            }
        }
    });
</script>
@endif
@endsection


