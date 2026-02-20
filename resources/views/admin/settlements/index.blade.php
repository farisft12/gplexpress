@extends('layouts.app')

@section('title', 'Manajemen Settlement - GPL Expres')
@section('page-title', 'Manajemen Settlement')

@section('content')
<div>
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6 lg:mb-8">
        <div>
            <h1 class="text-2xl lg:text-3xl font-bold text-gray-900">Manajemen Settlement</h1>
            <p class="text-sm text-gray-600 mt-2">Kelola setoran COD dari kurir</p>
        </div>
        <a href="{{ route('admin.finance.settlements.create') }}" class="bg-[#F4C430] text-white px-4 py-2.5 lg:px-6 lg:py-3 rounded-lg font-semibold hover:bg-[#E6B020] transition-colors text-sm lg:text-base whitespace-nowrap">
            + Buat Settlement
        </a>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-md p-6 mb-6">
        <form method="GET" action="{{ route('admin.finance.index') }}" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                <select name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#F4C430] focus:border-transparent outline-none">
                    <option value="">Semua Status</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="confirmed" {{ request('status') === 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Kurir</label>
                <select name="courier_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#F4C430] focus:border-transparent outline-none">
                    <option value="">Semua Kurir</option>
                    @foreach($couriers as $courier)
                        <option value="{{ $courier->id }}" {{ request('courier_id') == $courier->id ? 'selected' : '' }}>
                            {{ $courier->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end">
                <button type="submit" class="w-full bg-[#F4C430] text-white px-4 py-2 rounded-lg font-semibold hover:bg-[#E6B020] transition-colors">
                    Filter
                </button>
            </div>
        </form>
    </div>

    <!-- Settlements Table -->
    <div class="bg-white rounded-xl shadow-md overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kurir</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Jumlah</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Metode</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dikonfirmasi Oleh</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($settlements as $settlement)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $settlement->created_at->format('d M Y H:i') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ $settlement->courier->name }}</div>
                                <div class="text-xs text-gray-500">{{ $settlement->courier->email }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-semibold text-gray-900">
                                Rp {{ number_format($settlement->amount, 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $settlement->method === 'cash' ? 'bg-green-100 text-green-700' : 'bg-blue-100 text-blue-700' }}">
                                    {{ strtoupper($settlement->method) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($settlement->isConfirmed())
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-700">
                                        Confirmed
                                    </span>
                                @else
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-700">
                                        Pending
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                @if($settlement->confirmedBy)
                                    {{ $settlement->confirmedBy->name }}
                                    <div class="text-xs text-gray-500">{{ $settlement->confirmed_at->format('d M Y H:i') }}</div>
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="{{ route('admin.finance.settlements.show', $settlement) }}" 
                                       class="p-2 text-blue-600 hover:text-blue-800 hover:bg-blue-50 rounded-lg transition-colors" 
                                       title="Detail">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                    </a>
                                    @if($settlement->isPending())
                                        <form method="POST" action="{{ route('admin.finance.settlements.confirm', $settlement) }}" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin mengonfirmasi settlement ini?');">
                                            @csrf
                                            <button type="submit" 
                                                    class="p-2 text-green-600 hover:text-green-800 hover:bg-green-50 rounded-lg transition-colors" 
                                                    title="Konfirmasi">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                </svg>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                                Tidak ada data settlement
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $settlements->links() }}
        </div>
    </div>
</div>
@endsection


