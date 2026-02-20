@extends('layouts.app')

@section('title', 'Assign Paket ke Kurir - GPL Express')
@section('page-title', 'Assign Paket ke Kurir')

@section('content')
<div class="max-w-6xl mx-auto">
    <div class="mb-6 lg:mb-8">
        <a href="{{ route('admin.shipments.index') }}" class="text-[#F4C430] hover:underline mb-4 inline-block text-sm lg:text-base">← Kembali</a>
        <h1 class="text-2xl lg:text-3xl font-bold text-gray-900">Assign Paket ke Kurir</h1>
    </div>

    <form method="POST" action="{{ route('admin.shipments.assign') }}" class="bg-white rounded-xl shadow-md p-8">
        @csrf

        <!-- Pilih Kurir -->
        <div class="mb-8">
            <label for="courier_id" class="block text-sm font-medium text-gray-700 mb-2">Pilih Kurir</label>
            <select id="courier_id" name="courier_id" required
                class="w-full md:w-1/2 px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#F4C430] focus:border-transparent outline-none @error('courier_id') border-red-500 @enderror">
                <option value="">-- Pilih Kurir --</option>
                @foreach($kurirs as $kurir)
                    <option value="{{ $kurir->id }}">{{ $kurir->name }} ({{ $kurir->email }})</option>
                @endforeach
            </select>
            @error('courier_id')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Daftar Paket per Cabang -->
        <div class="mb-8">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Pilih Paket yang Akan Di-assign</h3>
            
            @if($unassignedShipments->count() > 0)
                <div class="space-y-4">
                    @foreach($unassignedShipments as $branchId => $shipments)
                        @php
                            $branch = $shipments->first()->originBranch;
                            if ($branchId === 'no_branch' || !$branch) {
                                $branchName = 'Cabang Tidak Diketahui';
                                $branchCode = '-';
                            } else {
                                $branchName = $branch->name;
                                $branchCode = $branch->code;
                            }
                        @endphp
                        
                        <div class="border border-gray-200 rounded-lg overflow-hidden">
                            <!-- Branch Header -->
                            <div class="bg-[#F4C430]/10 border-b border-gray-200 px-4 py-3">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 bg-[#F4C430]/20 rounded-lg flex items-center justify-center">
                                            <svg class="w-6 h-6 text-[#F4C430]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                            </svg>
                                        </div>
                                        <div>
                                            <h4 class="font-semibold text-gray-900">{{ $branchName }}</h4>
                                            <p class="text-xs text-gray-600">Kode: {{ $branchCode }} • {{ $shipments->count() }} paket</p>
                                        </div>
                                    </div>
                                    <div>
                                        <input type="checkbox" 
                                               class="branch-select-all rounded border-gray-300 text-[#F4C430] focus:ring-[#F4C430]"
                                               data-branch="{{ $branchId }}">
                                        <span class="ml-2 text-sm text-gray-600">Pilih Semua</span>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Shipments Table -->
                            <div class="overflow-x-auto">
                                <table class="w-full">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-4 py-3 text-left">
                                                <input type="checkbox" class="select-all-branch-{{ $branchId }} rounded border-gray-300 text-[#F4C430] focus:ring-[#F4C430]">
                                            </th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nomor Resi</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Penerima</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tujuan</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tipe</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Alamat</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200">
                                        @foreach($shipments as $shipment)
                                            <tr class="hover:bg-gray-50">
                                                <td class="px-4 py-3">
                                                    <input type="checkbox" 
                                                           name="shipment_ids[]" 
                                                           value="{{ $shipment->id }}" 
                                                           class="shipment-checkbox branch-{{ $branchId }}-checkbox rounded border-gray-300 text-[#F4C430] focus:ring-[#F4C430]">
                                                </td>
                                                <td class="px-4 py-3">
                                                    <div class="text-sm font-semibold text-gray-900">{{ $shipment->resi_number }}</div>
                                                </td>
                                                <td class="px-4 py-3">
                                                    <div class="text-sm text-gray-900">{{ $shipment->receiver_name }}</div>
                                                    <div class="text-xs text-gray-500">{{ $shipment->receiver_phone }}</div>
                                                </td>
                                                <td class="px-4 py-3">
                                                    @if($shipment->destinationBranch)
                                                        <div class="text-sm font-medium text-gray-900">{{ $shipment->destinationBranch->name }}</div>
                                                        <div class="text-xs text-gray-500">{{ $shipment->destinationBranch->code }}</div>
                                                    @else
                                                        <span class="text-xs text-gray-400">-</span>
                                                    @endif
                                                </td>
                                                <td class="px-4 py-3">
                                                    @if($shipment->type === 'cod')
                                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-[#F4C430]/10 text-[#F4C430]">
                                                            COD
                                                        </span>
                                                        @if($shipment->total_cod_collectible > 0)
                                                            <div class="text-xs text-gray-500 mt-1">Rp {{ number_format($shipment->total_cod_collectible, 0, ',', '.') }}</div>
                                                        @endif
                                                    @else
                                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-700">
                                                            Non-COD
                                                        </span>
                                                    @endif
                                                </td>
                                                <td class="px-4 py-3">
                                                    <div class="text-sm text-gray-600 max-w-xs truncate">{{ $shipment->receiver_address }}</div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="bg-gray-50 border border-gray-200 rounded-lg p-8 text-center">
                    <p class="text-gray-600">Tidak ada paket yang menunggu untuk di-assign</p>
                </div>
            @endif
        </div>

        <!-- Submit -->
        <div class="flex gap-4">
            <button type="submit" class="bg-[#F4C430] text-white px-8 py-3 rounded-lg font-semibold hover:bg-[#E6B020] transition-colors">
                Assign ke Kurir
            </button>
            <a href="{{ route('admin.shipments.index') }}" class="bg-gray-200 text-gray-700 px-8 py-3 rounded-lg font-semibold hover:bg-gray-300 transition-colors">
                Batal
            </a>
        </div>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Handle select all per branch
        document.querySelectorAll('.branch-select-all').forEach(branchSelectAll => {
            const branchId = branchSelectAll.getAttribute('data-branch');
            // Escape special characters in branchId for CSS selector
            const escapedBranchId = CSS.escape(branchId);
            const branchCheckboxes = document.querySelectorAll('.branch-' + escapedBranchId + '-checkbox');
            const selectAllBranch = document.querySelector('.select-all-branch-' + escapedBranchId);

            // Select all in branch header
            branchSelectAll.addEventListener('change', function() {
                branchCheckboxes.forEach(checkbox => {
                    checkbox.checked = this.checked;
                });
                if (selectAllBranch) {
                    selectAllBranch.checked = this.checked;
                }
            });

            // Select all in table header
            if (selectAllBranch) {
                selectAllBranch.addEventListener('change', function() {
                    branchCheckboxes.forEach(checkbox => {
                        checkbox.checked = this.checked;
                    });
                    branchSelectAll.checked = this.checked;
                });
            }

            // Update select all when individual checkbox changes
            branchCheckboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    const allChecked = Array.from(branchCheckboxes).every(cb => cb.checked);
                    if (selectAllBranch) {
                        selectAllBranch.checked = allChecked;
                    }
                    branchSelectAll.checked = allChecked;
                });
            });
        });
    });
</script>
@endsection

