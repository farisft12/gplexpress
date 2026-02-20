@extends('layouts.app')

@section('title', 'Tarif Harga - GPL Expres')
@section('page-title', 'Tarif Harga')

@section('content')
<div>
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6 lg:mb-8">
        <h1 class="text-2xl lg:text-3xl font-bold text-gray-900">Tarif Harga</h1>
        <a href="{{ route('admin.pricing.create') }}" class="bg-[#F4C430] text-white px-4 py-2.5 lg:px-6 lg:py-3 rounded-lg font-semibold hover:bg-[#E6B020] transition-colors text-sm lg:text-base whitespace-nowrap">
            + Tambah Tarif
        </a>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-sm p-4 lg:p-6 mb-6">
        <form method="GET" action="{{ route('admin.pricing.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Cari</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari tarif..." 
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#F4C430] focus:border-transparent outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Asal</label>
                <select name="origin_branch_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#F4C430] focus:border-transparent outline-none">
                    <option value="">Semua Asal</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}" {{ request('origin_branch_id') == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Tujuan</label>
                <select name="destination_branch_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#F4C430] focus:border-transparent outline-none">
                    <option value="">Semua Tujuan</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}" {{ request('destination_branch_id') == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end">
                <button type="submit" class="w-full bg-gray-900 text-white px-4 py-2 rounded-lg font-semibold hover:bg-gray-800 transition-colors">
                    Filter
                </button>
            </div>
        </form>
    </div>

    <!-- Pricing Table -->
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Asal → Tujuan</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Harga Dasar</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipe Layanan</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($pricings as $pricing)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ $pricing->name }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-gray-900">{{ $pricing->originBranch->name }}</div>
                                <div class="text-xs text-gray-500">→ {{ $pricing->destinationBranch->name }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-semibold text-gray-900">Rp {{ number_format($pricing->base_price, 0, ',', '.') }}</div>
                                @if($pricing->cod_fee_percentage > 0 || $pricing->cod_fee_fixed > 0)
                                    <div class="text-xs text-gray-500">COD: {{ $pricing->cod_fee_percentage }}% + Rp {{ number_format($pricing->cod_fee_fixed, 0, ',', '.') }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                    {{ ucfirst(str_replace('_', ' ', $pricing->service_type)) }}
                                </span>
                                <div class="text-xs text-gray-500 mt-1">{{ $pricing->estimated_days }} hari</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $pricing->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $pricing->status === 'active' ? 'Aktif' : 'Tidak Aktif' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <a href="{{ route('admin.pricing.edit', $pricing) }}" class="text-[#F4C430] hover:text-[#E6B020] mr-3">Edit</a>
                                <form method="POST" action="{{ route('admin.pricing.destroy', $pricing) }}" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus tarif ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-800">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                Tidak ada data tarif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($pricings->hasPages())
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $pricings->links() }}
            </div>
        @endif
    </div>
</div>
@endsection







