@extends('layouts.app')

@section('title', 'Tambah Biaya Operasional - GPL Expres')
@section('page-title', 'Tambah Biaya Operasional')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
        <div>
            <h1 class="text-2xl lg:text-3xl font-bold text-gray-900">Tambah Biaya Operasional</h1>
            <p class="text-sm text-gray-600 mt-2">Tambahkan biaya operasional dalam format tabel (seperti Excel)</p>
        </div>
        <a href="{{ route('admin.finance.index') }}" class="bg-gray-900 text-white px-4 py-2.5 lg:px-6 lg:py-3 rounded-lg font-semibold hover:bg-gray-800 transition-colors text-sm lg:text-base whitespace-nowrap">
            Kembali
        </a>
    </div>

    <!-- Form -->
    <div class="bg-white rounded-xl shadow-md p-4 lg:p-6">
        <form method="POST" action="{{ route('admin.finance.operational-costs.store') }}" id="operationalCostsForm">
            @csrf
            
            <!-- Table Header -->
            <div class="mb-4 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
                <div>
                    <h2 class="text-base lg:text-lg font-semibold text-gray-900">Data Biaya Operasional</h2>
                    <p class="text-xs text-gray-500 mt-1">Klik "Tambah Baris" untuk menambah data baru</p>
                </div>
                <button type="button" onclick="addRow()" class="bg-[#F4C430] text-white px-4 py-2.5 rounded-lg font-semibold hover:bg-[#E6B020] transition-colors text-sm flex items-center gap-2 shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Tambah Baris
                </button>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto rounded-lg border border-gray-200">
                <table class="w-full" id="operationalCostsTable">
                    <thead>
                        <tr class="bg-gradient-to-r from-gray-50 to-gray-100">
                            <th class="px-3 py-2.5 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider border-b border-gray-200 w-12">No</th>
                            <th class="px-3 py-2.5 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider border-b border-gray-200 min-w-[140px]">Tanggal <span class="text-red-500">*</span></th>
                            <th class="px-3 py-2.5 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider border-b border-gray-200 min-w-[200px]">Uraian <span class="text-red-500">*</span></th>
                            <th class="px-3 py-2.5 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider border-b border-gray-200 min-w-[150px]">Cabang</th>
                            <th class="px-3 py-2.5 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider border-b border-gray-200 min-w-[130px]">Tarif <span class="text-red-500">*</span></th>
                            <th class="px-3 py-2.5 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider border-b border-gray-200 w-16">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="tableBody" class="bg-white divide-y divide-gray-100">
                        <!-- Rows will be added dynamically -->
                    </tbody>
                </table>
            </div>

            <!-- Error Messages -->
            <div id="formErrors" class="mt-4 hidden">
                <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                    <h3 class="text-sm font-medium text-red-800 mb-2">Terdapat kesalahan pada form:</h3>
                    <ul id="errorList" class="list-disc list-inside text-sm text-red-600"></ul>
                </div>
            </div>

            <!-- Info Box -->
            <div class="mt-4 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                <div class="flex items-start gap-2">
                    <svg class="w-5 h-5 text-blue-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <div class="text-xs text-blue-800">
                        <p class="font-medium mb-1">Tips:</p>
                        <ul class="list-disc list-inside space-y-0.5">
                            <li>Gunakan tombol "Tambah Baris" untuk menambah data baru</li>
                            <li>Minimal harus ada satu baris data untuk disimpan</li>
                            <li>Field bertanda <span class="text-red-500">*</span> wajib diisi</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Buttons -->
            <div class="flex gap-3 pt-4 mt-4 border-t border-gray-200">
                <a href="{{ route('admin.finance.index') }}" 
                   class="flex-1 px-5 py-2.5 border border-gray-300 rounded-lg text-gray-700 font-semibold hover:bg-gray-50 transition-colors text-center text-sm">
                    Batal
                </a>
                <button type="submit" 
                        class="flex-1 px-5 py-2.5 bg-[#F4C430] text-white rounded-lg font-semibold hover:bg-[#E6B020] transition-colors text-sm shadow-sm">
                    <span class="flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Simpan Semua
                    </span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
let rowCount = 0;
const branches = @json($branches);

// Add initial row
document.addEventListener('DOMContentLoaded', function() {
    addRow();
});

function addRow() {
    rowCount++;
    const tbody = document.getElementById('tableBody');
    const row = document.createElement('tr');
    row.className = 'hover:bg-blue-50/30 transition-colors group';
    row.dataset.rowIndex = rowCount;
    
    const branchOptions = branches.length > 0 
        ? branches.map(b => `<option value="${b.id}">${b.name}</option>`).join('')
        : '';
    
    row.innerHTML = `
        <td class="px-3 py-2.5 text-sm font-medium text-gray-600 text-center">${rowCount}</td>
        <td class="px-3 py-2.5">
            <input type="date" 
                   name="operational_costs[${rowCount}][date]" 
                   value="{{ date('Y-m-d') }}"
                   required
                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-[#F4C430] focus:border-[#F4C430] outline-none text-sm transition-all">
        </td>
        <td class="px-3 py-2.5">
            <input type="text" 
                   name="operational_costs[${rowCount}][description]" 
                   required
                   placeholder="Contoh: Bensin, Parkir, dll"
                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-[#F4C430] focus:border-[#F4C430] outline-none text-sm transition-all">
        </td>
        <td class="px-3 py-2.5">
            ${branches.length > 0 ? `
                <select name="operational_costs[${rowCount}][branch_id]" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-[#F4C430] focus:border-[#F4C430] outline-none text-sm bg-white transition-all">
                    <option value="">- Pilih -</option>
                    ${branchOptions}
                </select>
            ` : '<input type="hidden" name="operational_costs[' + rowCount + '][branch_id]" value=""><span class="text-xs text-gray-400">-</span>'}
        </td>
        <td class="px-3 py-2.5">
            <div class="relative">
                <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-500 text-xs font-medium">Rp</span>
                <input type="number" 
                       name="operational_costs[${rowCount}][amount]" 
                       step="0.01"
                       min="0.01"
                       required
                       placeholder="0"
                       class="w-full pl-9 pr-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-[#F4C430] focus:border-[#F4C430] outline-none text-sm transition-all">
            </div>
        </td>
        <td class="px-3 py-2.5 text-center">
            <button type="button" 
                    onclick="removeRow(this)" 
                    class="text-red-500 hover:text-red-700 hover:bg-red-50 p-1.5 rounded-md transition-all"
                    title="Hapus baris">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
            </button>
        </td>
    `;
    
    tbody.appendChild(row);
    updateRowNumbers();
}

function removeRow(button) {
    const row = button.closest('tr');
    if (document.getElementById('tableBody').children.length <= 1) {
        alert('Minimal harus ada satu baris data.');
        return;
    }
    row.remove();
    updateRowNumbers();
}

function updateRowNumbers() {
    const rows = document.querySelectorAll('#tableBody tr');
    rows.forEach((row, index) => {
        row.querySelector('td:first-child').textContent = index + 1;
    });
    rowCount = rows.length;
}

// Form validation
document.getElementById('operationalCostsForm').addEventListener('submit', function(e) {
    const rows = document.querySelectorAll('#tableBody tr');
    if (rows.length === 0) {
        e.preventDefault();
        alert('Minimal harus ada satu baris data.');
        return false;
    }
    
    let hasError = false;
    const errors = [];
    
    rows.forEach((row, index) => {
        const date = row.querySelector('input[name*="[date]"]').value;
        const description = row.querySelector('input[name*="[description]"]').value.trim();
        const amount = row.querySelector('input[name*="[amount]"]').value;
        
        if (!date) {
            errors.push(`Baris ${index + 1}: Tanggal harus diisi`);
            hasError = true;
        }
        if (!description) {
            errors.push(`Baris ${index + 1}: Uraian operasional harus diisi`);
            hasError = true;
        }
        if (!amount || parseFloat(amount) < 0.01) {
            errors.push(`Baris ${index + 1}: Tarif harus diisi dan minimal 0.01`);
            hasError = true;
        }
    });
    
    if (hasError) {
        e.preventDefault();
        const errorDiv = document.getElementById('formErrors');
        const errorList = document.getElementById('errorList');
        errorList.innerHTML = errors.map(err => `<li>${err}</li>`).join('');
        errorDiv.classList.remove('hidden');
        errorDiv.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        return false;
    }
});
</script>

<style>
#operationalCostsTable tbody tr:nth-child(even) {
    background-color: #fafafa;
}

#operationalCostsTable tbody tr:hover {
    background-color: #f0f9ff;
}

#operationalCostsTable tbody tr.group:hover {
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
}

#operationalCostsTable input,
#operationalCostsTable select {
    transition: all 0.15s ease;
}

#operationalCostsTable input:focus,
#operationalCostsTable select:focus {
    box-shadow: 0 0 0 3px rgba(244, 196, 48, 0.1);
    transform: translateY(-1px);
}

#operationalCostsTable input:hover:not(:focus),
#operationalCostsTable select:hover:not(:focus) {
    border-color: #d1d5db;
}

#operationalCostsTable thead th {
    position: sticky;
    top: 0;
    z-index: 10;
}
</style>
@endsection
