@extends('layouts.app')

@section('title', 'Data Barang Keluar Masuk - GPL Express')
@section('page-title', 'Data Barang Keluar Masuk')

@section('content')
<div>
    <div class="mb-6 lg:mb-8 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <h1 class="text-2xl lg:text-3xl font-bold text-gray-900">Data Barang Keluar Masuk</h1>
        @if($branches->isNotEmpty())
            <form method="GET" action="{{ route('manager.barang-keluar-masuk') }}" class="flex items-center space-x-2">
                <label for="branch_id" class="sr-only">Pilih Cabang</label>
                <select name="branch_id" id="branch_id" onchange="this.form.submit()"
                        class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Pilih Cabang</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>
                            {{ $branch->name }}
                        </option>
                    @endforeach
                </select>
            </form>
        @endif
    </div>

    <!-- Filter Date -->
    <div class="bg-white rounded-xl shadow-sm p-4 lg:p-6 mb-6">
        <form method="GET" action="{{ route('manager.barang-keluar-masuk') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            @if($branches->isNotEmpty())
                <input type="hidden" name="branch_id" value="{{ request('branch_id') }}">
            @endif
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Periode Revenue</label>
                <select name="period" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#F4C430] focus:border-transparent outline-none">
                    <option value="harian" {{ $period == 'harian' ? 'selected' : '' }}>Harian</option>
                    <option value="mingguan" {{ $period == 'mingguan' ? 'selected' : '' }}>Mingguan</option>
                    <option value="bulanan" {{ $period == 'bulanan' || !isset($period) ? 'selected' : '' }}>Bulanan</option>
                    <option value="tahunan" {{ $period == 'tahunan' ? 'selected' : '' }}>Tahunan</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Dari Tanggal</label>
                <input type="date" name="date_from" value="{{ $dateFrom }}" 
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#F4C430] focus:border-transparent outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Sampai Tanggal</label>
                <input type="date" name="date_to" value="{{ $dateTo }}" 
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#F4C430] focus:border-transparent outline-none">
            </div>
            <div class="flex items-end">
                <button type="submit" class="w-full bg-gray-900 text-white px-4 py-2 rounded-lg font-semibold hover:bg-gray-800 transition-colors">
                    Filter
                </button>
            </div>
        </form>
    </div>

    <!-- Summary -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 lg:gap-6 mb-6">
        <div class="bg-white rounded-xl shadow-sm p-5 border-l-4 border-blue-500">
            <p class="text-sm text-gray-600">Total Paket Keluar</p>
            <p class="text-2xl font-bold text-gray-900">{{ number_format($summary['total_keluar']) }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-5 border-l-4 border-green-500">
            <p class="text-sm text-gray-600">Total Paket Masuk</p>
            <p class="text-2xl font-bold text-gray-900">{{ number_format($summary['total_masuk']) }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-5 border-l-4 border-orange-500">
            <p class="text-sm text-gray-600">COD Keluar</p>
            <p class="text-2xl font-bold text-gray-900">Rp {{ number_format($summary['cod_keluar'], 0, ',', '.') }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-5 border-l-4 border-purple-500">
            <p class="text-sm text-gray-600">COD Masuk</p>
            <p class="text-2xl font-bold text-gray-900">Rp {{ number_format($summary['cod_masuk'], 0, ',', '.') }}</p>
        </div>
    </div>

    <!-- Revenue Breakdown -->
    <div class="mb-6">
        <h2 class="text-xl font-bold text-gray-900 mb-4">Revenue Breakdown ({{ ucfirst($period) }})</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 lg:gap-6">
            <div class="bg-white rounded-xl shadow-sm p-5 border-l-4 border-indigo-500">
                <p class="text-sm text-gray-600 mb-1">Revenue {{ ucfirst($period) }} Keluar</p>
                <p class="text-2xl font-bold text-gray-900">Rp {{ number_format($revenueData['revenue_keluar'] ?? 0, 0, ',', '.') }}</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm p-5 border-l-4 border-indigo-400">
                <p class="text-sm text-gray-600 mb-1">Revenue {{ ucfirst($period) }} Masuk</p>
                <p class="text-2xl font-bold text-gray-900">Rp {{ number_format($revenueData['revenue_masuk'] ?? 0, 0, ',', '.') }}</p>
            </div>
        </div>
    </div>

    <!-- Tabs -->
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="border-b border-gray-200">
            <nav class="flex -mb-px">
                <button onclick="showTab('keluar')" id="tab-keluar" class="tab-button active px-6 py-3 text-sm font-medium text-gray-500 border-b-2 border-transparent hover:text-gray-700 hover:border-gray-300">
                    Paket Keluar
                </button>
                <button onclick="showTab('masuk')" id="tab-masuk" class="tab-button px-6 py-3 text-sm font-medium text-gray-500 border-b-2 border-transparent hover:text-gray-700 hover:border-gray-300">
                    Paket Masuk
                </button>
            </nav>
        </div>

        <!-- Paket Keluar -->
        <div id="content-keluar" class="tab-content">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Resi</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tujuan</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Penerima</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($paketKeluar as $paket)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $paket->resi_number }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $paket->destinationBranch->name ?? 'N/A' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $paket->receiver_name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span @class([
                                        'px-2 py-1 text-xs font-semibold rounded-full',
                                        'bg-green-100 text-green-800' => $paket->status === 'diterima',
                                        'bg-blue-100 text-blue-800' => $paket->status === 'dalam_pengiriman',
                                        'bg-yellow-100 text-yellow-800' => !in_array($paket->status, ['diterima', 'dalam_pengiriman']),
                                    ])>
                                        {{ ucfirst(str_replace('_', ' ', $paket->status)) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $paket->created_at->format('d M Y') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center text-gray-500">Tidak ada data paket keluar</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($paketKeluar->hasPages())
                <div class="px-6 py-4 border-t border-gray-200">
                    {{ $paketKeluar->links() }}
                </div>
            @endif
        </div>

        <!-- Paket Masuk -->
        <div id="content-masuk" class="tab-content hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Resi</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Asal</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Penerima</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($paketMasuk as $paket)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $paket->resi_number }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $paket->originBranch->name ?? 'N/A' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $paket->receiver_name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span @class([
                                        'px-2 py-1 text-xs font-semibold rounded-full',
                                        'bg-green-100 text-green-800' => $paket->status === 'diterima',
                                        'bg-blue-100 text-blue-800' => $paket->status === 'dalam_pengiriman',
                                        'bg-yellow-100 text-yellow-800' => !in_array($paket->status, ['diterima', 'dalam_pengiriman']),
                                    ])>
                                        {{ ucfirst(str_replace('_', ' ', $paket->status)) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $paket->created_at->format('d M Y') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center text-gray-500">Tidak ada data paket masuk</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($paketMasuk->hasPages())
                <div class="px-6 py-4 border-t border-gray-200">
                    {{ $paketMasuk->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

<script>
function showTab(tab) {
    // Hide all content
    document.querySelectorAll('.tab-content').forEach(content => {
        content.classList.add('hidden');
    });
    
    // Remove active class from all tabs
    document.querySelectorAll('.tab-button').forEach(button => {
        button.classList.remove('active', 'border-[#F4C430]', 'text-[#F4C430]');
        button.classList.add('border-transparent', 'text-gray-500');
    });
    
    // Show selected content
    document.getElementById('content-' + tab).classList.remove('hidden');
    
    // Add active class to selected tab
    const selectedTab = document.getElementById('tab-' + tab);
    selectedTab.classList.add('active', 'border-[#F4C430]', 'text-[#F4C430]');
    selectedTab.classList.remove('border-transparent', 'text-gray-500');
}
</script>
@endsection


