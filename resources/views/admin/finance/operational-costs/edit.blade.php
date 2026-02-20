@extends('layouts.app')

@section('title', 'Edit Biaya Operasional - GPL Express')
@section('page-title', 'Edit Biaya Operasional')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <!-- Header -->
    <div class="mb-6">
        <a href="{{ route('admin.finance.index') }}" class="text-[#F4C430] hover:underline mb-4 inline-block text-sm lg:text-base">← Kembali ke Laporan Keuangan</a>
        <h1 class="text-2xl lg:text-3xl font-bold text-gray-900">Edit Biaya Operasional</h1>
    </div>

    <!-- Form -->
    <div class="bg-white rounded-xl shadow-md p-6 lg:p-8">
        <form method="POST" action="{{ route('admin.finance.operational-costs.update', $operationalCost) }}">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="date" class="block text-sm font-medium text-gray-700 mb-2">Tanggal *</label>
                    <input type="date" id="date" name="date" value="{{ old('date', $operationalCost->date->format('Y-m-d')) }}" required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#F4C430] focus:border-transparent outline-none @error('date') border-red-500 @enderror">
                    @error('date')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                @if(Auth::user()->isOwner() || (Auth::user()->isAdmin() && $branches->count() > 1))
                    <div>
                        <label for="branch_id" class="block text-sm font-medium text-gray-700 mb-2">Cabang</label>
                        <select name="branch_id" id="branch_id"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#F4C430] focus:border-transparent outline-none @error('branch_id') border-red-500 @enderror">
                            <option value="">-- Pilih Cabang (Opsional) --</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}" {{ old('branch_id', $operationalCost->branch_id) == $branch->id ? 'selected' : '' }}>
                                    {{ $branch->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('branch_id')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                @endif

                <div class="md:col-span-2">
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Uraian Operasional *</label>
                    <textarea id="description" name="description" rows="3" required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#F4C430] focus:border-transparent outline-none @error('description') border-red-500 @enderror"
                        placeholder="Contoh: Pembelian ATK kantor, Gaji karyawan bulanan">{{ old('description', $operationalCost->description) }}</textarea>
                    @error('description')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="amount" class="block text-sm font-medium text-gray-700 mb-2">Tarif (Rp) *</label>
                    <input type="number" id="amount" name="amount" value="{{ old('amount', $operationalCost->amount) }}" required step="0.01"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#F4C430] focus:border-transparent outline-none @error('amount') border-red-500 @enderror"
                        placeholder="Contoh: 150000">
                    @error('amount')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="mt-8 flex justify-end gap-4">
                <a href="{{ route('admin.finance.index') }}"
                    class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors font-semibold">
                    Batal
                </a>
                <button type="submit"
                    class="px-6 py-3 bg-[#F4C430] text-white rounded-lg font-semibold hover:bg-[#E6B020] transition-colors">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
