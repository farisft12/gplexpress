@extends('layouts.app')

@section('title', 'Manajemen Paket - GPL Expres')
@section('page-title', 'Manajemen Paket')

@section('content')
<div>
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6 lg:mb-8">
        <h1 class="text-2xl lg:text-3xl font-bold text-gray-900">Manajemen Paket</h1>
        <a href="{{ route('admin.shipments.create') }}" class="bg-[#F4C430] text-white px-4 py-2.5 lg:px-6 lg:py-3 rounded-lg font-semibold hover:bg-[#E6B020] transition-colors text-sm lg:text-base whitespace-nowrap">
            + Buat Paket Baru
        </a>
    </div>

    <!-- Direction Tabs -->
    @php
        $currentDirection = request('direction', 'all');
        $user = auth()->user();
        // Only show tabs and counts for admin/manager with branch_id
        $showDirectionTabs = $user->branch_id && !$user->isOwner();
        if ($showDirectionTabs) {
            // For outgoing: use BranchScope (filters by branch_id/origin)
            $outgoingCount = \App\Models\Shipment::where('origin_branch_id', $user->branch_id)->count();
            
            // For incoming: need to disable BranchScope because it filters by branch_id (origin)
            // but we want to count by destination_branch_id
            $incomingCount = \App\Models\Shipment::withoutGlobalScope(\App\Models\Scopes\BranchScope::class)
                ->where('destination_branch_id', $user->branch_id)
                ->count();
        }
    @endphp
    @if($showDirectionTabs)
    <div class="bg-white rounded-xl shadow-md p-4 mb-6">
        <div class="flex gap-2 border-b border-gray-200">
            <a href="{{ route('admin.shipments.index', array_merge(request()->except('direction'), ['direction' => 'all'])) }}" 
               class="px-4 py-2 text-sm font-medium {{ $currentDirection === 'all' || !$currentDirection ? 'text-[#F4C430] border-b-2 border-[#F4C430]' : 'text-gray-600 hover:text-gray-900' }}">
                Semua Paket
            </a>
            <a href="{{ route('admin.shipments.index', array_merge(request()->except('direction'), ['direction' => 'outgoing'])) }}" 
               class="px-4 py-2 text-sm font-medium {{ $currentDirection === 'outgoing' ? 'text-[#F4C430] border-b-2 border-[#F4C430]' : 'text-gray-600 hover:text-gray-900' }}">
                Paket Keluar <span class="ml-1 text-xs bg-blue-100 text-blue-800 px-2 py-0.5 rounded-full">{{ $outgoingCount }}</span>
            </a>
            <a href="{{ route('admin.shipments.index', array_merge(request()->except('direction'), ['direction' => 'incoming'])) }}" 
               class="px-4 py-2 text-sm font-medium {{ $currentDirection === 'incoming' ? 'text-[#F4C430] border-b-2 border-[#F4C430]' : 'text-gray-600 hover:text-gray-900' }}">
                Paket Masuk <span class="ml-1 text-xs bg-green-100 text-green-800 px-2 py-0.5 rounded-full">{{ $incomingCount }}</span>
            </a>
        </div>
    </div>
    @endif

    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-md p-6 mb-6">
        <form method="GET" action="{{ route('admin.shipments.index') }}" class="grid grid-cols-1 md:grid-cols-{{ $showDirectionTabs ? '5' : '4' }} gap-4">
            <input type="hidden" name="direction" value="{{ request('direction') }}">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Nomor Resi</label>
                <input type="text" name="resi" value="{{ request('resi') }}" placeholder="Cari nomor resi..." 
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#F4C430] focus:border-transparent outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                <select name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#F4C430] focus:border-transparent outline-none">
                    <option value="">Semua Status</option>
                    <option value="pickup" {{ request('status') === 'pickup' ? 'selected' : '' }}>Pickup</option>
                    <option value="diproses" {{ request('status') === 'diproses' ? 'selected' : '' }}>Diproses</option>
                    <option value="dalam_pengiriman" {{ request('status') === 'dalam_pengiriman' ? 'selected' : '' }}>Dalam Pengiriman</option>
                    <option value="sampai_di_cabang_tujuan" {{ request('status') === 'sampai_di_cabang_tujuan' ? 'selected' : '' }}>Sampai di Cabang Tujuan</option>
                    <option value="diterima" {{ request('status') === 'diterima' ? 'selected' : '' }}>Diterima</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Tipe</label>
                <select name="type" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#F4C430] focus:border-transparent outline-none">
                    <option value="">Semua Tipe</option>
                    <option value="cod" {{ request('type') === 'cod' ? 'selected' : '' }}>COD</option>
                    <option value="non_cod" {{ request('type') === 'non_cod' ? 'selected' : '' }}>Non-COD</option>
                </select>
            </div>
            @if($showDirectionTabs)
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Arah</label>
                <select name="direction" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#F4C430] focus:border-transparent outline-none">
                    <option value="all" {{ request('direction') === 'all' || !request('direction') ? 'selected' : '' }}>Semua</option>
                    <option value="outgoing" {{ request('direction') === 'outgoing' ? 'selected' : '' }}>Paket Keluar</option>
                    <option value="incoming" {{ request('direction') === 'incoming' ? 'selected' : '' }}>Paket Masuk</option>
                </select>
            </div>
            @endif
            <div class="flex items-end">
                <button type="submit" class="w-full bg-gray-900 text-white px-4 py-2 rounded-lg font-semibold hover:bg-gray-800 transition-colors">
                    Filter
                </button>
            </div>
        </form>
    </div>

    <!-- Shipments Cards (Mobile) -->
    <div class="block md:hidden space-y-4 mb-6">
        @forelse($shipments as $shipment)
            @php
                $isOutgoing = $shipment->origin_branch_id == auth()->user()->branch_id;
                $isIncoming = $shipment->destination_branch_id == auth()->user()->branch_id;
                $statusColors = [
                    'pickup' => 'bg-yellow-100 text-yellow-800',
                    'diproses' => 'bg-blue-100 text-blue-800',
                    'dalam_pengiriman' => 'bg-purple-100 text-purple-800',
                    'sampai_di_cabang_tujuan' => 'bg-orange-100 text-orange-800',
                    'diterima' => 'bg-green-100 text-green-800',
                ];
            @endphp
            <div class="bg-white rounded-xl shadow-md p-4 {{ $isIncoming && $shipment->status === 'sampai_di_cabang_tujuan' ? 'border-l-4 border-green-500' : '' }}">
                <!-- Header -->
                <div class="flex justify-between items-start mb-3">
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-1">
                            <h3 class="text-base font-bold text-gray-900">{{ $shipment->resi_number }}</h3>
                            @if($isOutgoing)
                                <span class="text-xs px-2 py-0.5 rounded-full bg-blue-100 text-blue-800 font-medium">Keluar</span>
                            @elseif($isIncoming)
                                <span class="text-xs px-2 py-0.5 rounded-full bg-green-100 text-green-800 font-medium">Masuk</span>
                            @endif
                        </div>
                        <p class="text-xs text-gray-500">{{ $shipment->created_at->format('d/m/Y H:i') }}</p>
                    </div>
                    <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $statusColors[$shipment->status] ?? 'bg-gray-100 text-gray-800' }}">
                        {{ ucfirst(str_replace('_', ' ', $shipment->status)) }}
                    </span>
                </div>

                <!-- Cabang Info -->
                <div class="mb-3 p-2 bg-gray-50 rounded-lg">
                    <div class="text-xs text-gray-600 mb-1">
                        <span class="font-medium">Asal:</span> {{ $shipment->originBranch->name ?? '-' }}
                    </div>
                    <div class="text-xs text-gray-600">
                        <span class="font-medium">Tujuan:</span> 
                        <span class="{{ $isIncoming ? 'font-semibold text-green-700' : '' }}">{{ $shipment->destinationBranch->name ?? '-' }}</span>
                    </div>
                </div>

                <!-- Pengirim & Penerima -->
                <div class="grid grid-cols-2 gap-3 mb-3">
                    <div>
                        <p class="text-xs text-gray-500 mb-1">Pengirim</p>
                        <p class="text-sm font-medium text-gray-900">{{ $shipment->sender_name }}</p>
                        <p class="text-xs text-gray-500">{{ $shipment->sender_phone ?? ($shipment->external_resi_number ? 'Resi: ' . $shipment->external_resi_number : '-') }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 mb-1">Penerima</p>
                        <p class="text-sm font-medium text-gray-900">{{ $shipment->receiver_name }}</p>
                        <p class="text-xs text-gray-500">{{ $shipment->receiver_phone }}</p>
                    </div>
                </div>

                <!-- Tipe & Kurir -->
                <div class="flex items-center justify-between mb-3 pb-3 border-b border-gray-200">
                    <div>
                        @if($shipment->type === 'cod')
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-[#F4C430]/10 text-[#F4C430]">
                                COD
                            </span>
                            <p class="text-xs text-gray-600 mt-1">Rp {{ number_format($shipment->total_cod_collectible, 0, ',', '.') }}</p>
                        @else
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-700">
                                Non-COD
                            </span>
                            @if($shipment->shipping_cost)
                                <p class="text-xs text-gray-600 mt-1">Rp {{ number_format($shipment->shipping_cost, 0, ',', '.') }}</p>
                            @endif
                        @endif
                    </div>
                    <div class="text-right">
                        <p class="text-xs text-gray-500 mb-1">Kurir</p>
                        <p class="text-sm text-gray-900">{{ $shipment->courier ? $shipment->courier->name : '-' }}</p>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('admin.shipments.show', $shipment) }}" 
                       class="flex-1 min-w-[80px] px-3 py-2 bg-blue-50 text-blue-600 rounded-lg text-xs font-medium text-center hover:bg-blue-100 transition-colors">
                        Detail
                    </a>
                    <a href="{{ route('tracking.index') }}?resi_number={{ $shipment->resi_number }}" 
                       target="_blank"
                       class="flex-1 min-w-[80px] px-3 py-2 bg-green-50 text-green-600 rounded-lg text-xs font-medium text-center hover:bg-green-100 transition-colors">
                        Lacak
                    </a>
                    <a href="{{ route('admin.shipments.print', $shipment) }}" 
                       target="_blank"
                       class="flex-1 min-w-[80px] px-3 py-2 bg-gray-50 text-gray-600 rounded-lg text-xs font-medium text-center hover:bg-gray-100 transition-colors">
                        Print
                    </a>
                    @if($shipment->status === 'pickup')
                        <a href="{{ route('admin.shipments.edit', $shipment) }}" 
                           class="flex-1 min-w-[80px] px-3 py-2 bg-[#F4C430]/10 text-[#F4C430] rounded-lg text-xs font-medium text-center hover:bg-[#F4C430]/20 transition-colors">
                            Edit
                        </a>
                    @endif
                    @if($shipment->status === 'sampai_di_cabang_tujuan')
                        <form method="POST" action="{{ route('admin.shipments.send-notification', ['shipmentId' => $shipment->id]) }}" class="inline">
                            @csrf
                            <button type="submit" 
                                    class="flex-1 min-w-[80px] px-3 py-2 bg-indigo-50 text-indigo-600 rounded-lg text-xs font-medium text-center hover:bg-indigo-100 transition-colors"
                                    onclick="return confirm('Kirim pesan notifikasi ke {{ $shipment->receiver_name }}?');">
                                Kirim Pesan
                            </button>
                        </form>
                    @endif
                    @if($shipment->type === 'cod' && $shipment->status === 'sampai_di_cabang_tujuan' && $shipment->cod_status === 'belum_lunas')
                        <button onclick="openPaymentModal({{ $shipment->id }}, '{{ $shipment->resi_number }}', {{ $shipment->total_cod_collectible }})" 
                                class="flex-1 min-w-[80px] px-3 py-2 bg-green-600 text-white rounded-lg text-xs font-medium text-center hover:bg-green-700 transition-colors">
                            Bayar
                        </button>
                    @endif
                </div>
            </div>
        @empty
            <div class="bg-white rounded-xl shadow-md p-12 text-center">
                <p class="text-gray-500">Tidak ada data paket</p>
            </div>
        @endforelse

        <!-- Pagination Mobile -->
        @if($shipments->hasPages())
            <div class="bg-white rounded-xl shadow-md p-4">
                {{ $shipments->links() }}
            </div>
        @endif
    </div>

    <!-- Shipments Table (Desktop) -->
    <div class="hidden md:block bg-white rounded-xl shadow-md overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Paket</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cabang</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pengirim</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Penerima</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipe</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kurir</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($shipments as $shipment)
                        @php
                            $isOutgoing = $shipment->origin_branch_id == auth()->user()->branch_id;
                            $isIncoming = $shipment->destination_branch_id == auth()->user()->branch_id;
                        @endphp
                        <tr class="hover:bg-gray-50 {{ $isIncoming && $shipment->status === 'sampai_di_cabang_tujuan' ? 'bg-green-50' : '' }}">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-semibold text-gray-900">{{ $shipment->resi_number }}</div>
                                <div class="text-xs text-gray-500">{{ $shipment->created_at->format('d/m/Y H:i') }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="space-y-1">
                                    @if($isOutgoing)
                                        <div class="flex items-center gap-1">
                                            <span class="text-xs px-2 py-0.5 rounded-full bg-blue-100 text-blue-800 font-medium">Keluar</span>
                                        </div>
                                        <div class="text-xs text-gray-600">
                                            <span class="font-medium">Asal:</span> {{ $shipment->originBranch->name ?? '-' }}
                                        </div>
                                        <div class="text-xs text-gray-600">
                                            <span class="font-medium">Tujuan:</span> {{ $shipment->destinationBranch->name ?? '-' }}
                                        </div>
                                    @elseif($isIncoming)
                                        <div class="flex items-center gap-1">
                                            <span class="text-xs px-2 py-0.5 rounded-full bg-green-100 text-green-800 font-medium">Masuk</span>
                                        </div>
                                        <div class="text-xs text-gray-600">
                                            <span class="font-medium">Asal:</span> {{ $shipment->originBranch->name ?? '-' }}
                                        </div>
                                        <div class="text-xs text-gray-600">
                                            <span class="font-medium">Tujuan:</span> 
                                            <span class="font-semibold text-green-700">{{ $shipment->destinationBranch->name ?? '-' }}</span>
                                        </div>
                                    @else
                                        <div class="text-xs text-gray-600">
                                            <span class="font-medium">Asal:</span> {{ $shipment->originBranch->name ?? '-' }}
                                        </div>
                                        <div class="text-xs text-gray-600">
                                            <span class="font-medium">Tujuan:</span> {{ $shipment->destinationBranch->name ?? '-' }}
                                        </div>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-gray-900">{{ $shipment->sender_name }}</div>
                                <div class="text-xs text-gray-500">{{ $shipment->sender_phone ?? ($shipment->external_resi_number ? 'Resi: ' . $shipment->external_resi_number : '-') }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-gray-900">{{ $shipment->receiver_name }}</div>
                                <div class="text-xs text-gray-500">{{ $shipment->receiver_phone }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($shipment->type === 'cod')
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-[#F4C430]/10 text-[#F4C430]">
                                        COD
                                    </span>
                                    <div class="text-xs text-gray-500 mt-1">Rp {{ number_format($shipment->total_cod_collectible, 0, ',', '.') }}</div>
                                @else
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-700">
                                        Non-COD
                                    </span>
                                    @if($shipment->shipping_cost)
                                        <div class="text-xs text-gray-500 mt-1">Rp {{ number_format($shipment->shipping_cost, 0, ',', '.') }}</div>
                                    @else
                                        <div class="text-xs text-gray-400 mt-1">Belum dihitung</div>
                                    @endif
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @php
                                    $statusColors = [
                                        'pickup' => 'bg-yellow-100 text-yellow-800',
                                        'diproses' => 'bg-blue-100 text-blue-800',
                                        'dalam_pengiriman' => 'bg-purple-100 text-purple-800',
                                        'sampai_di_cabang_tujuan' => 'bg-orange-100 text-orange-800',
                                        'diterima' => 'bg-green-100 text-green-800',
                                    ];
                                @endphp
                                <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $statusColors[$shipment->status] ?? 'bg-gray-100 text-gray-800' }}">
                                    {{ ucfirst(str_replace('_', ' ', $shipment->status)) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $shipment->courier ? $shipment->courier->name : '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-1">
                                    <a href="{{ route('tracking.index') }}?resi_number={{ $shipment->resi_number }}" 
                                       target="_blank"
                                       class="p-2 text-green-600 hover:text-green-800 hover:bg-green-50 rounded-lg transition-colors" 
                                       title="Lacak Paket">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        </svg>
                                    </a>
                                    <a href="{{ route('admin.shipments.show', $shipment) }}" 
                                       class="p-2 text-blue-600 hover:text-blue-800 hover:bg-blue-50 rounded-lg transition-colors" 
                                       title="Detail">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                    </a>
                                    <a href="{{ route('admin.shipments.print', $shipment) }}" 
                                       target="_blank"
                                       class="p-2 text-gray-600 hover:text-gray-800 hover:bg-gray-50 rounded-lg transition-colors" 
                                       title="Print Resi">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                                        </svg>
                                    </a>
                                    <a href="{{ route('admin.shipments.edit-status', $shipment->id) }}" 
                                       class="p-2 text-purple-600 hover:text-purple-800 hover:bg-purple-50 rounded-lg transition-colors" 
                                       title="Edit Status">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                        </svg>
                                    </a>
                                    @if($shipment->status === 'pickup')
                                        <a href="{{ route('admin.shipments.edit', $shipment) }}" 
                                           class="p-2 text-[#F4C430] hover:text-[#E6B020] hover:bg-[#F4C430]/10 rounded-lg transition-colors" 
                                           title="Edit">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                            </svg>
                                        </a>
                                        <form method="POST" action="{{ route('admin.shipments.destroy', $shipment) }}" 
                                              class="inline" 
                                              onsubmit="return confirm('Apakah Anda yakin ingin menghapus paket ini?');">
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
                                    @endif
                                    @if($shipment->status === 'sampai_di_cabang_tujuan')
                                        <form method="POST" action="{{ route('admin.shipments.send-notification', ['shipmentId' => $shipment->id]) }}" class="inline">
                                            @csrf
                                            <button type="submit" 
                                                    class="p-2 text-indigo-600 hover:text-indigo-800 hover:bg-indigo-50 rounded-lg transition-colors" 
                                                    title="Kirim Pesan ke Penerima"
                                                    onclick="return confirm('Kirim pesan notifikasi ke {{ $shipment->receiver_name }}?');">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                                                </svg>
                                            </button>
                                        </form>
                                    @endif
                                    @if($shipment->type === 'cod' && $shipment->status === 'sampai_di_cabang_tujuan' && $shipment->cod_status === 'belum_lunas')
                                        <button onclick="openPaymentModal({{ $shipment->id }}, '{{ $shipment->resi_number }}', {{ $shipment->total_cod_collectible }})" 
                                                class="p-2 text-green-600 hover:text-green-800 hover:bg-green-50 rounded-lg transition-colors" 
                                                title="Bayar COD">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                            </svg>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center text-gray-500">
                                Tidak ada data paket
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination Desktop -->
        @if($shipments->hasPages())
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $shipments->links() }}
            </div>
        @endif
    </div>

    <!-- Payment Modal -->
    <div id="paymentModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-xl shadow-xl max-w-md w-full max-h-[90vh] overflow-y-auto">
            <div class="p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-xl font-bold text-gray-900">Pembayaran COD</h3>
                    <button onclick="closePaymentModal()" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                
                <div class="mb-4">
                    <p class="text-sm text-gray-600">Nomor Resi</p>
                    <p class="text-lg font-semibold text-gray-900" id="paymentResiNumber"></p>
                </div>
                
                <div class="mb-6">
                    <p class="text-sm text-gray-600">Total Pembayaran</p>
                    <p class="text-2xl font-bold text-[#F4C430]" id="paymentAmount">Rp 0</p>
                </div>

                <!-- QRIS Payment Section -->
                <div id="qrisSection" class="hidden mb-6">
                    <div class="bg-gray-50 rounded-lg p-4 mb-4">
                        <p class="text-sm font-semibold text-gray-900 mb-2">Scan QR Code untuk pembayaran:</p>
                        <div class="flex justify-center mb-4">
                            <div id="qrisCodeContainer" class="bg-white p-4 rounded-lg border-2 border-gray-200">
                                <div id="qrisCodeCanvas" class="w-64 h-64 flex items-center justify-center">
                                    <div class="text-center text-gray-500">
                                        <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-gray-400"></div>
                                        <p class="mt-2 text-sm">Memuat QR Code...</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <p class="text-xs text-center text-gray-500 mb-2">Gunakan aplikasi Gojek, OVO, atau aplikasi QRIS lainnya</p>
                        <div id="sandboxNotice" class="mb-3 p-2 bg-yellow-50 border border-yellow-200 rounded text-xs text-yellow-800 hidden">
                            <strong>⚠️ Sandbox Mode:</strong> Untuk testing, gunakan <a href="https://simulator.sandbox.midtrans.com/v2/qris/index" target="_blank" class="underline font-semibold">QRIS Simulator</a> dengan URL QR Code di bawah.
                        </div>
                        <div class="mb-3">
                            <label class="block text-xs font-medium text-gray-700 mb-1">
                                QR Code Image Url untuk Simulator:
                                <span class="text-red-500">*</span>
                            </label>
                            <div class="flex gap-2">
                                <input type="text" id="qrCodeUrlText" readonly class="flex-1 p-2 text-xs border border-gray-300 rounded font-mono bg-gray-50" value="" placeholder="URL akan muncul setelah QR code dibuat..." style="font-family: 'Courier New', monospace;" onclick="this.select(); this.setSelectionRange(0, 99999);" onfocus="this.select();">
                                <button onclick="copyQrCodeUrl()" class="px-3 py-2 bg-gray-600 text-white rounded hover:bg-gray-700 text-xs whitespace-nowrap">
                                    Salin
                                </button>
                            </div>
                            <p class="text-xs text-gray-500 mt-1">
                                <strong>Cara menggunakan:</strong> Copy URL di atas (harus plain text, tidak ter-encode), paste di field "QR Code Image Url" di 
                                <a href="https://simulator.sandbox.midtrans.com/v2/qris/index" target="_blank" class="text-blue-600 underline font-semibold">QRIS Simulator</a>, 
                                lalu klik tombol "Scan QR"
                            </p>
                            <p class="text-xs text-yellow-600 mt-1 bg-yellow-50 p-2 rounded border border-yellow-200">
                                ⚠️ <strong>Penting:</strong> Pastikan URL tidak mengandung karakter <code>%</code> (ter-encode). URL harus dalam format plain text seperti: <code>https://api.sandbox.midtrans.com/v2/qris/...</code>
                            </p>
                        </div>
                        <div class="text-center space-y-2">
                            <button onclick="checkPaymentStatus()" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors text-sm">
                                Cek Status Pembayaran
                            </button>
                            <button onclick="openQrisSimulator()" id="simulatorBtn" class="hidden px-4 py-2 bg-yellow-500 text-white rounded-lg hover:bg-yellow-600 transition-colors text-sm">
                                Buka QRIS Simulator (Sandbox)
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Payment Method Selection -->
                <div id="paymentMethodSelection" class="space-y-3">
                    <button onclick="processCashPayment()" 
                            class="w-full bg-[#F4C430] text-white py-3 rounded-lg font-semibold hover:bg-[#E6B020] transition-colors">
                        Bayar dengan Cash
                    </button>
                    <button onclick="processQrisPayment()" 
                            class="w-full bg-green-600 text-white py-3 rounded-lg font-semibold hover:bg-green-700 transition-colors">
                        Bayar dengan QRIS
                    </button>
                </div>

                <div id="paymentStatus" class="mt-4 hidden"></div>
            </div>
        </div>
    </div>

    <!-- QR Code Generator Library -->
    <script src="https://cdn.jsdelivr.net/npm/qrcode@1.5.3/build/qrcode.min.js"></script>
    
    <script>
        let currentShipmentId = null;
        let currentResiNumber = null;
        let currentAmount = 0;
        let paymentCheckInterval = null;
        let qrCodeInstance = null;
        let currentQrCodeUrl = null;
        let isSandbox = false; // Will be set based on environment

        function openPaymentModal(shipmentId, resiNumber, amount) {
            currentShipmentId = shipmentId;
            currentResiNumber = resiNumber;
            currentAmount = amount;
            
            document.getElementById('paymentResiNumber').textContent = resiNumber;
            document.getElementById('paymentAmount').textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(amount);
            document.getElementById('paymentModal').classList.remove('hidden');
            document.getElementById('qrisSection').classList.add('hidden');
            document.getElementById('paymentMethodSelection').classList.remove('hidden');
            document.getElementById('paymentStatus').classList.add('hidden');
        }

        function closePaymentModal() {
            document.getElementById('paymentModal').classList.add('hidden');
            if (paymentCheckInterval) {
                clearInterval(paymentCheckInterval);
                paymentCheckInterval = null;
            }
            // Clear QR code
            if (qrCodeInstance) {
                const qrContainer = document.getElementById('qrisCodeCanvas');
                qrContainer.innerHTML = '';
                qrCodeInstance = null;
            }
            // Reset sandbox notice
            document.getElementById('sandboxNotice').classList.add('hidden');
            document.getElementById('simulatorBtn').classList.add('hidden');
            currentQrCodeUrl = null;
        }

        function copyQrCodeUrl() {
            const qrCodeUrlText = document.getElementById('qrCodeUrlText');
            
            // Check if element exists
            if (!qrCodeUrlText) {
                alert('URL QR Code belum tersedia. Silakan tunggu hingga QR code dibuat.');
                return;
            }
            
            // Get clean URL (plain text, no encoding)
            let urlToCopy = qrCodeUrlText.value ? qrCodeUrlText.value.trim() : '';
            
            // If input is empty, check if currentQrCodeUrl is available (fallback)
            if (!urlToCopy && typeof currentQrCodeUrl !== 'undefined' && currentQrCodeUrl) {
                urlToCopy = currentQrCodeUrl.trim();
                // Update input field with the URL
                qrCodeUrlText.value = urlToCopy;
            }
            
            if (!urlToCopy) {
                alert('URL QR Code kosong. Silakan tunggu hingga QR code dibuat atau refresh halaman.');
                return;
            }
            
            // Ensure URL is not encoded
            if (urlToCopy.includes('%')) {
                try {
                    urlToCopy = decodeURIComponent(urlToCopy);
                } catch (e) {
                    console.warn('Decode failed, using original:', e);
                }
            }
            
            // Try modern Clipboard API first (preferred method)
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(urlToCopy).then(() => {
                    // Show success feedback
                    showCopySuccess();
                }).catch(err => {
                    console.error('Clipboard API failed:', err);
                    // Fallback to execCommand
                    fallbackCopy(urlToCopy, qrCodeUrlText);
                });
            } else {
                // Fallback to execCommand for older browsers
                fallbackCopy(urlToCopy, qrCodeUrlText);
            }
        }
        
        function fallbackCopy(urlToCopy, inputElement) {
            // Make input temporarily editable for selection
            inputElement.readOnly = false;
            inputElement.focus();
            inputElement.select();
            inputElement.setSelectionRange(0, 99999); // For mobile devices
            
            try {
                const successful = document.execCommand('copy');
                if (successful) {
                    showCopySuccess();
                } else {
                    // If execCommand fails, show URL in alert for manual copy
                    alert('Gagal menyalin otomatis. Silakan salin manual:\n\n' + urlToCopy);
                }
            } catch (err) {
                console.error('execCommand failed:', err);
                // Show URL in alert for manual copy
                alert('Gagal menyalin otomatis. Silakan salin manual:\n\n' + urlToCopy);
            } finally {
                // Make input readonly again
                inputElement.readOnly = true;
                inputElement.blur();
            }
        }
        
        function showCopySuccess() {
            // Find the copy button and show success feedback
            const copyBtn = document.querySelector('button[onclick="copyQrCodeUrl()"]');
            if (copyBtn) {
                const originalText = copyBtn.textContent;
                const originalClass = copyBtn.className;
                copyBtn.textContent = '✓ Tersalin!';
                copyBtn.className = copyBtn.className.replace('bg-gray-600', 'bg-green-600');
                copyBtn.classList.remove('hover:bg-gray-700');
                copyBtn.classList.add('hover:bg-green-700');
                
                setTimeout(() => {
                    copyBtn.textContent = originalText;
                    copyBtn.className = originalClass;
                }, 2000);
            }
        }

        function openQrisSimulator() {
            if (currentQrCodeUrl) {
                // Open simulator in new tab
                window.open('https://simulator.sandbox.midtrans.com/v2/qris/index', '_blank');
                // Show instruction
                setTimeout(() => {
                    alert('QRIS Simulator telah dibuka. Paste URL QR Code yang sudah disalin ke field "QR Code Image Url" di simulator, lalu klik "Scan QR".');
                }, 500);
            }
        }

        function fetchQRCodeImage(qrCodeUrl) {
            // Fetch QR code image from Midtrans URL via our backend proxy
            // Use direct image URL instead of blob URL
            const qrContainer = document.getElementById('qrisCodeCanvas');
            qrContainer.innerHTML = '<div class="text-center text-gray-500"><div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-gray-400"></div><p class="mt-2 text-sm">Memuat QR Code...</p></div>';
            
            // Ensure URL is not already encoded
            // If URL is already encoded, decode it first
            let cleanUrl = qrCodeUrl;
            try {
                // Check if URL is already encoded
                if (qrCodeUrl.includes('%')) {
                    cleanUrl = decodeURIComponent(qrCodeUrl);
                }
            } catch (e) {
                // If decode fails, use original URL
                console.warn('URL decode failed, using original:', e);
                cleanUrl = qrCodeUrl;
            }
            
            console.log('QR Code URL:', cleanUrl);
            
            // Use proxy endpoint that returns image directly
            // Encode only once for query parameter
            const proxyUrl = `/admin/shipments/${currentShipmentId}/payment/qr-image?url=${encodeURIComponent(cleanUrl)}`;
            
            // Create image element and load from proxy URL
            const img = document.createElement('img');
            img.src = proxyUrl;
            img.alt = 'QR Code';
            img.className = 'w-64 h-64 mx-auto';
            img.onload = function() {
                qrContainer.innerHTML = '';
                qrContainer.appendChild(img);
                document.getElementById('qrisSection').classList.remove('hidden');
                document.getElementById('paymentStatus').innerHTML = '<div class="bg-blue-50 border border-blue-200 rounded-lg p-3 mb-4"><p class="text-sm text-blue-800">QR Code berhasil dibuat. Silakan scan untuk melakukan pembayaran.</p></div>';
                paymentCheckInterval = setInterval(checkPaymentStatus, 5000);
            };
            img.onerror = function(error) {
                console.error('Error loading QR code image:', error, 'URL:', proxyUrl);
                qrContainer.innerHTML = '';
                document.getElementById('paymentStatus').innerHTML = '<div class="bg-red-50 border border-red-200 rounded-lg p-3"><p class="text-sm text-red-800">Gagal memuat QR Code. Silakan coba lagi atau gunakan qr_string untuk generate QR code.</p></div>';
            };
        }

        function processCashPayment() {
            if (!confirm('Konfirmasi pembayaran Cash sebesar Rp ' + new Intl.NumberFormat('id-ID').format(currentAmount) + '?')) {
                return;
            }

            fetch(`/admin/shipments/${currentShipmentId}/payment/cash`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.reload();
                } else {
                    alert('Error: ' + (data.message || 'Gagal memproses pembayaran'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan saat memproses pembayaran');
            });
        }

        function processQrisPayment() {
            document.getElementById('paymentMethodSelection').classList.add('hidden');
            document.getElementById('paymentStatus').classList.remove('hidden');
            document.getElementById('paymentStatus').innerHTML = '<div class="text-center py-4"><div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-[#F4C430]"></div><p class="mt-2 text-sm text-gray-600">Memproses pembayaran QRIS...</p></div>';

            fetch(`/admin/shipments/${currentShipmentId}/payment/qris`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Set QR code URL immediately (before generating QR code)
                    const qrCodeUrlText = document.getElementById('qrCodeUrlText');
                    if (qrCodeUrlText && data.qr_code_url) {
                        // Decode URL if encoded
                        let cleanUrl = data.qr_code_url;
                        let previousUrl = '';
                        let decodeAttempts = 0;
                        const maxAttempts = 10;
                        
                        while (cleanUrl !== previousUrl && cleanUrl.includes('%') && decodeAttempts < maxAttempts) {
                            try {
                                previousUrl = cleanUrl;
                                cleanUrl = decodeURIComponent(cleanUrl);
                                decodeAttempts++;
                            } catch (e) {
                                console.warn('URL decode failed:', e);
                                break;
                            }
                        }
                        
                        // Validate URL format
                        let isValidUrl = false;
                        try {
                            const urlObj = new URL(cleanUrl);
                            isValidUrl = urlObj.protocol === 'https:' && urlObj.hostname.includes('midtrans.com');
                        } catch (e) {
                            console.error('Invalid URL format:', cleanUrl, e);
                            cleanUrl = data.qr_code_url; // Fallback to original
                        }
                        
                        // Set URL to input field immediately
                        qrCodeUrlText.value = cleanUrl;
                        currentQrCodeUrl = cleanUrl;
                        
                        // Check if sandbox mode
                        isSandbox = cleanUrl.includes('sandbox');
                        
                        // Show sandbox notice if needed
                        if (isSandbox) {
                            document.getElementById('sandboxNotice').classList.remove('hidden');
                            document.getElementById('simulatorBtn').classList.remove('hidden');
                        }
                        
                        console.log('QR Code URL set:', cleanUrl);
                    }
                    
                    // Generate QR Code from qr_string
                    if (data.qr_string) {
                        // Clear previous QR code
                        const qrContainer = document.getElementById('qrisCodeCanvas');
                        qrContainer.innerHTML = '';
                        
                        // Generate QR code using qrcode library
                        // qr_string from Midtrans is already in correct format, use it directly
                        if (typeof QRCode !== 'undefined') {
                            // Validate qr_string format (should start with 000201 for QRIS)
                            if (!data.qr_string || data.qr_string.length < 20) {
                                console.error('Invalid qr_string format', data);
                                document.getElementById('paymentStatus').innerHTML = '<div class="bg-red-50 border border-red-200 rounded-lg p-3"><p class="text-sm text-red-800">Format QR Code tidak valid. Silakan coba lagi.</p></div>';
                                return;
                            }
                            
                            // Log qr_string for debugging (first 50 chars only)
                            console.log('Generating QR from string:', data.qr_string.substring(0, 50) + '...');
                            
                            // Create canvas element for QR code
                            const canvas = document.createElement('canvas');
                            qrContainer.appendChild(canvas);
                            
                            // Generate QR code from qr_string (use directly without modification)
                            QRCode.toCanvas(canvas, data.qr_string, {
                                width: 256,
                                margin: 2,
                                color: {
                                    dark: '#000000',
                                    light: '#ffffff'
                                },
                                errorCorrectionLevel: 'H' // High error correction for QRIS
                            }, function (error) {
                                if (error) {
                                    console.error('QR Code generation error:', error);
                                    qrContainer.removeChild(canvas);
                                    // Fallback: try to fetch QR code image from URL
                                    if (data.qr_code_url) {
                                        fetchQRCodeImage(data.qr_code_url);
                                    } else {
                                        document.getElementById('paymentStatus').innerHTML = '<div class="bg-red-50 border border-red-200 rounded-lg p-3"><p class="text-sm text-red-800">Gagal membuat QR Code: ' + error.message + '. Silakan coba lagi.</p></div>';
                                    }
                                } else {
                                    console.log('QR Code generated successfully');
                                    
                                    // URL sudah di-set sebelumnya di awal response handling (baris 532)
                                    // Just show the section and status message
                                    document.getElementById('qrisSection').classList.remove('hidden');
                                    const statusMessage = isSandbox 
                                        ? '<div class="bg-blue-50 border border-blue-200 rounded-lg p-3 mb-4"><p class="text-sm text-blue-800">QR Code berhasil dibuat. <strong>Untuk testing di Sandbox:</strong> Gunakan QRIS Simulator dengan URL di atas, atau scan QR code dengan aplikasi QRIS (akan redirect ke simulator).</p></div>'
                                        : '<div class="bg-blue-50 border border-blue-200 rounded-lg p-3 mb-4"><p class="text-sm text-blue-800">QR Code berhasil dibuat. Silakan scan dengan aplikasi Gojek, OVO, atau aplikasi QRIS lainnya untuk melakukan pembayaran.</p></div>';
                                    document.getElementById('paymentStatus').innerHTML = statusMessage;
                                    
                                    // Start checking payment status every 5 seconds
                                    paymentCheckInterval = setInterval(checkPaymentStatus, 5000);
                                }
                            });
                        } else {
                            // Fallback: try to fetch QR code image from URL
                            if (data.qr_code_url) {
                                fetchQRCodeImage(data.qr_code_url);
                            } else {
                                document.getElementById('paymentStatus').innerHTML = '<div class="bg-red-50 border border-red-200 rounded-lg p-3"><p class="text-sm text-red-800">QR Code library tidak tersedia. Silakan refresh halaman.</p></div>';
                            }
                        }
                    } else if (data.qr_code_url) {
                        // Fallback: fetch QR code image from URL
                        fetchQRCodeImage(data.qr_code_url);
                    } else {
                        document.getElementById('paymentStatus').innerHTML = '<div class="bg-red-50 border border-red-200 rounded-lg p-3"><p class="text-sm text-red-800">QR Code tidak tersedia. Silakan coba lagi.</p></div>';
                    }
                } else {
                    document.getElementById('paymentStatus').innerHTML = '<div class="bg-red-50 border border-red-200 rounded-lg p-3"><p class="text-sm text-red-800">Error: ' + (data.message || 'Gagal membuat transaksi QRIS') + '</p></div>';
                    document.getElementById('paymentMethodSelection').classList.remove('hidden');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                document.getElementById('paymentStatus').innerHTML = '<div class="bg-red-50 border border-red-200 rounded-lg p-3"><p class="text-sm text-red-800">Terjadi kesalahan saat memproses pembayaran</p></div>';
                document.getElementById('paymentMethodSelection').classList.remove('hidden');
            });
        }

        function checkPaymentStatus() {
            fetch(`/admin/shipments/${currentShipmentId}/payment/status`, {
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.status === 'settlement') {
                    if (paymentCheckInterval) {
                        clearInterval(paymentCheckInterval);
                        paymentCheckInterval = null;
                    }
                    document.getElementById('paymentStatus').innerHTML = '<div class="bg-green-50 border border-green-200 rounded-lg p-3"><p class="text-sm text-green-800 font-semibold">✓ Pembayaran berhasil!</p></div>';
                    setTimeout(() => {
                        window.location.reload();
                    }, 2000);
                } else if (data.status === 'expire') {
                    if (paymentCheckInterval) {
                        clearInterval(paymentCheckInterval);
                        paymentCheckInterval = null;
                    }
                    document.getElementById('paymentStatus').innerHTML = '<div class="bg-red-50 border border-red-200 rounded-lg p-3"><p class="text-sm text-red-800">Transaksi telah kedaluwarsa. Silakan buat transaksi baru.</p></div>';
                    document.getElementById('paymentMethodSelection').classList.remove('hidden');
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        }

        // Close modal on outside click
        document.getElementById('paymentModal')?.addEventListener('click', function(e) {
            if (e.target === this) {
                closePaymentModal();
            }
        });
    </script>

    <!-- Quick Actions -->
    <div class="mt-6">
        <a href="{{ route('admin.shipments.assign.form') }}" class="inline-block bg-gray-900 text-white px-6 py-3 rounded-lg font-semibold hover:bg-gray-800 transition-colors">
            Assign Paket ke Kurir
        </a>
    </div>
</div>
@endsection

