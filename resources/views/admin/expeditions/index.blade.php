@extends('layouts.app')

@section('title', 'Manajemen Ekspedisi - GPL Expres')
@section('page-title', 'Manajemen Ekspedisi')

@section('content')
<div>
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6 lg:mb-8">
        <h1 class="text-2xl lg:text-3xl font-bold text-gray-900">Manajemen Ekspedisi</h1>
        <a href="{{ route('admin.expeditions.create') }}" class="bg-[#F4C430] text-white px-4 py-2.5 lg:px-6 lg:py-3 rounded-lg font-semibold hover:bg-[#E6B020] transition-colors text-sm lg:text-base whitespace-nowrap">
            + Tambah Ekspedisi
        </a>
    </div>

    @if(session('success'))
        <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg text-green-800">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->has('error'))
        <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg text-red-800">
            {{ $errors->first('error') }}
        </div>
    @endif

    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-sm p-4 lg:p-6 mb-6">
        <form method="GET" action="{{ route('admin.expeditions.index') }}" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Cari</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama atau kode..."
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#F4C430] focus:border-transparent outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                <select name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#F4C430] focus:border-transparent outline-none">
                    <option value="">Semua Status</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Aktif</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Tidak Aktif</option>
                </select>
            </div>
            <div class="flex items-end">
                <button type="submit" class="w-full bg-gray-900 text-white px-4 py-2 rounded-lg font-semibold hover:bg-gray-800 transition-colors">
                    Filter
                </button>
            </div>
        </form>
    </div>

    <!-- Expeditions Table -->
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kode</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($expeditions as $expedition)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-gray-900">{{ $expedition->name }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $expedition->code ?? '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $expedition->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $expedition->status === 'active' ? 'Aktif' : 'Tidak Aktif' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('admin.expeditions.edit', $expedition) }}"
                                       class="p-2 text-[#F4C430] hover:text-[#E6B020] hover:bg-[#F4C430]/10 rounded-lg transition-colors"
                                       title="Edit">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                    </a>
                                    <form method="POST" action="{{ route('admin.expeditions.destroy', $expedition) }}"
                                          class="inline"
                                          onsubmit="return confirm('Apakah Anda yakin ingin menghapus ekspedisi ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="p-2 text-red-600 hover:text-red-800 hover:bg-red-50 rounded-lg transition-colors"
                                                title="Hapus">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center text-gray-500">
                                Tidak ada data ekspedisi
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($expeditions->hasPages())
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $expeditions->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
