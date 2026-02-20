@extends('layouts.app')

@section('title', 'Paket Saya - GPL Expres')
@section('page-title', 'Paket Saya')

@section('content')
<div>
    <div class="mb-6 lg:mb-8">
        <h1 class="text-2xl lg:text-3xl font-bold text-gray-900">Paket Saya</h1>
        <p class="text-sm text-gray-600 mt-2">Daftar paket yang sudah Anda ambil</p>
    </div>

    <!-- Tab Filter -->
    <div class="mb-6">
        <div class="flex space-x-2">
            <a href="{{ route('courier.my-packages', ['type' => 'all']) }}" 
               class="px-4 py-2 text-sm font-medium rounded-lg transition-colors {{ ($type ?? 'all') === 'all' ? 'bg-[#F4C430] text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                Semua
            </a>
            <a href="{{ route('courier.my-packages', ['type' => 'linehaul']) }}" 
               class="px-4 py-2 text-sm font-medium rounded-lg transition-colors {{ ($type ?? 'all') === 'linehaul' ? 'bg-blue-500 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                Kurir Linehaul
            </a>
            <a href="{{ route('courier.my-packages', ['type' => 'delivery']) }}" 
               class="px-4 py-2 text-sm font-medium rounded-lg transition-colors {{ ($type ?? 'all') === 'delivery' ? 'bg-green-500 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                Kurir Delivery
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-green-50 border-l-4 border-green-400 text-green-700 p-4 mb-6 rounded-lg" role="alert">
            <div class="flex">
                <div class="shrink-0">
                    <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium">{{ session('success') }}</p>
                </div>
            </div>
        </div>
    @endif

    @if($errors->any())
        <div class="bg-red-50 border-l-4 border-red-400 text-red-700 p-4 mb-6 rounded-lg" role="alert">
            <div class="flex">
                <div class="shrink-0">
                    <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="ml-3">
                    <ul class="list-disc list-inside text-sm">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    <!-- Bulk Action Bar -->
    <div id="bulkActionBar" class="bg-[#F4C430] text-white p-4 mb-4 rounded-lg hidden">
        <div class="flex items-center justify-between">
            <span id="selectedCount" class="font-semibold">0 paket dipilih</span>
            <div class="flex space-x-2">
                <select id="bulkStatus" class="px-3 py-2 bg-white text-gray-900 rounded-lg text-sm">
                    <option value="">Pilih Status</option>
                    <option value="dalam_pengiriman">Dalam Pengiriman</option>
                    <option value="sampai_di_cabang_tujuan">Sampai di Cabang Tujuan</option>
                    <option value="diterima">Diterima</option>
                </select>
                <button onclick="applyBulkStatus()" class="px-4 py-2 bg-white text-[#F4C430] rounded-lg font-semibold hover:bg-gray-100 transition-colors">
                    Terapkan
                </button>
                <button onclick="clearSelection()" class="px-4 py-2 bg-gray-800 text-white rounded-lg font-semibold hover:bg-gray-700 transition-colors">
                    Batal
                </button>
            </div>
        </div>
    </div>

    <!-- Packages Table -->
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full border-collapse">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <input type="checkbox" id="selectAll" onchange="toggleSelectAll(this)" class="rounded border-gray-300 text-[#F4C430] focus:ring-[#F4C430]">
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Resi</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tujuan</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Penerima</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipe</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white">
                    @forelse($packages as $package)
                        @php
                            $isLinehaul = $package->courier_id === auth()->id();
                            $isDelivery = $package->destination_courier_id === auth()->id();
                            $rowClass = $isDelivery ? 'hover:bg-green-50' : ($isLinehaul ? 'hover:bg-blue-50' : 'hover:bg-gray-50');
                        @endphp
                        <tr class="{{ $rowClass }}">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <input type="checkbox" 
                                       name="package_ids[]" 
                                       value="{{ $package->id }}" 
                                       class="package-checkbox rounded border-gray-300 text-[#F4C430] focus:ring-[#F4C430]"
                                       onchange="updateBulkActionBar()"
                                       data-status="{{ $package->status }}">
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ $package->resi_number }}</div>
                                <div class="flex gap-1 mt-1 items-center">
                                    @if($isDelivery)
                                        <span class="px-2 py-0.5 text-xs font-semibold rounded-full bg-green-100 text-green-700 flex items-center gap-1">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                            </svg>
                                            Kurir Delivery
                                        </span>
                                    @elseif($isLinehaul)
                                        <span class="px-2 py-0.5 text-xs font-semibold rounded-full bg-blue-100 text-blue-700 flex items-center gap-1">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                            Kurir Linehaul
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                {{ $package->destinationBranch->name ?? 'N/A' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                {{ $package->receiver_name }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span @class([
                                    'px-2 py-1 text-xs font-semibold rounded-full',
                                    'bg-green-100 text-green-800' => $package->status === 'diterima',
                                    'bg-blue-100 text-blue-800' => $package->status === 'dalam_pengiriman',
                                    'bg-yellow-100 text-yellow-800' => $package->status === 'diproses',
                                    'bg-purple-100 text-purple-800' => $package->status === 'sampai_di_cabang_tujuan',
                                    'bg-gray-100 text-gray-800' => !in_array($package->status, ['diterima', 'dalam_pengiriman', 'diproses', 'sampai_di_cabang_tujuan']),
                                ])>
                                    {{ ucfirst(str_replace('_', ' ', $package->status)) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                @if($package->type === 'cod')
                                    <span class="text-orange-600 font-semibold">COD</span>
                                    @if($package->cod_status === 'belum_lunas')
                                        <span class="text-red-600 text-xs">(Belum Lunas)</span>
                                    @endif
                                @else
                                    <span class="text-gray-600">Non-COD</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex items-center space-x-2">
                                    <!-- Detail Icon -->
                                    <a href="{{ route('courier.my-packages.show', $package) }}" 
                                       class="p-2 text-[#F4C430] hover:bg-[#F4C430]/10 rounded-lg transition-colors" 
                                       title="Detail">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                    </a>

                                    <!-- Edit Status Icon -->
                                    @php
                                        $isLinehaul = $package->courier_id === auth()->id();
                                        $isDelivery = $package->destination_courier_id === auth()->id();
                                        $canEditStatus = false;
                                        
                                        // Kurir Linehaul: bisa edit jika status belum sampai_di_cabang_tujuan atau diterima
                                        if ($isLinehaul && !in_array($package->status, ['sampai_di_cabang_tujuan', 'diterima'])) {
                                            $canEditStatus = true;
                                        }
                                        // Kurir Delivery: bisa edit jika status sudah sampai_di_cabang_tujuan atau dalam_pengiriman
                                        if ($isDelivery && in_array($package->status, ['sampai_di_cabang_tujuan', 'dalam_pengiriman'])) {
                                            $canEditStatus = true;
                                        }
                                    @endphp
                                    @if($canEditStatus)
                                        <button onclick="openStatusModal({{ $package->id }}, '{{ $package->resi_number }}', '{{ $package->status }}', '{{ $package->type }}', '{{ $package->cod_status ?? '' }}')" 
                                                class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors" 
                                                title="Edit Status">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                            </svg>
                                        </button>
                                    @endif

                                    <!-- Bayar COD Icon - Hanya untuk Kurir Delivery -->
                                    @if($package->type === 'cod' && $package->destination_courier_id === auth()->id() && $package->cod_status === 'belum_lunas' && in_array($package->status, ['sampai_di_cabang_tujuan', 'dalam_pengiriman']))
                                        <button onclick="openPaymentModal({{ $package->id }}, '{{ $package->resi_number }}', {{ $package->cod_amount }})" 
                                                class="p-2 text-green-600 hover:bg-green-50 rounded-lg transition-colors" 
                                                title="Bayar COD">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                            </svg>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                                Belum ada paket yang diambil. <a href="{{ route('courier.scan') }}" class="text-[#F4C430] hover:underline">Scan resi untuk mengambil paket</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        @if($packages->hasPages())
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $packages->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Status Update Modal -->
<div id="statusModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 hidden">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-xl bg-white">
        <div class="mt-3">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Edit Status Paket</h3>
                <button onclick="closeStatusModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <form id="statusForm" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Resi</label>
                    <input type="text" id="modal-resi" readonly class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-50 text-gray-600">
                </div>
                
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Status Baru <span class="text-red-500">*</span></label>
                    <select name="status" id="status" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#F4C430] focus:border-[#F4C430] outline-none">
                        <option value="">Pilih Status</option>
                    </select>
                    <p class="mt-1 text-xs text-gray-500" id="status-help"></p>
                </div>
                
                <div>
                    <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">Catatan (Opsional)</label>
                    <textarea name="notes" id="notes" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#F4C430] focus:border-[#F4C430] outline-none" placeholder="Catatan pengantaran..."></textarea>
                </div>
                
                <div>
                    <label for="location" class="block text-sm font-medium text-gray-700 mb-2">Lokasi (Opsional)</label>
                    <input type="text" name="location" id="location" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#F4C430] focus:border-[#F4C430] outline-none" placeholder="Lokasi saat ini">
                </div>
                
                <div class="flex space-x-3 pt-4">
                    <button type="button" onclick="closeStatusModal()" class="flex-1 px-4 py-2 bg-gray-100 text-gray-700 rounded-lg font-semibold hover:bg-gray-200 transition-colors">
                        Batal
                    </button>
                    <button type="submit" class="flex-1 px-4 py-2 bg-[#F4C430] text-white rounded-lg font-semibold hover:bg-[#E6B020] transition-colors">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Payment Modal -->
@include('admin.shipments.payment-modal')

<script>
let currentPackageId = null;
let currentStatus = null;
let currentType = null;
let currentCodStatus = null;

function openStatusModal(packageId, resi, status, type, codStatus) {
    currentPackageId = packageId;
    currentStatus = status;
    currentType = type;
    currentCodStatus = codStatus;
    
    document.getElementById('modal-resi').value = resi;
    document.getElementById('statusForm').action = `/courier/shipments/${packageId}/update-status`;
    
    // Clear previous options
    const statusSelect = document.getElementById('status');
    statusSelect.innerHTML = '<option value="">Pilih Status</option>';
    
    // Get available status options based on current status
    const availableStatuses = getAvailableStatuses(status, type, codStatus);
    
    availableStatuses.forEach(statusOption => {
        const option = document.createElement('option');
        option.value = statusOption.value;
        option.textContent = statusOption.label;
        statusSelect.appendChild(option);
    });
    
    // Update help text
    const helpText = document.getElementById('status-help');
    if (availableStatuses.length === 0) {
        helpText.textContent = 'Tidak ada status yang dapat diubah';
        statusSelect.disabled = true;
    } else {
        helpText.textContent = `Status saat ini: ${status.replace('_', ' ')}`;
        statusSelect.disabled = false;
    }
    
    document.getElementById('statusModal').classList.remove('hidden');
}

function closeStatusModal() {
    document.getElementById('statusModal').classList.add('hidden');
    document.getElementById('statusForm').reset();
    currentPackageId = null;
}

function getAvailableStatuses(currentStatus, type, codStatus) {
    const statuses = [];
    
    // Status flow: diproses -> dalam_pengiriman -> sampai_di_cabang_tujuan -> diterima
    if (currentStatus === 'diproses') {
        statuses.push({ value: 'dalam_pengiriman', label: 'Dalam Pengiriman' });
    } else if (currentStatus === 'dalam_pengiriman') {
        statuses.push({ value: 'sampai_di_cabang_tujuan', label: 'Sampai di Cabang Tujuan' });
    } else if (currentStatus === 'sampai_di_cabang_tujuan') {
        statuses.push({ value: 'diterima', label: 'Diterima' });
        if (type === 'cod' && codStatus === 'belum_lunas') {
            statuses.push({ value: 'cod_lunas', label: 'COD Lunas' });
        }
    }
    
    return statuses;
}

// Close modal when clicking outside
document.getElementById('statusModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeStatusModal();
    }
});

// Bulk Action Functions
function toggleSelectAll(checkbox) {
    const checkboxes = document.querySelectorAll('.package-checkbox');
    checkboxes.forEach(cb => {
        cb.checked = checkbox.checked;
    });
    updateBulkActionBar();
}

function updateBulkActionBar() {
    const checked = document.querySelectorAll('.package-checkbox:checked');
    const bulkBar = document.getElementById('bulkActionBar');
    const selectedCount = document.getElementById('selectedCount');
    
    if (checked.length > 0) {
        bulkBar.classList.remove('hidden');
        selectedCount.textContent = `${checked.length} paket dipilih`;
    } else {
        bulkBar.classList.add('hidden');
    }
    
    // Update select all checkbox
    const allCheckboxes = document.querySelectorAll('.package-checkbox');
    const selectAll = document.getElementById('selectAll');
    if (allCheckboxes.length > 0) {
        selectAll.checked = checked.length === allCheckboxes.length;
    }
}

function clearSelection() {
    document.querySelectorAll('.package-checkbox').forEach(cb => cb.checked = false);
    document.getElementById('selectAll').checked = false;
    updateBulkActionBar();
}

function applyBulkStatus() {
    const checked = document.querySelectorAll('.package-checkbox:checked');
    const status = document.getElementById('bulkStatus').value;
    
    if (checked.length === 0) {
        alert('Pilih paket terlebih dahulu');
        return;
    }
    
    if (!status) {
        alert('Pilih status terlebih dahulu');
        return;
    }
    
    const packageIds = Array.from(checked).map(cb => cb.value);
    
    // Validate status transitions
    let canUpdate = true;
    checked.forEach(cb => {
        const currentStatus = cb.getAttribute('data-status');
        if (currentStatus === 'diterima') {
            canUpdate = false;
        }
    });
    
    if (!canUpdate) {
        alert('Tidak dapat mengubah status paket yang sudah diterima');
        return;
    }
    
    if (confirm(`Apakah Anda yakin ingin mengubah status ${checked.length} paket menjadi ${status.replace('_', ' ')}?`)) {
        // Create form and submit
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route("courier.bulk-update-status") }}';
        
        const csrf = document.createElement('input');
        csrf.type = 'hidden';
        csrf.name = '_token';
        csrf.value = '{{ csrf_token() }}';
        form.appendChild(csrf);
        
        packageIds.forEach(id => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'package_ids[]';
            input.value = id;
            form.appendChild(input);
        });
        
        const statusInput = document.createElement('input');
        statusInput.type = 'hidden';
        statusInput.name = 'status';
        statusInput.value = status;
        form.appendChild(statusInput);
        
        document.body.appendChild(form);
        form.submit();
    }
}
</script>
@endsection
