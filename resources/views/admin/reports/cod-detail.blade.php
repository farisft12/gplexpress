@extends('layouts.app')

@section('title', 'Detail Paket COD - GPL Express')
@section('page-title', 'Detail Paket COD')

@section('content')
<div>
    <div class="mb-6 lg:mb-8">
        <a href="{{ route('admin.reports.cod', request()->except(['date', 'year', 'week', 'month', 'group_by'])) }}" 
           class="text-[#F4C430] hover:underline mb-4 inline-block text-sm lg:text-base">← Kembali ke Laporan COD</a>
        <h1 class="text-2xl lg:text-3xl font-bold text-gray-900">Detail Paket COD</h1>
        <p class="text-sm text-gray-600 mt-2">Periode: <strong>{{ $periodLabel }}</strong></p>
    </div>

    <!-- Summary -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-blue-500">
            <p class="text-xs text-gray-600 mb-1">Total Paket</p>
            <p class="text-2xl font-bold text-gray-900">{{ number_format($summary['total'], 0, ',', '.') }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-green-500">
            <p class="text-xs text-gray-600 mb-1">Lunas</p>
            <p class="text-2xl font-bold text-green-700">{{ number_format($summary['lunas'], 0, ',', '.') }} paket</p>
            <p class="text-sm text-green-600 mt-1">Rp {{ number_format($summary['nilai_lunas'], 0, ',', '.') }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-yellow-500">
            <p class="text-xs text-gray-600 mb-1">Belum Lunas</p>
            <p class="text-2xl font-bold text-yellow-700">{{ number_format($summary['belum_lunas'], 0, ',', '.') }} paket</p>
            <p class="text-sm text-yellow-600 mt-1">Rp {{ number_format($summary['nilai_belum_lunas'], 0, ',', '.') }}</p>
        </div>
    </div>

    <!-- Shipments Table -->
    <div class="bg-white rounded-xl shadow-md overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Resi</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pengirim</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Penerima</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nilai COD</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status COD</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kurir</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($shipments as $shipment)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <a href="{{ route('admin.shipments.show', $shipment->id) }}" 
                                   class="text-sm font-semibold text-[#F4C430] hover:text-[#E6B020] hover:underline">
                                    {{ $shipment->resi_number }}
                                </a>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-gray-900">{{ $shipment->sender_name }}</div>

                                <div class="text-xs text-gray-500">{{ $shipment->sender_phone ?? ($shipment->external_resi_number ? 'Resi: ' . $shipment->external_resi_number : '-') }}</div>

                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-gray-900">{{ $shipment->receiver_name }}</div>
                                <div class="text-xs text-gray-500">{{ $shipment->receiver_phone }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">

                                Rp {{ number_format($shipment->total_cod_collectible, 0, ',', '.') }}

                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($shipment->cod_status === 'lunas')
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-700">
                                        Lunas
                                    </span>
                                @else
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-700">
                                        Belum Lunas
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-700">
                                    {{ ucfirst(str_replace('_', ' ', $shipment->status)) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $shipment->courier->name ?? '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $shipment->created_at->format('d/m/Y H:i') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-8 text-center text-gray-500">
                                Tidak ada paket pada periode ini
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $shipments->appends(request()->query())->links() }}
        </div>
    </div>
</div>
@endsection

