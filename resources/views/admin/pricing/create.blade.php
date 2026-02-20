@extends('layouts.app')

@section('title', 'Tambah Tarif Harga - GPL Express')
@section('page-title', 'Tambah Tarif Harga')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="mb-6 lg:mb-8">
        <a href="{{ route('admin.pricing.index') }}" class="text-[#F4C430] hover:underline mb-4 inline-block text-sm lg:text-base">← Kembali</a>
        <h1 class="text-2xl lg:text-3xl font-bold text-gray-900">Tambah Tarif Harga</h1>
    </div>

    <form method="POST" action="{{ route('admin.pricing.store') }}" class="bg-white rounded-xl shadow-sm p-6 lg:p-8">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="md:col-span-2">
                <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Nama Tarif *</label>
                <input type="text" id="name" name="name" value="{{ old('name') }}" required
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#F4C430] focus:border-transparent outline-none @error('name') border-red-500 @enderror"
                    placeholder="Contoh: Jakarta - Bandung Reguler">
                @error('name')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="origin_branch_id" class="block text-sm font-medium text-gray-700 mb-2">Cabang Asal *</label>
                <select id="origin_branch_id" name="origin_branch_id" required
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#F4C430] focus:border-transparent outline-none @error('origin_branch_id') border-red-500 @enderror">
                    <option value="">Pilih Cabang Asal</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}" {{ old('origin_branch_id') == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                    @endforeach
                </select>
                @error('origin_branch_id')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="destination_branch_id" class="block text-sm font-medium text-gray-700 mb-2">Cabang Tujuan *</label>
                <select id="destination_branch_id" name="destination_branch_id" required
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#F4C430] focus:border-transparent outline-none @error('destination_branch_id') border-red-500 @enderror">
                    <option value="">Pilih Cabang Tujuan</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}" {{ old('destination_branch_id') == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                    @endforeach
                </select>
                @error('destination_branch_id')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="base_price" class="block text-sm font-medium text-gray-700 mb-2">Harga Dasar (Rp) *</label>
                <input type="number" id="base_price" name="base_price" value="{{ old('base_price') }}" required min="0" step="1000"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#F4C430] focus:border-transparent outline-none @error('base_price') border-red-500 @enderror">
                @error('base_price')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="service_type" class="block text-sm font-medium text-gray-700 mb-2">Tipe Layanan *</label>
                <select id="service_type" name="service_type" required
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#F4C430] focus:border-transparent outline-none">
                    <option value="reguler" {{ old('service_type') === 'reguler' ? 'selected' : '' }}>Reguler</option>
                    <option value="express" {{ old('service_type') === 'express' ? 'selected' : '' }}>Express</option>
                    <option value="same_day" {{ old('service_type') === 'same_day' ? 'selected' : '' }}>Same Day</option>
                </select>
            </div>

            <div>
                <label for="cod_fee_percentage" class="block text-sm font-medium text-gray-700 mb-2">Fee COD (%)</label>
                <input type="number" id="cod_fee_percentage" name="cod_fee_percentage" value="{{ old('cod_fee_percentage', 0) }}" min="0" max="100" step="0.01"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#F4C430] focus:border-transparent outline-none">
            </div>

            <div>
                <label for="cod_fee_fixed" class="block text-sm font-medium text-gray-700 mb-2">Fee COD Tetap (Rp)</label>
                <input type="number" id="cod_fee_fixed" name="cod_fee_fixed" value="{{ old('cod_fee_fixed', 0) }}" min="0" step="1000"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#F4C430] focus:border-transparent outline-none">
            </div>

            <div>
                <label for="estimated_days" class="block text-sm font-medium text-gray-700 mb-2">Estimasi Hari *</label>
                <input type="number" id="estimated_days" name="estimated_days" value="{{ old('estimated_days', 1) }}" required min="1"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#F4C430] focus:border-transparent outline-none">
            </div>

            <div>
                <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Status *</label>
                <select id="status" name="status" required
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#F4C430] focus:border-transparent outline-none">
                    <option value="active" {{ old('status') === 'active' ? 'selected' : '' }}>Aktif</option>
                    <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Tidak Aktif</option>
                </select>
            </div>
        </div>

        <div class="flex gap-4 mt-8">
            <button type="submit" class="bg-[#F4C430] text-white px-8 py-3 rounded-lg font-semibold hover:bg-[#E6B020] transition-colors">
                Simpan
            </button>
            <a href="{{ route('admin.pricing.index') }}" class="bg-gray-200 text-gray-700 px-8 py-3 rounded-lg font-semibold hover:bg-gray-300 transition-colors">
                Batal
            </a>
        </div>
    </form>
</div>
@endsection







