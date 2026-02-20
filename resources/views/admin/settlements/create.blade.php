@extends('layouts.app')

@section('title', 'Buat Settlement - GPL Express')
@section('page-title', 'Buat Settlement')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="mb-6 lg:mb-8">
        <a href="{{ route('admin.finance.index') }}" class="text-[#F4C430] hover:underline mb-4 inline-block text-sm lg:text-base">← Kembali</a>
        <h1 class="text-2xl lg:text-3xl font-bold text-gray-900">Buat Settlement</h1>
    </div>

    <form method="POST" action="{{ route('admin.finance.settlements.store') }}" class="bg-white rounded-xl shadow-md p-6 lg:p-8">
        @csrf

        @if(request('courier_id'))
            <input type="hidden" name="courier_id" value="{{ request('courier_id') }}">
        @endif

        <!-- Pilih Kurir -->
        <div class="mb-6">
            <label for="courier_id" class="block text-sm font-medium text-gray-700 mb-2">Pilih Kurir <span class="text-red-500">*</span></label>
            <select id="courier_id" name="courier_id" required
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#F4C430] focus:border-transparent outline-none @error('courier_id') border-red-500 @enderror"
                {{ request('courier_id') ? 'disabled' : '' }}>
                <option value="">-- Pilih Kurir --</option>
                @foreach($couriers as $courier)
                    <option value="{{ $courier['id'] }}" 
                            data-balance="{{ $courier['balance'] }}"
                            {{ (request('courier_id') == $courier['id'] || old('courier_id') == $courier['id']) ? 'selected' : '' }}>
                        {{ $courier['name'] }} - Saldo: Rp {{ number_format($courier['balance'], 0, ',', '.') }}
                    </option>
                @endforeach
            </select>
            @error('courier_id')
                <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
            @enderror
            <p class="mt-2 text-sm text-gray-500" id="balance-info"></p>
        </div>

        <!-- Jumlah -->
        <div class="mb-6">
            <label for="amount" class="block text-sm font-medium text-gray-700 mb-2">Jumlah Settlement (Rp) <span class="text-red-500">*</span></label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                    <span class="text-gray-500 text-sm">Rp</span>
                </div>
                <input type="number" id="amount" name="amount" value="{{ old('amount') }}" required step="0.01" min="0.01"
                    class="w-full pl-12 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#F4C430] focus:border-transparent outline-none @error('amount') border-red-500 @enderror"
                    placeholder="0">
            </div>
            @error('amount')
                <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Metode -->
        <div class="mb-6">
            <label for="method" class="block text-sm font-medium text-gray-700 mb-2">Metode Pembayaran <span class="text-red-500">*</span></label>
            <select id="method" name="method" required
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#F4C430] focus:border-transparent outline-none @error('method') border-red-500 @enderror">
                <option value="">-- Pilih Metode --</option>
                <option value="cash" {{ old('method') === 'cash' ? 'selected' : '' }}>Cash</option>
                <option value="transfer" {{ old('method') === 'transfer' ? 'selected' : '' }}>Transfer</option>
            </select>
            @error('method')
                <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Catatan -->
        <div class="mb-6">
            <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">Catatan</label>
            <textarea id="notes" name="notes" rows="3"
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#F4C430] focus:border-transparent outline-none @error('notes') border-red-500 @enderror"
                placeholder="Catatan tambahan (opsional)">{{ old('notes') }}</textarea>
            @error('notes')
                <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Submit -->
        <div class="flex gap-4">
            <a href="{{ route('admin.finance.index') }}" class="flex-1 bg-gray-200 text-gray-700 px-8 py-3 rounded-lg font-semibold hover:bg-gray-300 transition-colors text-center">
                Batal
            </a>
            <button type="submit" class="flex-1 bg-[#F4C430] text-white px-8 py-3 rounded-lg font-semibold hover:bg-[#E6B020] transition-colors">
                Buat Settlement
            </button>
        </div>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const courierSelect = document.getElementById('courier_id');
        const amountInput = document.getElementById('amount');
        const balanceInfo = document.getElementById('balance-info');

        function updateBalanceInfo() {
            const selectedOption = courierSelect.options[courierSelect.selectedIndex];
            if (selectedOption && selectedOption.value) {
                const balance = parseFloat(selectedOption.getAttribute('data-balance')) || 0;
                balanceInfo.textContent = `Saldo tersedia: Rp ${balance.toLocaleString('id-ID')}`;
                amountInput.max = balance;
            } else {
                balanceInfo.textContent = '';
            }
        }

        courierSelect.addEventListener('change', updateBalanceInfo);
        updateBalanceInfo();
    });
</script>
@endsection


