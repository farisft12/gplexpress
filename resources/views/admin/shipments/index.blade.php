@extends('layouts.app')

@section('title', 'Manajemen Paket - GPL Express')
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
                            <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $shipment->cod_status === 'lunas' ? 'bg-green-100 text-green-800' : 'bg-[#F4C430]/10 text-[#F4C430]' }}">
                                COD{{ $shipment->cod_status === 'lunas' ? ' (Lunas)' : '' }}
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
                        <p class="text-xs text-gray-500 mb-1">Kurir Linehaul</p>
                        <p class="text-sm text-gray-900">{{ $shipment->courier ? $shipment->courier->name : '-' }}</p>
                        @if($shipment->destinationCourier)
                            <p class="text-xs text-gray-500 mb-1 mt-2">Kurir Delivery</p>
                            <p class="text-sm text-gray-900 font-semibold text-green-600">{{ $shipment->destinationCourier->name }}</p>
                        @endif
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
                    @php
                        // Show edit status button for incoming packages that are in transit or arrived
                        // For incoming: only dalam_pengiriman or sampai_di_cabang_tujuan
                        // For outgoing: all statuses except diterima
                        $canEditStatus = false;
                        if ($isIncoming) {
                            $canEditStatus = in_array($shipment->status, ['dalam_pengiriman', 'sampai_di_cabang_tujuan']);
                        } else {
                            $canEditStatus = $shipment->status !== 'diterima';
                        }
                    @endphp
                    @if($canEditStatus)
                        <a href="{{ route('admin.shipments.edit-status', $shipment->id) }}" 
                           class="flex-1 min-w-[80px] px-3 py-2 bg-purple-50 text-purple-600 rounded-lg text-xs font-medium text-center hover:bg-purple-100 transition-colors">
                            Edit Status
                        </a>
                    @endif
                    @php
                        // Show edit button ONLY for outgoing packages when status is pickup
                        // Incoming packages CANNOT edit data, only status
                        $canEditMobile = false;
                        if ($isOutgoing && $shipment->status === 'pickup') {
                            $canEditMobile = true;
                        }
                    @endphp
                    @if($canEditMobile)
                        <a href="{{ route('admin.shipments.edit', $shipment) }}" 
                           class="flex-1 min-w-[80px] px-3 py-2 bg-[#F4C430]/10 text-[#F4C430] rounded-lg text-xs font-medium text-center hover:bg-[#F4C430]/20 transition-colors">
                            Edit
                        </a>
                    @endif
                    @if($shipment->status === 'sampai_di_cabang_tujuan')
                        <form method="POST" action="{{ route('admin.shipments.send-notification', ['shipmentId' => $shipment->id]) }}" class="inline send-notification-form" data-shipment-id="{{ $shipment->id }}" data-receiver-name="{{ $shipment->receiver_name }}">
                            @csrf
                            <button type="submit" 
                                    class="flex-1 min-w-[80px] px-3 py-2 bg-indigo-50 text-indigo-600 rounded-lg text-xs font-medium text-center hover:bg-indigo-100 transition-colors">
                                Kirim Pesan
                            </button>
                        </form>
                    @endif
                    @if($shipment->type === 'cod' && $shipment->status === 'sampai_di_cabang_tujuan' && $shipment->cod_status === 'belum_lunas')
                        <button type="button" 
                                class="payment-btn flex-1 min-w-[80px] px-3 py-2 bg-green-600 text-white rounded-lg text-xs font-medium text-center hover:bg-green-700 transition-colors"
                                data-shipment-id="{{ $shipment->id }}"
                                data-resi-number="{{ $shipment->resi_number }}"
                                data-amount="{{ $shipment->total_cod_collectible }}">
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
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $shipment->cod_status === 'lunas' ? 'bg-green-100 text-green-800' : 'bg-[#F4C430]/10 text-[#F4C430]' }}">
                                        COD{{ $shipment->cod_status === 'lunas' ? ' (Lunas)' : '' }}
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
                                <div>
                                    <div>{{ $shipment->courier ? $shipment->courier->name : '-' }}</div>
                                    @if($shipment->destinationCourier)
                                        <div class="text-xs text-green-600 font-semibold mt-1">Kurir Delivery: {{ $shipment->destinationCourier->name }}</div>
                                    @endif
                                </div>
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
                                    @php
                                        // Show edit status button for incoming packages that are in transit or arrived
                                        // For incoming: only dalam_pengiriman or sampai_di_cabang_tujuan
                                        // For outgoing: all statuses except diterima
                                        $canEditStatusDesktop = false;
                                        if ($isIncoming) {
                                            $canEditStatusDesktop = in_array($shipment->status, ['dalam_pengiriman', 'sampai_di_cabang_tujuan']);
                                        } else {
                                            $canEditStatusDesktop = $shipment->status !== 'diterima';
                                        }
                                    @endphp
                                    @if($canEditStatusDesktop)
                                        <a href="{{ route('admin.shipments.edit-status', $shipment->id) }}"
                                           class="p-2 text-purple-600 hover:text-purple-800 hover:bg-purple-50 rounded-lg transition-colors" 
                                           title="Edit Status">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                            </svg>
                                        </a>
                                    @endif
                                    @php
                                        // Show edit button ONLY for outgoing packages when status is pickup
                                        // Incoming packages CANNOT edit data, only status
                                        $canEdit = false;
                                        if ($isOutgoing && $shipment->status === 'pickup') {
                                            $canEdit = true;
                                        }
                                    @endphp
                                    @if($canEdit)
                                        <a href="{{ route('admin.shipments.edit', $shipment) }}" 
                                           class="p-2 text-[#F4C430] hover:text-[#E6B020] hover:bg-[#F4C430]/10 rounded-lg transition-colors" 
                                           title="Edit">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                            </svg>
                                        </a>
                                    @endif
                                    @if($shipment->status === 'pickup')
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
                                        <form method="POST" action="{{ route('admin.shipments.send-notification', ['shipmentId' => $shipment->id]) }}" class="inline send-notification-form" data-shipment-id="{{ $shipment->id }}" data-receiver-name="{{ $shipment->receiver_name }}">
                                            @csrf
                                            <button type="submit" 
                                                    class="p-2 text-indigo-600 hover:text-indigo-800 hover:bg-indigo-50 rounded-lg transition-colors" 
                                                    title="Kirim Pesan ke Penerima">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                                                </svg>
                                            </button>
                                        </form>
                                    @endif
                                    @if($shipment->type === 'cod' && $shipment->status === 'sampai_di_cabang_tujuan' && $shipment->cod_status === 'belum_lunas')
                                        <button type="button"
                                                class="payment-btn p-2 text-green-600 hover:text-green-800 hover:bg-green-50 rounded-lg transition-colors" 
                                                title="Bayar COD"
                                                data-shipment-id="{{ $shipment->id }}"
                                                data-resi-number="{{ $shipment->resi_number }}"
                                                data-amount="{{ $shipment->total_cod_collectible }}">
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
                    <button onclick="window.closePaymentModal()" class="text-gray-400 hover:text-gray-600">
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

                <!-- Payment Method Selection -->
                <div id="paymentMethodSelection" class="space-y-3">
                    <button onclick="window.processCashPayment()" 
                            class="w-full bg-[#F4C430] text-white py-3 rounded-lg font-semibold hover:bg-[#E6B020] transition-colors">
                        Bayar dengan Cash
                    </button>
                    <!-- QRIS Payment Disabled -->
                </div>

                <div id="paymentStatus" class="mt-4 hidden"></div>
            </div>
        </div>
    </div>

    
    <script>
        // Payment modal variables
        let currentShipmentId = null;
        let currentResiNumber = null;
        let currentAmount = 0;

        // Define payment modal functions globally
        window.openPaymentModal = function(shipmentId, resiNumber, amount) {
            try {
                currentShipmentId = shipmentId;
                currentResiNumber = resiNumber;
                currentAmount = amount;
                
                const paymentModal = document.getElementById('paymentModal');
                const paymentResiNumber = document.getElementById('paymentResiNumber');
                const paymentAmount = document.getElementById('paymentAmount');
                const paymentMethodSelection = document.getElementById('paymentMethodSelection');
                const paymentStatus = document.getElementById('paymentStatus');
                
                if (!paymentModal) {
                    console.error('Payment modal element not found');
                    alert('Modal pembayaran tidak ditemukan. Silakan refresh halaman.');
                    return;
                }
                
                if (paymentResiNumber) {
                    paymentResiNumber.textContent = resiNumber;
                }
                
                if (paymentAmount) {
                    paymentAmount.textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(amount);
                }
                
                // Show modal
                paymentModal.classList.remove('hidden');
                paymentModal.style.display = 'flex';
                
                // Reset sections
                if (paymentMethodSelection) {
                    paymentMethodSelection.classList.remove('hidden');
                }
                if (paymentStatus) {
                    paymentStatus.classList.add('hidden');
                }
            } catch (error) {
                console.error('Error opening payment modal:', error);
                alert('Terjadi kesalahan saat membuka modal pembayaran. Silakan refresh halaman.');
            }
        };

        window.closePaymentModal = function() {
            try {
                const paymentModal = document.getElementById('paymentModal');
                if (paymentModal) {
                    paymentModal.classList.add('hidden');
                    paymentModal.style.display = 'none';
                }
            } catch (error) {
                console.error('Error closing payment modal:', error);
            }
        };

        window.processCashPayment = function() {
            const amount = new Intl.NumberFormat('id-ID').format(currentAmount);
            
            // Show beautiful confirmation notification
            if (typeof window.showPaymentConfirmNotification === 'function') {
                window.showPaymentConfirmNotification(
                    'Konfirmasi Pembayaran Cash',
                    `Konfirmasi pembayaran Cash sebesar <strong>Rp ${amount}</strong>?`,
                    function() {
                        // Show loading
                        let loadingToast = null;
                        if (typeof window.showLoadingNotification === 'function') {
                            loadingToast = window.showLoadingNotification('Memproses pembayaran...');
                        }
                    
                    fetch(`/admin/shipments/${currentShipmentId}/payment/cash`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    })
                    .then(response => {
                        // Check if response is OK
                        if (!response.ok) {
                            // Try to parse error response as JSON
                            return response.json().catch(() => {
                                // If not JSON, return error object
                                return {
                                    success: false,
                                    message: `Error ${response.status}: ${response.statusText}`
                                };
                            }).then(errorData => {
                                throw errorData;
                            });
                        }
                        
                        // Check if response is JSON
                        const contentType = response.headers.get('content-type');
                        if (contentType && contentType.includes('application/json')) {
                            return response.json();
                        } else {
                            // If not JSON, read as text to see what we got
                            return response.text().then(text => {
                                console.error('Non-JSON response:', text);
                                throw {
                                    success: false,
                                    message: 'Server returned non-JSON response'
                                };
                            });
                        }
                    })
                    .then(data => {
                        if (loadingToast && typeof window.removeToast === 'function') {
                            window.removeToast(loadingToast);
                        }
                        if (data.success) {
                            if (typeof window.showSuccessNotification === 'function') {
                                window.showSuccessNotification('Berhasil!', data.message || 'Pembayaran Cash berhasil diproses. Status paket diubah menjadi Diterima.');
                            }
                            // Close payment modal
                            if (typeof window.closePaymentModal === 'function') {
                                window.closePaymentModal();
                            }
                            // Reload page after 2 seconds
                            setTimeout(() => {
                                window.location.reload();
                            }, 2000);
                        } else {
                            if (typeof window.showErrorNotification === 'function') {
                                window.showErrorNotification('Gagal!', data.message || 'Gagal memproses pembayaran.');
                            }
                        }
                    })
                    .catch(error => {
                        if (loadingToast && typeof window.removeToast === 'function') {
                            window.removeToast(loadingToast);
                        }
                        console.error('Error:', error);
                        let errorMessage = 'Terjadi kesalahan saat memproses pembayaran.';
                        if (error && error.message) {
                            errorMessage = error.message;
                        } else if (error && typeof error === 'object' && error.success === false) {
                            errorMessage = error.message || 'Gagal memproses pembayaran.';
                        }
                        if (typeof window.showErrorNotification === 'function') {
                            window.showErrorNotification('Error!', errorMessage);
                        }
                    });
                }
            );
            } else {
                alert('Fungsi konfirmasi pembayaran tidak tersedia. Silakan refresh halaman.');
            }
        };


        // Notification functions - must be in global scope
        // Show confirmation notification
        window.showConfirmNotification = function(title, message, onConfirm) {
            const toast = document.createElement('div');
            toast.className = 'toast bg-white rounded-xl shadow-2xl border-l-4 border-blue-500 p-4 min-w-[320px] max-w-md mx-auto';
            toast.style.pointerEvents = 'auto';
            toast.innerHTML = `
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center toast-icon">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-3 flex-1">
                        <h3 class="text-sm font-semibold text-gray-900">${title}</h3>
                        <p class="mt-1 text-sm text-gray-600">${message}</p>
                        <div class="mt-4 flex gap-2">
                            <button class="confirm-btn px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">
                                Ya, Kirim
                            </button>
                            <button class="cancel-btn px-4 py-2 bg-gray-200 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-300 transition-colors">
                                Batal
                            </button>
                        </div>
                    </div>
                </div>
            `;
            
            const container = document.getElementById('toast-container');
            if (container) {
                container.appendChild(toast);
                
                toast.style.pointerEvents = 'auto';
                toast.querySelectorAll('button').forEach(btn => {
                    btn.style.pointerEvents = 'auto';
                    btn.style.cursor = 'pointer';
                });
                
                toast.querySelector('.confirm-btn').addEventListener('click', function(e) {
                    e.stopPropagation();
                    window.removeToast(toast);
                    onConfirm();
                });
                
                toast.querySelector('.cancel-btn').addEventListener('click', function(e) {
                    e.stopPropagation();
                    window.removeToast(toast);
                });
            }
        };
        
        // Show success notification
        window.showSuccessNotification = function(title, message) {
            const toast = document.createElement('div');
            toast.className = 'toast bg-white rounded-xl shadow-2xl border-l-4 border-green-500 p-4 min-w-[320px] max-w-md mx-auto';
            toast.style.pointerEvents = 'auto';
            toast.innerHTML = `
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center toast-icon">
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-3 flex-1">
                        <h3 class="text-sm font-semibold text-gray-900">${title}</h3>
                        <p class="mt-1 text-sm text-gray-600">${message}</p>
                    </div>
                    <button class="ml-4 text-gray-400 hover:text-gray-600 close-toast">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            `;
            
            const container = document.getElementById('toast-container');
            if (container) {
                container.appendChild(toast);
                
                toast.style.pointerEvents = 'auto';
                const closeBtn = toast.querySelector('.close-toast');
                if (closeBtn) {
                    closeBtn.style.pointerEvents = 'auto';
                    closeBtn.style.cursor = 'pointer';
                    closeBtn.addEventListener('click', function(e) {
                        e.stopPropagation();
                        window.removeToast(toast);
                    });
                }
                
                setTimeout(() => {
                    window.removeToast(toast);
                }, 5000);
            }
        };
        
        // Show error notification
        window.showErrorNotification = function(title, message) {
            const toast = document.createElement('div');
            toast.className = 'toast bg-white rounded-xl shadow-2xl border-l-4 border-red-500 p-4 min-w-[320px] max-w-md mx-auto';
            toast.style.pointerEvents = 'auto';
            toast.innerHTML = `
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <div class="w-10 h-10 bg-red-100 rounded-full flex items-center justify-center toast-icon">
                            <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-3 flex-1">
                        <h3 class="text-sm font-semibold text-gray-900">${title}</h3>
                        <p class="mt-1 text-sm text-gray-600">${message}</p>
                    </div>
                    <button class="ml-4 text-gray-400 hover:text-gray-600 close-toast">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            `;
            
            const container = document.getElementById('toast-container');
            if (container) {
                container.appendChild(toast);
                
                toast.style.pointerEvents = 'auto';
                const closeBtn = toast.querySelector('.close-toast');
                if (closeBtn) {
                    closeBtn.style.pointerEvents = 'auto';
                    closeBtn.style.cursor = 'pointer';
                    closeBtn.addEventListener('click', function(e) {
                        e.stopPropagation();
                        window.removeToast(toast);
                    });
                }
                
                setTimeout(() => {
                    window.removeToast(toast);
                }, 5000);
            }
        };
        
        // Show payment confirmation notification
        window.showPaymentConfirmNotification = function(title, message, onConfirm) {
            const toast = document.createElement('div');
            toast.className = 'toast bg-white rounded-xl shadow-2xl border-l-4 border-green-500 p-4 min-w-[320px] max-w-md mx-auto';
            toast.style.pointerEvents = 'auto';
            toast.innerHTML = `
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center toast-icon">
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-3 flex-1">
                        <h3 class="text-sm font-semibold text-gray-900">${title}</h3>
                        <p class="mt-1 text-sm text-gray-600">${message}</p>
                        <div class="mt-4 flex gap-2">
                            <button class="confirm-payment-btn px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-colors">
                                Ya, Konfirmasi
                            </button>
                            <button class="cancel-payment-btn px-4 py-2 bg-gray-200 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-300 transition-colors">
                                Batal
                            </button>
                        </div>
                    </div>
                </div>
            `;
            
            const container = document.getElementById('toast-container');
            if (container) {
                container.appendChild(toast);
                
                toast.style.pointerEvents = 'auto';
                toast.querySelectorAll('button').forEach(btn => {
                    btn.style.pointerEvents = 'auto';
                    btn.style.cursor = 'pointer';
                });
                
                toast.querySelector('.confirm-payment-btn').addEventListener('click', function(e) {
                    e.stopPropagation();
                    window.removeToast(toast);
                    onConfirm();
                });
                
                toast.querySelector('.cancel-payment-btn').addEventListener('click', function(e) {
                    e.stopPropagation();
                    window.removeToast(toast);
                });
            }
        };
        
        // Show loading notification
        window.showLoadingNotification = function(message) {
            const toast = document.createElement('div');
            toast.className = 'toast bg-white rounded-xl shadow-2xl border-l-4 border-blue-500 p-4 min-w-[320px] max-w-md mx-auto';
            toast.style.pointerEvents = 'auto';
            toast.innerHTML = `
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="animate-spin h-6 w-6 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-gray-900">${message}</p>
                    </div>
                </div>
            `;
            
            const container = document.getElementById('toast-container');
            if (container) {
                container.appendChild(toast);
            }
            return toast;
        };
        
        // Remove toast with animation
        window.removeToast = function(toast) {
            if (!toast) return;
            toast.classList.add('hiding');
            setTimeout(() => {
                if (toast.parentNode) {
                    toast.parentNode.removeChild(toast);
                }
            }, 300);
        };
    </script>

    <!-- Quick Actions -->
    <div class="mt-6">
        <a href="{{ route('admin.shipments.assign.form') }}" class="inline-block bg-gray-900 text-white px-6 py-3 rounded-lg font-semibold hover:bg-gray-800 transition-colors">
            Assign Paket ke Kurir
        </a>
    </div>
</div>

<!-- Toast Notification Container -->
<div id="toast-container" style="position: fixed !important; top: 20px !important; left: 50% !important; transform: translateX(-50%) !important; z-index: 9999 !important; pointer-events: none; width: 100%; max-width: 500px; display: flex; flex-direction: column; align-items: center; gap: 12px;"></div>

<style>
    #toast-container {
        position: fixed !important;
        top: 20px !important;
        left: 50% !important;
        transform: translateX(-50%) !important;
        z-index: 9999 !important;
        pointer-events: none;
        width: 100%;
        max-width: 500px;
        display: flex;
        flex-direction: column;
        align-items: center;
    }
    
    @keyframes slideInDown {
        from {
            transform: translateX(-50%) translateY(-100px);
            opacity: 0;
        }
        to {
            transform: translateX(-50%) translateY(0);
            opacity: 1;
        }
    }
    
    @keyframes slideOutUp {
        from {
            transform: translateX(-50%) translateY(0);
            opacity: 1;
        }
        to {
            transform: translateX(-50%) translateY(-100px);
            opacity: 0;
        }
    }
    
    @keyframes pulse {
        0%, 100% {
            transform: scale(1);
        }
        50% {
            transform: scale(1.05);
        }
    }
    
    .toast {
        animation: slideInDown 0.3s ease-out;
        transform: translateX(-50%) !important;
        margin-left: auto !important;
        margin-right: auto !important;
        width: 100%;
        max-width: 400px;
        pointer-events: auto !important;
    }
    
    .toast * {
        pointer-events: auto !important;
    }
    
    .toast button {
        cursor: pointer !important;
    }
    
    .toast .close-toast {
        cursor: pointer !important;
    }
    
    .toast.hiding {
        animation: slideOutUp 0.3s ease-in;
    }
    
    .toast-icon {
        animation: pulse 2s infinite;
    }
</style>

<script>
    // Close modal when clicking outside and handle payment buttons
    document.addEventListener('DOMContentLoaded', function() {
        const paymentModal = document.getElementById('paymentModal');
        if (paymentModal) {
            paymentModal.addEventListener('click', function(e) {
                if (e.target.id === 'paymentModal' || e.target === paymentModal) {
                    window.closePaymentModal();
                }
            });
        }

        // Handle payment buttons
        document.querySelectorAll('.payment-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const shipmentId = parseInt(this.getAttribute('data-shipment-id'));
                const resiNumber = this.getAttribute('data-resi-number');
                const amount = parseFloat(this.getAttribute('data-amount'));
                
                if (window.openPaymentModal && typeof window.openPaymentModal === 'function') {
                    window.openPaymentModal(shipmentId, resiNumber, amount);
                } else {
                    console.error('openPaymentModal function not found');
                    alert('Fungsi pembayaran tidak tersedia. Silakan refresh halaman.');
                }
            });
        });
    });

    // Handle send notification forms
    document.addEventListener('DOMContentLoaded', function() {
    // Handle send notification forms
    document.querySelectorAll('.send-notification-form').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const form = this;
            const receiverName = form.getAttribute('data-receiver-name');
            const shipmentId = form.getAttribute('data-shipment-id');
            const submitButton = form.querySelector('button[type="submit"]');
            const originalText = submitButton.innerHTML;
            
            // Show confirmation with custom notification
            if (typeof window.showConfirmNotification === 'function') {
                window.showConfirmNotification(
                    'Kirim Notifikasi',
                    `Kirim pesan notifikasi ke <strong>${receiverName}</strong>?`,
                    function() {
                    // Disable button and show loading
                    submitButton.disabled = true;
                    submitButton.innerHTML = '<svg class="animate-spin h-4 w-4 inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Mengirim...';
                    
                    // Create form data
                    const formData = new FormData(form);
                    
                    // Send AJAX request
                    fetch(form.action, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            if (typeof window.showSuccessNotification === 'function') {
                                window.showSuccessNotification('Berhasil!', data.message || 'Pesan notifikasi berhasil dikirim ke penerima.');
                            }
                        } else {
                            if (typeof window.showErrorNotification === 'function') {
                                window.showErrorNotification('Gagal!', data.message || 'Gagal mengirim pesan notifikasi.');
                            }
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        if (typeof window.showErrorNotification === 'function') {
                            window.showErrorNotification('Error!', 'Terjadi kesalahan saat mengirim notifikasi. Silakan coba lagi.');
                        }
                    })
                    .finally(() => {
                        // Re-enable button
                        submitButton.disabled = false;
                        submitButton.innerHTML = originalText;
                    });
                }
            );
            } else {
                alert('Fungsi notifikasi tidak tersedia. Silakan refresh halaman.');
            }
        });
    });
});
</script>

@endsection

