@extends('layouts.app')

@section('title', 'Performance Dashboard - GPL Expres')
@section('page-title', 'Performance Dashboard')

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Period Filter -->
    <div class="bg-white shadow-md rounded-lg p-4 mb-6">
        <form method="GET" action="{{ route('admin.performance.manager') }}" class="flex flex-wrap gap-4 items-end">
            @if(Auth::user()->isOwner())
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Cabang</label>
                    <select name="branch_id" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Semua Cabang</option>
                        @foreach(\App\Models\Branch::where('status', 'active')->get() as $branch)
                            <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>
                                {{ $branch->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif
            
            <div class="flex-1 min-w-[150px]">
                <label class="block text-sm font-medium text-gray-700 mb-2">Periode</label>
                <select name="period" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                    <option value="day" {{ request('period') == 'day' ? 'selected' : '' }}>Harian</option>
                    <option value="week" {{ request('period') == 'week' || !request('period') ? 'selected' : '' }}>Mingguan</option>
                    <option value="month" {{ request('period') == 'month' ? 'selected' : '' }}>Bulanan</option>
                </select>
            </div>
            
            <div class="flex-1 min-w-[150px]">
                <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal</label>
                <input type="date" name="date" value="{{ request('date', now()->format('Y-m-d')) }}" 
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
            </div>
            
            <button type="submit" class="bg-[#F4C430] hover:bg-[#F4C430]/90 text-white font-bold py-2 px-6 rounded-md transition-colors">
                Filter
            </button>
        </form>
    </div>

    <!-- Branch Info -->
    @if(Auth::user()->isOwner())
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
            <p class="text-sm text-blue-800">
                <strong>Cabang:</strong> {{ $branchId ? \App\Models\Branch::find($branchId)->name : 'Semua Cabang' }}
            </p>
        </div>
    @endif

    <!-- SLA Metrics -->
    @if(isset($slaMetrics) && isset($slaMetrics['total']) && $slaMetrics['total'] > 0)
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <div class="bg-white shadow-md rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-700 mb-2">SLA Achievement</h3>
            <div class="text-3xl font-bold text-[#F4C430]">
                {{ number_format($slaMetrics['on_time_percentage'] ?? 0, 1) }}%
            </div>
            <p class="text-sm text-gray-500 mt-2">
                {{ $slaMetrics['on_time'] ?? 0 }} dari {{ $slaMetrics['total'] ?? 0 }} paket tepat waktu
            </p>
        </div>
        
        <div class="bg-white shadow-md rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-700 mb-2">Total Paket</h3>
            <div class="text-3xl font-bold text-blue-600">
                {{ $slaMetrics['total'] ?? 0 }}
            </div>
            <p class="text-sm text-gray-500 mt-2">Dalam periode terpilih</p>
        </div>
        
        <div class="bg-white shadow-md rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-700 mb-2">Terlambat</h3>
            <div class="text-3xl font-bold text-red-600">
                {{ $slaMetrics['late'] ?? 0 }}
            </div>
            <p class="text-sm text-gray-500 mt-2">Paket yang terlambat</p>
        </div>
    </div>
    @endif

    <!-- Courier Rankings -->
    @if(isset($courierRankings) && count($courierRankings) > 0)
    <div class="bg-white shadow-md rounded-lg p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-700 mb-4">Ranking Kurir</h3>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rank</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Kurir</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Paket</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tepat Waktu</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Terlambat</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">SLA Rate</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($courierRankings as $index => $ranking)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            #{{ $index + 1 }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $ranking['courier_name'] ?? 'N/A' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $ranking['total'] ?? 0 }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-green-600">
                            {{ $ranking['on_time'] ?? 0 }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-red-600">
                            {{ $ranking['late'] ?? 0 }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ number_format($ranking['sla_rate'] ?? 0, 1) }}%
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @else
    <div class="bg-white shadow-md rounded-lg p-6 mb-6">
        <p class="text-gray-500 text-center">Tidak ada data performance untuk periode ini.</p>
    </div>
    @endif

    <!-- Late Delivery Reasons -->
    @if(isset($lateReasons) && count($lateReasons) > 0)
    <div class="bg-white shadow-md rounded-lg p-6">
        <h3 class="text-lg font-semibold text-gray-700 mb-4">Alasan Keterlambatan</h3>
        <div class="space-y-3">
            @foreach($lateReasons as $reason => $count)
            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                <span class="text-sm text-gray-700">{{ $reason }}</span>
                <span class="text-sm font-semibold text-red-600">{{ $count }} paket</span>
            </div>
            @endforeach
        </div>
    </div>
    @endif
</div>
@endsection




