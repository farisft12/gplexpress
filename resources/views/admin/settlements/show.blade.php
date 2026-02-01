@extends('layouts.app')

@section('title', 'Detail Settlement - GPL Expres')
@section('page-title', 'Detail Settlement')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="mb-6 lg:mb-8">
        <a href="{{ route('admin.finance.index') }}" class="text-[#F4C430] hover:underline mb-4 inline-block text-sm lg:text-base">← Kembali</a>
        <h1 class="text-2xl lg:text-3xl font-bold text-gray-900">Detail Settlement</h1>
    </div>

    <div class="bg-white rounded-xl shadow-md p-6 lg:p-8 space-y-6">
        <!-- Settlement Info -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Kurir</label>
                <p class="text-lg font-semibold text-gray-900">{{ $settlement->courier->name }}</p>
                <p class="text-sm text-gray-600">{{ $settlement->courier->email }}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Jumlah</label>
                <p class="text-2xl font-bold text-gray-900">Rp {{ number_format($settlement->amount, 0, ',', '.') }}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Metode</label>
                <span class="inline-block px-3 py-1 text-sm font-semibold rounded-full {{ $settlement->method === 'cash' ? 'bg-green-100 text-green-700' : 'bg-blue-100 text-blue-700' }}">
                    {{ strtoupper($settlement->method) }}
                </span>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                @if($settlement->isConfirmed())
                    <span class="inline-block px-3 py-1 text-sm font-semibold rounded-full bg-green-100 text-green-700">
                        Confirmed
                    </span>
                @else
                    <span class="inline-block px-3 py-1 text-sm font-semibold rounded-full bg-yellow-100 text-yellow-700">
                        Pending
                    </span>
                @endif
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Dibuat</label>
                <p class="text-sm text-gray-900">{{ $settlement->created_at->format('d M Y H:i:s') }}</p>
            </div>
            @if($settlement->isConfirmed())
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Dikonfirmasi Oleh</label>
                    <p class="text-sm font-semibold text-gray-900">{{ $settlement->confirmedBy->name }}</p>
                    <p class="text-xs text-gray-600">{{ $settlement->confirmed_at->format('d M Y H:i:s') }}</p>
                </div>
            @endif
        </div>

        @if($settlement->notes)
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Catatan</label>
                <p class="text-sm text-gray-900 bg-gray-50 p-3 rounded-lg">{{ $settlement->notes }}</p>
            </div>
        @endif

        <!-- Actions -->
        @if($settlement->isPending())
            <div class="pt-6 border-t border-gray-200">
                <form method="POST" action="{{ route('admin.finance.settlements.confirm', $settlement) }}" onsubmit="return confirm('Apakah Anda yakin ingin mengonfirmasi settlement ini?');">
                    @csrf
                    <button type="submit" class="w-full bg-green-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-green-700 transition-colors">
                        Konfirmasi Settlement
                    </button>
                </form>
            </div>
        @endif
    </div>
</div>
@endsection


