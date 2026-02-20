@extends('layouts.app')

@section('title', 'Edit Cabang - GPL Expres')
@section('page-title', 'Edit Cabang')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="mb-6 lg:mb-8">
        <a href="{{ route('admin.branches.index') }}" class="text-[#F4C430] hover:underline mb-4 inline-block text-sm lg:text-base">← Kembali</a>
        <h1 class="text-2xl lg:text-3xl font-bold text-gray-900">Edit Cabang</h1>
    </div>

    <form method="POST" action="{{ route('admin.branches.update', $branch) }}" class="bg-white rounded-xl shadow-sm p-6 lg:p-8">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="code" class="block text-sm font-medium text-gray-700 mb-2">Kode Cabang</label>
                <input type="text" id="code" name="code" value="{{ $branch->code }}" disabled
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg bg-gray-100 text-gray-500 cursor-not-allowed">
                <p class="mt-1 text-xs text-gray-500">Kode tidak dapat diubah</p>
            </div>

            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Nama Cabang *</label>
                <input type="text" id="name" name="name" value="{{ old('name', $branch->name) }}" required
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#F4C430] focus:border-transparent outline-none @error('name') border-red-500 @enderror">
                @error('name')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="city" class="block text-sm font-medium text-gray-700 mb-2">Kota *</label>
                <input type="text" id="city" name="city" value="{{ old('city', $branch->city) }}" required
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#F4C430] focus:border-transparent outline-none @error('city') border-red-500 @enderror">
                @error('city')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="md:col-span-2">
                <label for="address" class="block text-sm font-medium text-gray-700 mb-2">Alamat *</label>
                <textarea id="address" name="address" rows="2" required
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#F4C430] focus:border-transparent outline-none @error('address') border-red-500 @enderror">{{ old('address', $branch->address) }}</textarea>
                @error('address')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">Telepon</label>
                <input type="text" id="phone" name="phone" value="{{ old('phone', $branch->phone) }}"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#F4C430] focus:border-transparent outline-none">
            </div>

            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                <input type="email" id="email" name="email" value="{{ old('email', $branch->email) }}"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#F4C430] focus:border-transparent outline-none">
            </div>

            <div>
                <label for="manager_id" class="block text-sm font-medium text-gray-700 mb-2">Manager</label>
                <select id="manager_id" name="manager_id"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#F4C430] focus:border-transparent outline-none @error('manager_id') border-red-500 @enderror">
                    <option value="">Pilih Manager</option>
                    @foreach($managers as $manager)
                        <option value="{{ $manager->id }}" {{ old('manager_id', $branch->manager_id) == $manager->id ? 'selected' : '' }}>{{ $manager->name }} ({{ $manager->email }})</option>
                    @endforeach
                </select>
                @error('manager_id')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Status *</label>
                <select id="status" name="status" required
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#F4C430] focus:border-transparent outline-none">
                    <option value="active" {{ old('status', $branch->status) === 'active' ? 'selected' : '' }}>Aktif</option>
                    <option value="inactive" {{ old('status', $branch->status) === 'inactive' ? 'selected' : '' }}>Tidak Aktif</option>
                </select>
            </div>
        </div>

        <div class="flex gap-4 mt-8">
            <button type="submit" class="bg-[#F4C430] text-white px-8 py-3 rounded-lg font-semibold hover:bg-[#E6B020] transition-colors">
                Update
            </button>
            <a href="{{ route('admin.branches.index') }}" class="bg-gray-200 text-gray-700 px-8 py-3 rounded-lg font-semibold hover:bg-gray-300 transition-colors">
                Batal
            </a>
        </div>
    </form>
</div>
@endsection

