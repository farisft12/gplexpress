@extends('layouts.app')

@section('title', 'Laporan COD - GPL Express')
@section('page-title', 'Laporan COD')

@section('content')
<div>
    <div class="mb-6 lg:mb-8">
        <h1 class="text-2xl lg:text-3xl font-bold text-gray-900">Laporan COD</h1>
        <p class="text-sm text-gray-600 mt-2">Laporan paket COD dengan detail nilai dan status pembayaran</p>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-md p-6 mb-6">
        <form method="GET" action="{{ route('admin.reports.cod') }}" class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Dari Tanggal</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}" 
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#F4C430] focus:border-transparent outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Sampai Tanggal</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}" 
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#F4C430] focus:border-transparent outline-none">
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
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Group By</label>
                <select name="group_by" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#F4C430] focus:border-transparent outline-none">
                    <option value="day" {{ request('group_by', 'day') === 'day' ? 'selected' : '' }}>Harian</option>
                    <option value="week" {{ request('group_by') === 'week' ? 'selected' : '' }}>Mingguan</option>
                    <option value="month" {{ request('group_by') === 'month' ? 'selected' : '' }}>Bulanan</option>
                </select>
            </div>
            <div class="flex items-end">
                <button type="submit" class="w-full bg-[#F4C430] text-white px-4 py-2 rounded-lg font-semibold hover:bg-[#E6B020] transition-colors">
                    Filter
                </button>
            </div>
        </form>
    </div>

    <!-- Summary Total -->
    @if(isset($totals) && $totals->total_paket > 0)
    <div class="bg-[#F4C430]/10 border border-[#F4C430] rounded-xl p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Total Keseluruhan</h3>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div>
                <p class="text-xs text-gray-600 mb-1">Total Paket</p>
                <p class="text-xl font-bold text-gray-900">{{ number_format($totals->total_paket, 0, ',', '.') }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-600 mb-1">Total Nilai COD</p>
                <p class="text-xl font-bold text-gray-900">Rp {{ number_format($totals->total_nilai_cod, 0, ',', '.') }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-600 mb-1">Total Lunas</p>
                <p class="text-xl font-bold text-green-700">{{ number_format($totals->total_lunas, 0, ',', '.') }} paket</p>
                <p class="text-sm text-green-600">Rp {{ number_format($totals->total_nilai_lunas, 0, ',', '.') }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-600 mb-1">Total Belum Lunas</p>
                <p class="text-xl font-bold text-yellow-700">{{ number_format($totals->total_belum_lunas, 0, ',', '.') }} paket</p>
                <p class="text-sm text-yellow-600">Rp {{ number_format($totals->total_nilai_belum_lunas, 0, ',', '.') }}</p>
            </div>
        </div>
    </div>
    @endif

    <!-- Report Table -->
    <div class="bg-white rounded-xl shadow-md overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Periode</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jumlah Paket</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Nilai COD</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">COD Lunas</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nilai Lunas</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">COD Belum Lunas</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nilai Belum Lunas</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($reports as $report)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <a href="{{ route('admin.reports.cod.detail', array_merge(request()->except(['page']), [
                                    'group_by' => $groupBy ?? request('group_by', 'day'),
                                    'date' => $report->date ?? null,
                                    'year' => $report->year ?? null,
                                    'week' => $report->week ?? null,
                                    'month' => $report->month ?? null,
                                ])) }}" 
                                   class="text-sm font-medium text-[#F4C430] hover:text-[#E6B020] hover:underline">
                                    @if(isset($report->year) && isset($report->week))
                                        Minggu {{ $report->week }}, {{ $report->year }}
                                    @elseif(isset($report->year) && isset($report->month))
                                        {{ \Carbon\Carbon::create($report->year, $report->month, 1)->format('F Y') }}
                                    @else
                                        {{ \Carbon\Carbon::parse($report->date)->format('d M Y') }}
                                    @endif
                                    <svg class="inline-block w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </a>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ number_format($report->jumlah_paket, 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">
                                Rp {{ number_format($report->total_nilai_cod, 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-700">
                                    {{ number_format($report->cod_lunas, 0, ',', '.') }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-green-700">
                                Rp {{ number_format($report->nilai_lunas, 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-700">
                                    {{ number_format($report->cod_belum_lunas, 0, ',', '.') }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-yellow-700">
                                Rp {{ number_format($report->nilai_belum_lunas, 0, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                                Tidak ada data laporan
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $reports->links() }}
        </div>
    </div>
</div>
@endsection


