@extends('layouts.app')

@section('title', 'Edit Kurir - GPL Express')
@section('page-title', 'Edit Kurir')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="mb-6 lg:mb-8">
        <a href="{{ route('admin.kurirs.index') }}" class="text-[#F4C430] hover:underline mb-4 inline-block text-sm lg:text-base">← Kembali</a>
        <h1 class="text-2xl lg:text-3xl font-bold text-gray-900">Edit Kurir</h1>
    </div>

    <form method="POST" action="{{ route('admin.kurirs.update', $kurir) }}" class="bg-white rounded-xl shadow-sm p-6 lg:p-8">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Nama *</label>
                <input type="text" id="name" name="name" value="{{ old('name', $kurir->name) }}" required
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#F4C430] focus:border-transparent outline-none">
            </div>

            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email *</label>
                <input type="email" id="email" name="email" value="{{ old('email', $kurir->email) }}" required
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#F4C430] focus:border-transparent outline-none">
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-2">Password Baru (kosongkan jika tidak diubah)</label>
                <input type="password" id="password" name="password"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#F4C430] focus:border-transparent outline-none">
            </div>

            <div>
                <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">Konfirmasi Password Baru</label>
                <input type="password" id="password_confirmation" name="password_confirmation"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#F4C430] focus:border-transparent outline-none">
            </div>

            <div>
                <label for="branch_id" class="block text-sm font-medium text-gray-700 mb-2">Cabang</label>
                <select id="branch_id" name="branch_id"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#F4C430] focus:border-transparent outline-none">
                    <option value="">Pilih Cabang</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}" {{ old('branch_id', $kurir->branch_id) == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Status *</label>
                <select id="status" name="status" required
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#F4C430] focus:border-transparent outline-none">
                    <option value="active" {{ old('status', $kurir->status) === 'active' ? 'selected' : '' }}>Aktif</option>
                    <option value="inactive" {{ old('status', $kurir->status) === 'inactive' ? 'selected' : '' }}>Tidak Aktif</option>
                </select>
            </div>
        </div>

        <div class="flex gap-4 mt-8">
            <button type="submit" class="bg-[#F4C430] text-white px-8 py-3 rounded-lg font-semibold hover:bg-[#E6B020] transition-colors">
                Update
            </button>
            <a href="{{ route('admin.kurirs.index') }}" class="bg-gray-200 text-gray-700 px-8 py-3 rounded-lg font-semibold hover:bg-gray-300 transition-colors">
                Batal
            </a>
        </div>
    </form>
</div>
@endsection







