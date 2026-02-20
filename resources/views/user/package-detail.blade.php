@extends('layouts.app')

@section('title', 'Detail Paket - GPL Express')
@section('page-title', 'Detail Paket')

@section('content')
<div>
    <div class="mb-6 lg:mb-8">
        <h1 class="text-2xl lg:text-3xl font-bold text-gray-900">Detail Paket</h1>
        <p class="text-sm text-gray-600 mt-2">Resi: {{ $shipment->resi_number }}</p>
    </div>

    <!-- Package Info -->
    <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Informasi Paket</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <p class="text-sm text-gray-600">Status</p>
                <p class="text-lg font-medium text-gray-900">
                    <span class="px-2 py-1 text-xs font-semibold rounded-full 
                        @if($shipment->status === 'diterima') bg-green-100 text-green-800
                        @elseif($shipment->status === 'dalam_pengiriman') bg-blue-100 text-blue-800
                        @else bg-yellow-100 text-yellow-800
                        @endif">
                        {{ ucfirst(str_replace('_', ' ', $shipment->status)) }}
                    </span>
                </p>
            </div>
            <div>
                <p class="text-sm text-gray-600">Tipe</p>
                <p class="text-lg font-medium text-gray-900">{{ $shipment->type === 'cod' ? 'COD' : 'Non-COD' }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-600">Pengirim</p>
                <p class="text-lg font-medium text-gray-900">{{ $shipment->sender_name }}</p>
                <p class="text-sm text-gray-500">{{ $shipment->sender_phone }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-600">Penerima</p>
                <p class="text-lg font-medium text-gray-900">{{ $shipment->receiver_name }}</p>
                <p class="text-sm text-gray-500">{{ $shipment->receiver_phone }}</p>
            </div>
        </div>
    </div>

    <!-- Status Timeline -->
    <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Timeline Status</h2>
        <div class="space-y-4">
            @foreach($shipment->statusHistories as $history)
                <div class="flex items-start">
                    <div class="flex-shrink-0 w-2 h-2 bg-[#F4C430] rounded-full mt-2"></div>
                    <div class="ml-4 flex-1">
                        <p class="text-sm font-medium text-gray-900">{{ ucfirst(str_replace('_', ' ', $history->status)) }}</p>
                        <p class="text-xs text-gray-500">{{ $history->created_at->format('d M Y H:i') }}</p>
                        @if($history->notes)
                            <p class="text-sm text-gray-600 mt-1">{{ $history->notes }}</p>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Review Section (only if delivered) -->
    @if($shipment->status === 'diterima')
        <div id="review" class="bg-white rounded-xl shadow-sm p-6">
            @if($review)
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Review Anda</h2>
                <div class="space-y-4">
                    <div>
                        <p class="text-sm text-gray-600 mb-1">Rating</p>
                        <div class="flex items-center">
                            @for($i = 1; $i <= 5; $i++)
                                <span class="text-2xl {{ $i <= $review->rating ? 'text-[#F4C430]' : 'text-gray-300' }}">★</span>
                            @endfor
                            <span class="ml-2 text-sm text-gray-600">({{ $review->rating }}/5)</span>
                        </div>
                    </div>
                    @if($review->comment)
                        <div>
                            <p class="text-sm text-gray-600 mb-1">Komentar</p>
                            <p class="text-gray-900">{{ $review->comment }}</p>
                        </div>
                    @endif
                    <p class="text-xs text-gray-500">Dikirim pada {{ $review->created_at->format('d M Y H:i') }}</p>
                </div>
            @else
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Beri Review</h2>
                <form method="POST" action="{{ route('user.packages.review', $shipment) }}" class="space-y-4">
                    @csrf
                    @if($errors->any())
                        <div class="bg-red-50 border-l-4 border-red-400 text-red-700 p-4 rounded-lg">
                            <ul class="list-disc list-inside">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Rating</label>
                        <div class="flex items-center space-x-2">
                            @for($i = 1; $i <= 5; $i++)
                                <input type="radio" name="rating" value="{{ $i }}" id="rating{{ $i }}" 
                                       class="w-5 h-5 text-[#F4C430] focus:ring-[#F4C430]" required>
                                <label for="rating{{ $i }}" class="text-2xl cursor-pointer">
                                    <span class="text-gray-300 hover:text-[#F4C430]">★</span>
                                </label>
                            @endfor
                        </div>
                    </div>
                    <div>
                        <label for="comment" class="block text-sm font-medium text-gray-700 mb-2">Komentar</label>
                        <textarea name="comment" id="comment" rows="4" 
                                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#F4C430] focus:border-transparent outline-none"
                                  placeholder="Bagikan pengalaman Anda..."></textarea>
                    </div>
                    <button type="submit" class="bg-[#F4C430] text-white px-6 py-2 rounded-lg font-semibold hover:bg-[#E6B020] transition-colors">
                        Kirim Review
                    </button>
                </form>
            @endif
        </div>
    @endif
</div>
@endsection

