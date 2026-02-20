@extends('layouts.app')

@section('title', 'Assign COD ke Kurir - GPL Expres')
@section('page-title', 'Assign COD ke Kurir')

@section('content')
<div>
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6 lg:mb-8">
        <h1 class="text-2xl lg:text-3xl font-bold text-gray-900">Assign COD ke Kurir</h1>
        <a href="{{ route('admin.shipments.index') }}" class="bg-gray-900 text-white px-4 py-2.5 lg:px-6 lg:py-3 rounded-lg font-semibold hover:bg-gray-800 transition-colors text-sm lg:text-base">
            Kembali ke Daftar Paket
        </a>
    </div>

    <form method="POST" action="{{ route('admin.shipments.cod.assign') }}" id="assignCodForm">
        @csrf
        
        <!-- Select Courier -->
        <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Pilih Kurir</h2>
            <select name="courier_id" id="courier_id" required
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#F4C430] focus:border-transparent outline-none">
                <option value="">-- Pilih Kurir --</option>
                @foreach($couriers as $courier)
                    <option value="{{ $courier->id }}">{{ $courier->name }} ({{ $courier->email }})</option>
                @endforeach
            </select>
            @error('courier_id')
                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- COD Shipments List -->
        <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold text-gray-900">Paket COD yang Tersedia</h2>
                <div class="flex gap-2">
                    <button type="button" onclick="selectAll()" class="text-sm text-[#F4C430] hover:underline">
                        Pilih Semua
                    </button>
                    <span class="text-gray-400">|</span>
                    <button type="button" onclick="deselectAll()" class="text-sm text-gray-600 hover:underline">
                        Batal Pilih
                    </button>
                </div>
            </div>

            @if($codShipments->isEmpty())
                <div class="text-center py-12">
                    <p class="text-gray-500">Tidak ada paket COD yang tersedia untuk di-assign.</p>
                    <a href="{{ route('admin.shipments.index') }}" class="text-[#F4C430] hover:underline mt-2 inline-block">
                        Kembali ke Daftar Paket
                    </a>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                    <input type="checkbox" id="selectAllCheckbox" onchange="toggleAll(this)">
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Resi</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Penerima</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Alamat</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Jumlah COD</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($codShipments as $shipment)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-4">
                                        <input type="checkbox" name="shipment_ids[]" value="{{ $shipment->id }}" 
                                            class="shipment-checkbox rounded border-gray-300 text-[#F4C430] focus:ring-[#F4C430]">
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ $shipment->resi_number }}</div>
                                    </td>
                                    <td class="px-4 py-4">
                                        <div class="text-sm text-gray-900">{{ $shipment->receiver_name }}</div>
                                        <div class="text-xs text-gray-500">{{ $shipment->receiver_phone }}</div>
                                    </td>
                                    <td class="px-4 py-4">
                                        <div class="text-sm text-gray-900 max-w-xs truncate">{{ $shipment->receiver_address }}</div>
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap">
                                        <div class="text-sm font-semibold text-[#F4C430]">
                                            Rp {{ number_format($shipment->total_cod_collectible, 0, ',', '.') }}
                                        </div>
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-500">{{ $shipment->created_at->format('d M Y') }}</div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        @if($codShipments->isNotEmpty())
            <div class="flex justify-end gap-4">
                <a href="{{ route('admin.shipments.index') }}" 
                   class="bg-gray-200 text-gray-700 px-6 py-3 rounded-lg font-semibold hover:bg-gray-300 transition-colors">
                    Batal
                </a>
                <button type="submit" 
                        class="bg-[#F4C430] text-white px-6 py-3 rounded-lg font-semibold hover:bg-[#E6B020] transition-colors">
                    Assign ke Kurir
                </button>
            </div>
        @endif
    </form>
</div>

<script>
function selectAll() {
    document.querySelectorAll('.shipment-checkbox').forEach(checkbox => {
        checkbox.checked = true;
    });
    document.getElementById('selectAllCheckbox').checked = true;
}

function deselectAll() {
    document.querySelectorAll('.shipment-checkbox').forEach(checkbox => {
        checkbox.checked = false;
    });
    document.getElementById('selectAllCheckbox').checked = false;
}

function toggleAll(checkbox) {
    document.querySelectorAll('.shipment-checkbox').forEach(cb => {
        cb.checked = checkbox.checked;
    });
}

// Validate form submission
document.getElementById('assignCodForm').addEventListener('submit', function(e) {
    const courierId = document.getElementById('courier_id').value;
    const selectedShipments = document.querySelectorAll('.shipment-checkbox:checked');
    
    if (!courierId) {
        e.preventDefault();
        alert('Silakan pilih kurir terlebih dahulu.');
        return false;
    }
    
    if (selectedShipments.length === 0) {
        e.preventDefault();
        alert('Silakan pilih minimal satu paket COD.');
        return false;
    }
    
    return true;
});
</script>
@endsection
