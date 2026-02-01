@extends('layouts.app')

@section('title', 'Setting Fonnte - GPL Expres')
@section('page-title', 'Setting Fonnte')

@section('content')
<div>
    <div class="mb-6 lg:mb-8">
        <h1 class="text-2xl lg:text-3xl font-bold text-gray-900">Setting Fonnte</h1>
        <p class="text-sm text-gray-600 mt-2">Konfigurasi integrasi WhatsApp Fonnte</p>
    </div>

    <div class="bg-white rounded-xl shadow-sm p-6">
        @if(session('success'))
            <div class="bg-green-50 border-l-4 border-green-400 text-green-700 p-4 mb-6 rounded-lg" role="alert">
                <p class="font-bold">Berhasil!</p>
                <p>{{ session('success') }}</p>
            </div>
        @endif

        @if(isset($isConfigured) && $isConfigured)
            <div class="mb-6 p-4 rounded-lg {{ isset($deviceStatus) && $deviceStatus['success'] ? 'bg-green-50 border-l-4 border-green-400' : 'bg-yellow-50 border-l-4 border-yellow-400' }}">
                <p class="font-bold {{ isset($deviceStatus) && $deviceStatus['success'] ? 'text-green-700' : 'text-yellow-700' }}">
                    Status Konfigurasi:
                </p>
                @if(isset($deviceStatus) && $deviceStatus['success'])
                    <p class="text-green-700">✓ Fonnte terhubung dan siap digunakan</p>
                @else
                    <p class="text-yellow-700">⚠ Konfigurasi lengkap, tetapi verifikasi device gagal. Pastikan device terhubung.</p>
                @endif
            </div>
        @else
            <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-400 text-red-700 rounded-lg">
                <p class="font-bold">Perhatian:</p>
                <p>Fonnte belum dikonfigurasi. Silakan isi semua field yang diperlukan.</p>
            </div>
        @endif

        <form method="POST" action="{{ route('owner.settings.fonnte.update') }}" class="space-y-6">
            @csrf
            @method('PUT')
            <div>
                <label for="fonnte_token" class="block text-sm font-medium text-gray-700 mb-2">
                    Fonnte Token <span class="text-red-500">*</span>
                </label>
                <input 
                    type="text" 
                    id="fonnte_token" 
                    name="fonnte_token" 
                    value="{{ old('fonnte_token', env('FOONTE_TOKEN')) }}"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#F4C430] focus:border-transparent outline-none"
                    placeholder="Masukkan Fonnte Token"
                >
                <p class="mt-1 text-sm text-gray-500">Token untuk autentikasi API Fonnte</p>
            </div>
            <div>
                <label for="fonnte_no_token" class="block text-sm font-medium text-gray-700 mb-2">
                    Fonnte No Token <span class="text-red-500">*</span>
                </label>
                <input 
                    type="text" 
                    id="fonnte_no_token" 
                    name="fonnte_no_token" 
                    value="{{ old('fonnte_no_token', env('FOONTE_NO_TOKEN')) }}"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#F4C430] focus:border-transparent outline-none"
                    placeholder="Masukkan Fonnte No Token"
                >
                <p class="mt-1 text-sm text-gray-500">No Token untuk autentikasi API Fonnte</p>
            </div>
            <div>
                <label for="fonnte_phone" class="block text-sm font-medium text-gray-700 mb-2">
                    Nomor Telepon <span class="text-red-500">*</span>
                </label>
                <input 
                    type="text" 
                    id="fonnte_phone" 
                    name="fonnte_phone" 
                    value="{{ old('fonnte_phone', env('FOONTE_PHONE')) }}"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#F4C430] focus:border-transparent outline-none"
                    placeholder="088704464961"
                >
                <p class="mt-1 text-sm text-gray-500">Nomor WhatsApp yang terdaftar di Fonnte</p>
            </div>
            <div>
                <label for="fonnte_url" class="block text-sm font-medium text-gray-700 mb-2">
                    API URL
                </label>
                <input 
                    type="url" 
                    id="fonnte_url" 
                    name="fonnte_url" 
                    value="{{ old('fonnte_url', env('FOONTE_URL', 'https://api.fonnte.com')) }}"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#F4C430] focus:border-transparent outline-none"
                    placeholder="https://api.fonnte.com"
                >
                <p class="mt-1 text-sm text-gray-500">URL endpoint API Fonnte (default: https://api.fonnte.com)</p>
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

