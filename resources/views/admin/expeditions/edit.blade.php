@extends('layouts.app')

@section('title', 'Edit Ekspedisi - GPL Express')
@section('page-title', 'Edit Ekspedisi')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="mb-6 lg:mb-8">
        <a href="{{ route('admin.expeditions.index') }}" class="text-[#F4C430] hover:underline mb-4 inline-block text-sm lg:text-base">← Kembali</a>
        <h1 class="text-2xl lg:text-3xl font-bold text-gray-900">Edit Ekspedisi</h1>
    </div>

    <form method="POST" action="{{ route('admin.expeditions.update', $expedition) }}" class="bg-white rounded-xl shadow-sm p-6 lg:p-8">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Nama Ekspedisi *</label>
                <input type="text" id="name" name="name" value="{{ old('name', $expedition->name) }}" required
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#F4C430] focus:border-transparent outline-none @error('name') border-red-500 @enderror"
                    placeholder="Contoh: JNE, J&T, Pos Indonesia">
                @error('name')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="code" class="block text-sm font-medium text-gray-700 mb-2">Kode</label>
                <input type="text" id="code" name="code" value="{{ old('code', $expedition->code) }}"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#F4C430] focus:border-transparent outline-none @error('code') border-red-500 @enderror"
                    placeholder="Contoh: JNE, JT, POS">
                @error('code')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Status *</label>
                <select id="status" name="status" required
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#F4C430] focus:border-transparent outline-none @error('status') border-red-500 @enderror">
                    <option value="active" {{ old('status', $expedition->status) === 'active' ? 'selected' : '' }}>Aktif</option>
                    <option value="inactive" {{ old('status', $expedition->status) === 'inactive' ? 'selected' : '' }}>Tidak Aktif</option>
                </select>
                @error('status')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="flex gap-4 mt-8">
            <button type="submit" class="bg-[#F4C430] text-white px-8 py-3 rounded-lg font-semibold hover:bg-[#E6B020] transition-colors">
                Simpan
            </button>
            <a href="{{ route('admin.expeditions.index') }}" class="bg-gray-200 text-gray-700 px-8 py-3 rounded-lg font-semibold hover:bg-gray-300 transition-colors">
                Batal
            </a>
        </div>
    </form>
</div>
@endsection
