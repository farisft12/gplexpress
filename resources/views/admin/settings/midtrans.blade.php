@extends('layouts.app')

@section('title', 'Setting Midtrans - GPL Expres')
@section('page-title', 'Setting Midtrans')

@section('content')
<div>
    <div class="mb-6 lg:mb-8">
        <h1 class="text-2xl lg:text-3xl font-bold text-gray-900">Setting Midtrans</h1>
        <p class="text-sm text-gray-600 mt-2">Konfigurasi integrasi payment gateway Midtrans</p>
    </div>

    <div class="bg-white rounded-xl shadow-sm p-6">
        @if(session('success'))
            <div class="bg-green-50 border-l-4 border-green-400 text-green-700 p-4 mb-6 rounded-lg" role="alert">
                <p class="font-bold">Berhasil!</p>
                <p>{{ session('success') }}</p>
            </div>
        @endif

        <form method="POST" action="{{ route('owner.settings.midtrans.update') }}" class="space-y-6">
            @csrf
            @method('PUT')
            <div>
                <label for="midtrans_server_key" class="block text-sm font-medium text-gray-700 mb-2">
                    Server Key
                </label>
                <input 
                    type="password" 
                    id="midtrans_server_key" 
                    name="midtrans_server_key" 
                    value="{{ old('midtrans_server_key', env('MIDTRANS_SERVER_KEY')) }}"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#F4C430] focus:border-transparent outline-none"
                    placeholder="Masukkan Server Key Midtrans"
                >
            </div>
            <div>
                <label for="midtrans_client_key" class="block text-sm font-medium text-gray-700 mb-2">
                    Client Key
                </label>
                <input 
                    type="text" 
                    id="midtrans_client_key" 
                    name="midtrans_client_key" 
                    value="{{ old('midtrans_client_key', env('MIDTRANS_CLIENT_KEY')) }}"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#F4C430] focus:border-transparent outline-none"
                    placeholder="Masukkan Client Key Midtrans"
                >
            </div>
            <div>
                <label for="midtrans_merchant_id" class="block text-sm font-medium text-gray-700 mb-2">
                    Merchant ID
                </label>
                <input 
                    type="text" 
                    id="midtrans_merchant_id" 
                    name="midtrans_merchant_id" 
                    value="{{ old('midtrans_merchant_id', env('MIDTRANS_MERCHANT_ID')) }}"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#F4C430] focus:border-transparent outline-none"
                    placeholder="Masukkan Merchant ID"
                >
            </div>
            <div>
                <label for="midtrans_is_production" class="flex items-center">
                    <input 
                        type="checkbox" 
                        id="midtrans_is_production" 
                        name="midtrans_is_production" 
                        value="1"
                        {{ old('midtrans_is_production', env('MIDTRANS_IS_PRODUCTION', false)) ? 'checked' : '' }}
                        class="w-4 h-4 text-[#F4C430] focus:ring-[#F4C430] border-gray-300 rounded"
                    >
                    <span class="ml-2 text-sm text-gray-700">Production Mode</span>
                </label>
            </div>
            <button 
                type="submit" 
                class="bg-[#F4C430] text-white px-6 py-3 rounded-lg font-semibold hover:bg-[#E6B020] transition-colors"
            >
                Simpan Setting
            </button>
        </form>
    </div>
</div>
@endsection

