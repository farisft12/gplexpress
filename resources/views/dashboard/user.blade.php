@extends('layouts.app')

@section('title', 'Dashboard - GPL Express')
@section('page-title', 'Dashboard')

@section('content')
<div class="container mx-auto px-4 py-6">
    <h1 class="text-3xl font-bold text-gray-900 mb-8">Selamat Datang, {{ Auth::user()->name }}!</h1>

    <!-- Quick Stats -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow-md p-6 border-t-4 border-blue-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Total Paket</p>
                    <p class="text-2xl font-bold text-gray-900 mt-2">
                        {{ \App\Models\Shipment::where(function($q) {
                            $user = Auth::user();
                            if ($user->phone) {
                                $q->where('sender_phone', $user->phone)
                                  ->orWhere('receiver_phone', $user->phone);
                            }
                            if ($user->name) {
                                $q->orWhere('sender_name', 'like', '%' . $user->name . '%')
                                  ->orWhere('receiver_name', 'like', '%' . $user->name . '%');
                            }
                        })->count() }}
                    </p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6 border-t-4 border-green-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Paket Diterima</p>
                    <p class="text-2xl font-bold text-gray-900 mt-2">
                        {{ \App\Models\Shipment::where(function($q) {
                            $user = Auth::user();
                            if ($user->phone) {
                                $q->where('sender_phone', $user->phone)
                                  ->orWhere('receiver_phone', $user->phone);
                            }
                            if ($user->name) {
                                $q->orWhere('sender_name', 'like', '%' . $user->name . '%')
                                  ->orWhere('receiver_name', 'like', '%' . $user->name . '%');
                            }
                        })->where('status', 'diterima')->count() }}
                    </p>
                </div>
                <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6 border-t-4 border-yellow-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Dalam Pengiriman</p>
                    <p class="text-2xl font-bold text-gray-900 mt-2">
                        {{ \App\Models\Shipment::where(function($q) {
                            $user = Auth::user();
                            if ($user->phone) {
                                $q->where('sender_phone', $user->phone)
                                  ->orWhere('receiver_phone', $user->phone);
                            }
                            if ($user->name) {
                                $q->orWhere('sender_name', 'like', '%' . $user->name . '%')
                                  ->orWhere('receiver_name', 'like', '%' . $user->name . '%');
                            }
                        })->whereIn('status', ['diproses', 'dalam_pengiriman', 'sampai_di_cabang_tujuan'])->count() }}
                    </p>
                </div>
                <div class="w-12 h-12 bg-yellow-100 rounded-full flex items-center justify-center">
                    <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Riwayat Paket</h3>
            <p class="text-gray-600 mb-4">Lihat semua paket yang pernah Anda kirim atau terima</p>
            <a href="{{ route('user.packages.history') }}" class="inline-block bg-[#F4C430] hover:bg-[#F4C430]/90 text-white font-bold py-2 px-6 rounded-md transition-colors">
                Lihat Riwayat
            </a>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Cek Resi</h3>
            <p class="text-gray-600 mb-4">Lacak status pengiriman paket Anda</p>
            <a href="{{ route('tracking.index') }}" class="inline-block bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-md transition-colors">
                Lacak Paket
            </a>
        </div>
    </div>

    <!-- Recent Packages -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Paket Terbaru</h3>
        @php
            $recentPackages = \App\Models\Shipment::where(function($q) {
                $user = Auth::user();
                if ($user->phone) {
                    $q->where('sender_phone', $user->phone)
                      ->orWhere('receiver_phone', $user->phone);
                }
                if ($user->name) {
                    $q->orWhere('sender_name', 'like', '%' . $user->name . '%')
                      ->orWhere('receiver_name', 'like', '%' . $user->name . '%');
                }
            })->latest()->limit(5)->get();
        @endphp

        @if($recentPackages->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No. Resi</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($recentPackages as $package)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                {{ $package->resi_number }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs font-semibold rounded-full 
                                    @if($package->status == 'diterima') bg-green-100 text-green-800
                                    @elseif(in_array($package->status, ['diproses', 'dalam_pengiriman'])) bg-blue-100 text-blue-800
                                    @else bg-gray-100 text-gray-800
                                    @endif">
                                    {{ ucfirst(str_replace('_', ' ', $package->status)) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $package->created_at->format('d M Y') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <a href="{{ route('user.packages.show', $package) }}" class="text-[#F4C430] hover:underline">
                                    Detail
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="text-gray-500 text-center py-8">Belum ada paket</p>
        @endif
    </div>
</div>
@endsection




