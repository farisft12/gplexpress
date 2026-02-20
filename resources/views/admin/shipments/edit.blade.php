@extends('layouts.app')

@section('title', 'Edit Paket - GPL Expres')
@section('page-title', 'Edit Paket')

@section('content')
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
    <!-- Header -->
    <div class="mb-6 lg:mb-8">
        <a href="{{ route('admin.shipments.index') }}" class="inline-flex items-center text-[#F4C430] hover:text-[#E6B020] mb-4 text-sm lg:text-base transition-colors">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Kembali
        </a>
        <h1 class="text-2xl lg:text-3xl font-bold text-gray-900">Edit Paket</h1>
        <p class="text-sm text-gray-600 mt-1">Nomor Resi: <span class="font-semibold">{{ $shipment->resi_number }}</span></p>
    </div>

    @if($shipment->status !== 'pickup')
        <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4 mb-6">
            <div class="flex items-center">
                <svg class="w-5 h-5 text-yellow-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
                <p class="text-sm text-yellow-800">Paket ini sudah di-assign ke kurir dan tidak dapat diubah.</p>
            </div>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.shipments.update', $shipment) }}" class="space-y-6" id="shipmentForm">
        @csrf
        @method('PUT')

        @php
            $sourceType = old('source_type', $shipment->source_type ?? ($shipment->expedition_id ? 'ekspedisi_lain' : 'perorangan'));
        @endphp

        <!-- Pilih Sumber Paket -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 sm:p-6 lg:p-8 {{ $shipment->status !== 'pickup' ? 'opacity-75 pointer-events-none' : '' }}">
            <div class="flex items-center mb-6">
                <div class="shrink-0 w-10 h-10 bg-[#F4C430]/10 rounded-lg flex items-center justify-center mr-3">
                    <svg class="w-6 h-6 text-[#F4C430]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">Sumber Paket</h3>
                    <p class="text-sm text-gray-500">Pilih jenis paket</p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <label class="source-option relative flex items-center p-4 sm:p-5 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-[#F4C430] transition-all group">
                    <input type="radio" name="source_type" value="perorangan" {{ $sourceType === 'perorangan' ? 'checked' : '' }} class="absolute inset-0 opacity-0 cursor-pointer z-10" {{ $shipment->status !== 'pickup' ? 'disabled' : '' }}>
                    <div class="flex items-center w-full relative z-0">
                        <div class="source-circle shrink-0 w-5 h-5 border-2 border-gray-300 rounded-full mr-3 group-hover:border-[#F4C430] flex items-center justify-center transition-all relative">
                            <div class="source-dot w-2 h-2 bg-white rounded-full absolute opacity-0 transition-opacity"></div>
                        </div>
                        <div class="flex-1">
                            <div class="font-semibold text-gray-900">Perorangan</div>
                            <div class="text-sm text-gray-500">Paket dari pengirim perorangan</div>
                        </div>
                    </div>
                </label>

                <label class="source-option relative flex items-center p-4 sm:p-5 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-[#F4C430] transition-all group">
                    <input type="radio" name="source_type" value="ekspedisi_lain" {{ $sourceType === 'ekspedisi_lain' ? 'checked' : '' }} class="absolute inset-0 opacity-0 cursor-pointer z-10" {{ $shipment->status !== 'pickup' ? 'disabled' : '' }}>
                    <div class="flex items-center w-full relative z-0">
                        <div class="source-circle shrink-0 w-5 h-5 border-2 border-gray-300 rounded-full mr-3 group-hover:border-[#F4C430] flex items-center justify-center transition-all relative">
                            <div class="source-dot w-2 h-2 bg-white rounded-full absolute opacity-0 transition-opacity"></div>
                        </div>
                        <div class="flex-1">
                            <div class="font-semibold text-gray-900">Ekspedisi Lain</div>
                            <div class="text-sm text-gray-500">Paket dari ekspedisi lain dengan resi bawaan</div>
                        </div>
                    </div>
                </label>
            </div>
        </div>

        <!-- Informasi Cabang & Paket -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 sm:p-6 lg:p-8">
            <div class="flex items-center mb-6">
                <div class="shrink-0 w-10 h-10 bg-[#F4C430]/10 rounded-lg flex items-center justify-center mr-3">
                    <svg class="w-6 h-6 text-[#F4C430]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">Informasi Cabang & Paket</h3>
                    <p class="text-sm text-gray-500">Detail cabang dan informasi paket</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6">
                <div id="expeditionField" class="{{ $sourceType === 'ekspedisi_lain' ? '' : 'hidden' }}">
                    <label for="expedition_id" class="block text-sm font-medium text-gray-700 mb-2">
                        Ekspedisi Asal <span class="text-red-500">*</span>
                    </label>
                    <select id="expedition_id" name="expedition_id"
                        class="w-full px-4 py-2.5 sm:py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#F4C430] focus:border-[#F4C430] outline-none transition-colors text-sm sm:text-base @error('expedition_id') border-red-500 @enderror"
                        {{ $shipment->status !== 'pickup' ? 'disabled' : '' }}>
                        <option value="">Pilih Ekspedisi Asal</option>
                        @foreach($expeditions as $exp)
                            <option value="{{ $exp->id }}" {{ old('expedition_id', $shipment->expedition_id) == $exp->id ? 'selected' : '' }}>
                                {{ $exp->name }} ({{ $exp->code ?? '-' }})
                            </option>
                        @endforeach
                    </select>
                    @error('expedition_id')
                        <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="origin_branch_id" class="block text-sm font-medium text-gray-700 mb-2">
                        Cabang Pengirim <span class="text-red-500">*</span>
                    </label>
                    <select id="origin_branch_id" name="origin_branch_id" required
                        class="w-full px-4 py-2.5 sm:py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#F4C430] focus:border-[#F4C430] outline-none transition-colors text-sm sm:text-base @error('origin_branch_id') border-red-500 @enderror"
                        {{ $shipment->status !== 'pickup' ? 'disabled' : '' }}>
                        <option value="">Pilih Cabang Pengirim</option>
                        @foreach($originBranches as $branch)
                            <option value="{{ $branch->id }}" {{ old('origin_branch_id', $shipment->origin_branch_id) == $branch->id ? 'selected' : '' }}>
                                {{ $branch->name }} ({{ $branch->code }})
                            </option>
                        @endforeach
                    </select>
                    @error('origin_branch_id')
                        <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="destination_branch_id" class="block text-sm font-medium text-gray-700 mb-2">
                        Cabang Tujuan <span class="text-red-500">*</span>
                    </label>
                    <select id="destination_branch_id" name="destination_branch_id" required
                        class="w-full px-4 py-2.5 sm:py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#F4C430] focus:border-[#F4C430] outline-none transition-colors text-sm sm:text-base @error('destination_branch_id') border-red-500 @enderror"
                        {{ $shipment->status !== 'pickup' ? 'disabled' : '' }}>
                        <option value="">Pilih Cabang Tujuan</option>
                        @foreach($destinationBranches as $branch)
                            <option value="{{ $branch->id }}" {{ old('destination_branch_id', $shipment->destination_branch_id) == $branch->id ? 'selected' : '' }}>
                                {{ $branch->name }} ({{ $branch->code }})
                            </option>
                        @endforeach
                    </select>
                    @error('destination_branch_id')
                        <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="package_type" class="block text-sm font-medium text-gray-700 mb-2">
                        Jenis Paket <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="package_type" name="package_type" value="{{ old('package_type', $shipment->package_type) }}" required
                        class="w-full px-4 py-2.5 sm:py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#F4C430] focus:border-[#F4C430] outline-none transition-colors text-sm sm:text-base @error('package_type') border-red-500 @enderror"
                        placeholder="Contoh: Dokumen, Pakaian, Elektronik"
                        {{ $shipment->status !== 'pickup' ? 'readonly' : '' }}>
                    @error('package_type')
                        <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="weight" class="block text-sm font-medium text-gray-700 mb-2">
                        Berat (kg) <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <input type="number" id="weight" name="weight" value="{{ old('weight', $shipment->weight) }}" required step="0.1" min="0.1"
                            class="w-full px-4 py-2.5 sm:py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#F4C430] focus:border-[#F4C430] outline-none transition-colors text-sm sm:text-base @error('weight') border-red-500 @enderror"
                            placeholder="0.0"
                            {{ $shipment->status !== 'pickup' ? 'readonly' : '' }}>
                        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                            <span class="text-gray-500 text-sm">kg</span>
                        </div>
                    </div>
                    @error('weight')
                        <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Tipe Pengiriman -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 sm:p-6 lg:p-8">
            <div class="flex items-center mb-6">
                <div class="shrink-0 w-10 h-10 bg-blue-50 rounded-lg flex items-center justify-center mr-3">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">Tipe Pengiriman</h3>
                    <p class="text-sm text-gray-500">Pilih metode pembayaran</p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <label class="radio-option relative flex items-center p-4 sm:p-5 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-[#F4C430] transition-all group {{ $shipment->status !== 'pickup' ? 'opacity-50 cursor-not-allowed' : '' }}">
                    <input type="radio" name="type" value="non_cod" {{ old('type', $shipment->type) === 'non_cod' ? 'checked' : '' }} 
                           class="absolute inset-0 opacity-0 cursor-pointer z-10" {{ $shipment->status !== 'pickup' ? 'disabled' : '' }}>
                    <div class="flex items-center w-full relative z-0">
                        <div class="radio-circle shrink-0 w-5 h-5 border-2 border-gray-300 rounded-full mr-3 group-hover:border-[#F4C430] flex items-center justify-center transition-all relative">
                            <div class="radio-dot w-2 h-2 bg-white rounded-full absolute opacity-0 transition-opacity"></div>
                        </div>
                        <div class="flex-1">
                            <div class="font-semibold text-gray-900">Non-COD</div>
                            <div class="text-sm text-gray-500">Bayar di muka</div>
                        </div>
                    </div>
                </label>

                <label class="radio-option relative flex items-center p-4 sm:p-5 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-[#F4C430] transition-all group {{ $shipment->status !== 'pickup' ? 'opacity-50 cursor-not-allowed' : '' }}">
                    <input type="radio" name="type" value="cod" {{ old('type', $shipment->type) === 'cod' ? 'checked' : '' }}
                           class="absolute inset-0 opacity-0 cursor-pointer z-10" {{ $shipment->status !== 'pickup' ? 'disabled' : '' }}>
                    <div class="flex items-center w-full relative z-0">
                        <div class="radio-circle shrink-0 w-5 h-5 border-2 border-gray-300 rounded-full mr-3 group-hover:border-[#F4C430] flex items-center justify-center transition-all relative">
                            <div class="radio-dot w-2 h-2 bg-white rounded-full absolute opacity-0 transition-opacity"></div>
                        </div>
                        <div class="flex-1">
                            <div class="font-semibold text-gray-900">COD</div>
                            <div class="text-sm text-gray-500">Bayar saat terima</div>
                        </div>
                    </div>
                </label>
            </div>

            <!-- Non-COD Details (conditional) -->
            <div id="nonCodDetails" class="mt-6 {{ old('type', $shipment->type) === 'non_cod' ? '' : 'hidden' }}">
                <div class="bg-gray-50 rounded-lg p-4 sm:p-6 border border-gray-200">
                    <h4 class="text-sm font-semibold text-gray-900 mb-4">Detail Non-COD</h4>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                        <div>
                            <label for="shipping_cost" class="block text-sm font-medium text-gray-700 mb-2">
                                Tarif Pengiriman (Rp) <span class="text-red-500">*</span>
                                <span id="pricingInfo" class="text-xs text-gray-500 ml-2"></span>
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                    <span class="text-gray-500 text-sm">Rp</span>
                                </div>
                                <input type="number" id="shipping_cost" name="shipping_cost" value="{{ old('shipping_cost', $shipment->shipping_cost) }}" min="0" step="1000" readonly
                                    class="w-full pl-12 pr-4 py-2.5 sm:py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#F4C430] focus:border-[#F4C430] outline-none transition-colors text-sm sm:text-base bg-gray-50 @error('shipping_cost') border-red-500 @enderror"
                                    placeholder="0">
                                <div id="pricingLoader" class="absolute inset-y-0 right-0 flex items-center pr-3 hidden">
                                    <svg class="animate-spin h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                </div>
                            </div>
                            <p id="pricingError" class="mt-1.5 text-sm text-red-600 hidden"></p>
                            @error('shipping_cost')
                                <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- COD Details (conditional) - Nominal COD, Ongkir, Admin -->
            <div id="codDetails" class="mt-6 {{ old('type', $shipment->type) === 'cod' ? '' : 'hidden' }}">
                <div class="bg-gray-50 rounded-lg p-4 sm:p-6 border border-gray-200">
                    <h4 class="text-sm font-semibold text-gray-900 mb-4">Detail COD</h4>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 sm:gap-6">
                        <div>
                            <label for="cod_amount" class="block text-sm font-medium text-gray-700 mb-2">
                                Nominal COD (Rp) <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                    <span class="text-gray-500 text-sm">Rp</span>
                                </div>
                                <input type="number" id="cod_amount" name="cod_amount" value="{{ old('cod_amount', $shipment->cod_amount) }}" min="0" step="1000"
                                    class="w-full pl-12 pr-4 py-2.5 sm:py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#F4C430] focus:border-[#F4C430] outline-none transition-colors text-sm sm:text-base @error('cod_amount') border-red-500 @enderror"
                                    placeholder="0"
                                    {{ $shipment->status !== 'pickup' ? 'readonly' : '' }}>
                            </div>
                            @error('cod_amount')
                                <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="cod_shipping_cost" class="block text-sm font-medium text-gray-700 mb-2">
                                Ongkir (Rp) <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                    <span class="text-gray-500 text-sm">Rp</span>
                                </div>
                                <input type="number" id="cod_shipping_cost" name="cod_shipping_cost" value="{{ old('cod_shipping_cost', $shipment->cod_shipping_cost ?? 0) }}" min="0" step="1000"
                                    class="w-full pl-12 pr-4 py-2.5 sm:py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#F4C430] focus:border-[#F4C430] outline-none transition-colors text-sm sm:text-base @error('cod_shipping_cost') border-red-500 @enderror"
                                    placeholder="0"
                                    {{ $shipment->status !== 'pickup' ? 'readonly' : '' }}>
                            </div>
                            @error('cod_shipping_cost')
                                <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="cod_admin_fee" class="block text-sm font-medium text-gray-700 mb-2">
                                Admin (Rp) <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                    <span class="text-gray-500 text-sm">Rp</span>
                                </div>
                                <input type="number" id="cod_admin_fee" name="cod_admin_fee" value="{{ old('cod_admin_fee', $shipment->cod_admin_fee ?? 0) }}" min="0" step="1000"
                                    class="w-full pl-12 pr-4 py-2.5 sm:py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#F4C430] focus:border-[#F4C430] outline-none transition-colors text-sm sm:text-base @error('cod_admin_fee') border-red-500 @enderror"
                                    placeholder="0"
                                    {{ $shipment->status !== 'pickup' ? 'readonly' : '' }}>
                            </div>
                            @error('cod_admin_fee')
                                <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Data Pengirim -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 sm:p-6 lg:p-8">
            <div class="flex items-center mb-6">
                <div class="shrink-0 w-10 h-10 bg-green-50 rounded-lg flex items-center justify-center mr-3">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">Data Pengirim</h3>
                    <p class="text-sm text-gray-500">Informasi pengirim paket</p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                <div>
                    <label for="sender_name" class="block text-sm font-medium text-gray-700 mb-2">
                        Nama Pengirim / Nama Toko <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="sender_name" name="sender_name" value="{{ old('sender_name', $shipment->sender_name) }}" required
                        class="w-full px-4 py-2.5 sm:py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#F4C430] focus:border-[#F4C430] outline-none transition-colors text-sm sm:text-base @error('sender_name') border-red-500 @enderror"
                        placeholder="Nama lengkap pengirim"
                        {{ $shipment->status !== 'pickup' ? 'readonly' : '' }}>
                    @error('sender_name')
                        <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div id="senderPhoneField" class="{{ $sourceType === 'perorangan' ? '' : 'hidden' }}">
                    <label for="sender_phone" class="block text-sm font-medium text-gray-700 mb-2">
                        No. HP Pengirim <span class="text-red-500 sender-phone-required">*</span>
                    </label>
                    <input type="text" id="sender_phone" name="sender_phone" value="{{ old('sender_phone', $shipment->sender_phone) }}"
                        class="w-full px-4 py-2.5 sm:py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#F4C430] focus:border-[#F4C430] outline-none transition-colors text-sm sm:text-base @error('sender_phone') border-red-500 @enderror"
                        placeholder="08xxxxxxxxxx"
                        {{ $shipment->status !== 'pickup' ? 'readonly' : '' }}>
                    @error('sender_phone')
                        <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div id="externalResiField" class="{{ $sourceType === 'ekspedisi_lain' ? '' : 'hidden' }}">
                    <label for="external_resi_number" class="block text-sm font-medium text-gray-700 mb-2">
                        Resi Ekspedisi Awal <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="external_resi_number" name="external_resi_number" value="{{ old('external_resi_number', $shipment->external_resi_number) }}"
                        class="w-full px-4 py-2.5 sm:py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#F4C430] focus:border-[#F4C430] outline-none transition-colors text-sm sm:text-base @error('external_resi_number') border-red-500 @enderror"
                        placeholder="Nomor resi dari ekspedisi asal"
                        {{ $shipment->status !== 'pickup' ? 'readonly' : '' }}>
                    @error('external_resi_number')
                        <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="sm:col-span-2">
                    <label for="sender_address" class="block text-sm font-medium text-gray-700 mb-2">
                        Alamat Pengirim <span class="text-red-500">*</span>
                    </label>
                    <textarea id="sender_address" name="sender_address" rows="3" required
                        class="w-full px-4 py-2.5 sm:py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#F4C430] focus:border-[#F4C430] outline-none transition-colors text-sm sm:text-base resize-none @error('sender_address') border-red-500 @enderror"
                        placeholder="Alamat lengkap pengirim"
                        {{ $shipment->status !== 'pickup' ? 'readonly' : '' }}>{{ old('sender_address', $shipment->sender_address) }}</textarea>
                    @error('sender_address')
                        <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Data Penerima -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 sm:p-6 lg:p-8">
            <div class="flex items-center mb-6">
                <div class="shrink-0 w-10 h-10 bg-purple-50 rounded-lg flex items-center justify-center mr-3">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">Data Penerima</h3>
                    <p class="text-sm text-gray-500">Informasi penerima paket</p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                <div>
                    <label for="receiver_name" class="block text-sm font-medium text-gray-700 mb-2">
                        Nama Penerima <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="receiver_name" name="receiver_name" value="{{ old('receiver_name', $shipment->receiver_name) }}" required
                        class="w-full px-4 py-2.5 sm:py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#F4C430] focus:border-[#F4C430] outline-none transition-colors text-sm sm:text-base @error('receiver_name') border-red-500 @enderror"
                        placeholder="Nama lengkap penerima"
                        {{ $shipment->status !== 'pickup' ? 'readonly' : '' }}>
                    @error('receiver_name')
                        <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="receiver_phone" class="block text-sm font-medium text-gray-700 mb-2">
                        No. HP Penerima <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="receiver_phone" name="receiver_phone" value="{{ old('receiver_phone', $shipment->receiver_phone) }}" required
                        class="w-full px-4 py-2.5 sm:py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#F4C430] focus:border-[#F4C430] outline-none transition-colors text-sm sm:text-base @error('receiver_phone') border-red-500 @enderror"
                        placeholder="08xxxxxxxxxx"
                        {{ $shipment->status !== 'pickup' ? 'readonly' : '' }}>
                    @error('receiver_phone')
                        <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="sm:col-span-2">
                    <label for="receiver_address" class="block text-sm font-medium text-gray-700 mb-2">
                        Alamat Penerima <span class="text-red-500">*</span>
                    </label>
                    <textarea id="receiver_address" name="receiver_address" rows="3" required
                        class="w-full px-4 py-2.5 sm:py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#F4C430] focus:border-[#F4C430] outline-none transition-colors text-sm sm:text-base resize-none @error('receiver_address') border-red-500 @enderror"
                        placeholder="Alamat lengkap penerima"
                        {{ $shipment->status !== 'pickup' ? 'readonly' : '' }}>{{ old('receiver_address', $shipment->receiver_address) }}</textarea>
                    @error('receiver_address')
                        <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Submit Buttons -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 sm:p-6">
            <div class="flex flex-col sm:flex-row gap-3 sm:gap-4 justify-end">
                <a href="{{ route('admin.shipments.index') }}" 
                   class="w-full sm:w-auto px-6 py-2.5 sm:py-3 bg-gray-100 text-gray-700 rounded-lg font-semibold hover:bg-gray-200 transition-colors text-center text-sm sm:text-base">
                    Batal
                </a>
                @if($shipment->status === 'pickup')
                    <button type="submit" 
                            class="w-full sm:w-auto px-6 py-2.5 sm:py-3 bg-[#F4C430] text-white rounded-lg font-semibold hover:bg-[#E6B020] transition-colors text-sm sm:text-base shadow-sm">
                        Simpan Perubahan
                    </button>
                @endif
            </div>
        </div>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const sourceInputs = document.querySelectorAll('input[name="source_type"]');
        const expeditionField = document.getElementById('expeditionField');
        const expeditionSelect = document.getElementById('expedition_id');
        const senderPhoneField = document.getElementById('senderPhoneField');
        const senderPhone = document.getElementById('sender_phone');
        const externalResiField = document.getElementById('externalResiField');
        const externalResiNumber = document.getElementById('external_resi_number');

        const typeInputs = document.querySelectorAll('input[name="type"]');
        const codDetails = document.getElementById('codDetails');
        const nonCodDetails = document.getElementById('nonCodDetails');
        const codAmount = document.getElementById('cod_amount');
        const codShippingCost = document.getElementById('cod_shipping_cost');
        const codAdminFee = document.getElementById('cod_admin_fee');
        const shippingCost = document.getElementById('shipping_cost');
        const originBranch = document.getElementById('origin_branch_id');
        const destinationBranch = document.getElementById('destination_branch_id');
        const weight = document.getElementById('weight');
        const pricingInfo = document.getElementById('pricingInfo');
        const pricingLoader = document.getElementById('pricingLoader');
        const pricingError = document.getElementById('pricingError');

        // Source type toggle
        function toggleSourceType() {
            const sourceType = document.querySelector('input[name="source_type"]:checked')?.value;
            if (sourceType === 'ekspedisi_lain') {
                if (expeditionField) expeditionField.classList.remove('hidden');
                if (expeditionSelect) expeditionSelect.setAttribute('required', 'required');
                if (senderPhoneField) senderPhoneField.classList.add('hidden');
                if (senderPhone) senderPhone.removeAttribute('required');
                if (externalResiField) externalResiField.classList.remove('hidden');
                if (externalResiNumber) externalResiNumber.setAttribute('required', 'required');
            } else {
                if (expeditionField) expeditionField.classList.add('hidden');
                if (expeditionSelect) expeditionSelect.removeAttribute('required');
                if (senderPhoneField) senderPhoneField.classList.remove('hidden');
                if (senderPhone) senderPhone.setAttribute('required', 'required');
                if (externalResiField) externalResiField.classList.add('hidden');
                if (externalResiNumber) externalResiNumber.removeAttribute('required');
            }
        }

        // Update source type visuals
        function updateSourceVisuals() {
            sourceInputs.forEach(input => {
                if (input.disabled) return;
                const label = input.closest('label');
                const circle = label?.querySelector('.source-circle');
                const dot = label?.querySelector('.source-dot');
                if (input.checked) {
                    label?.classList.add('border-[#F4C430]', 'bg-[#F4C430]/5');
                    label?.classList.remove('border-gray-200');
                    circle?.classList.add('border-[#F4C430]', 'bg-[#F4C430]');
                    circle?.classList.remove('border-gray-300');
                    dot?.classList.remove('opacity-0');
                    dot?.classList.add('opacity-100');
                } else {
                    label?.classList.remove('border-[#F4C430]', 'bg-[#F4C430]/5');
                    label?.classList.add('border-gray-200');
                    circle?.classList.remove('border-[#F4C430]', 'bg-[#F4C430]');
                    circle?.classList.add('border-gray-300');
                    dot?.classList.add('opacity-0');
                    dot?.classList.remove('opacity-100');
                }
            });
        }

        // Add event listeners for source type
        sourceInputs.forEach(input => {
            // Listen for change event - input radio now covers entire label area
            input.addEventListener('change', function() {
                if (this.disabled) return;
                updateSourceVisuals();
                toggleSourceType();
            });
        });

        // Initialize source visuals and toggle
        updateSourceVisuals();
        toggleSourceType();

        function updateRadioVisuals() {
            typeInputs.forEach(input => {
                if (input.disabled) return;
                const label = input.closest('label');
                const circle = label?.querySelector('.radio-circle');
                const dot = label?.querySelector('.radio-dot');
                if (input.checked) {
                    label?.classList.add('border-[#F4C430]', 'bg-[#F4C430]/5');
                    label?.classList.remove('border-gray-200');
                    circle?.classList.add('border-[#F4C430]', 'bg-[#F4C430]');
                    circle?.classList.remove('border-gray-300');
                    dot?.classList.remove('opacity-0');
                    dot?.classList.add('opacity-100');
                } else {
                    label?.classList.remove('border-[#F4C430]', 'bg-[#F4C430]/5');
                    label?.classList.add('border-gray-200');
                    circle?.classList.remove('border-[#F4C430]', 'bg-[#F4C430]');
                    circle?.classList.add('border-gray-300');
                    dot?.classList.add('opacity-0');
                    dot?.classList.remove('opacity-100');
                }
            });
        }

        function toggleCodDetails() {
            const selectedType = document.querySelector('input[name="type"]:checked')?.value;
            if (selectedType === 'cod') {
                if (codDetails) codDetails.classList.remove('hidden');
                if (nonCodDetails) nonCodDetails.classList.add('hidden');
                if (codAmount) codAmount.setAttribute('required', 'required');
                if (codShippingCost) codShippingCost.setAttribute('required', 'required');
                if (codAdminFee) codAdminFee.setAttribute('required', 'required');
                if (codAmount) codAmount.removeAttribute('disabled');
                if (codShippingCost) codShippingCost.removeAttribute('disabled');
                if (codAdminFee) codAdminFee.removeAttribute('disabled');
                if (shippingCost) shippingCost.removeAttribute('required');
                if (shippingCost) shippingCost.value = '';
                if (shippingCost) shippingCost.setAttribute('readonly', 'readonly');
                if (pricingInfo) pricingInfo.textContent = '';
                if (pricingError) pricingError.classList.add('hidden');
            } else {
                if (codDetails) codDetails.classList.add('hidden');
                if (nonCodDetails) nonCodDetails.classList.remove('hidden');
                if (codAmount) codAmount.removeAttribute('required');
                if (codShippingCost) codShippingCost.removeAttribute('required');
                if (codAdminFee) codAdminFee.removeAttribute('required');
                if (codAmount) codAmount.value = '';
                if (codShippingCost) codShippingCost.value = '';
                if (codAdminFee) codAdminFee.value = '';
                if (codAmount) codAmount.setAttribute('disabled', 'disabled');
                if (codShippingCost) codShippingCost.setAttribute('disabled', 'disabled');
                if (codAdminFee) codAdminFee.setAttribute('disabled', 'disabled');
                if (shippingCost) shippingCost.setAttribute('required', 'required');
                if (shippingCost) shippingCost.removeAttribute('disabled');
                if (shippingCost) shippingCost.setAttribute('readonly', 'readonly');
                calculateShippingCost();
            }
        }

        // Function to calculate shipping cost
        async function calculateShippingCost() {
            const selectedType = document.querySelector('input[name="type"]:checked')?.value;
            
            // Only calculate for non-cod
            if (selectedType !== 'non_cod') {
                return;
            }
            
            const originId = originBranch?.value;
            const destId = destinationBranch?.value;
            const weightValue = parseFloat(weight?.value);
            
            // Check if all required fields are filled
            if (!originId || !destId || !weightValue || weightValue <= 0) {
                if (shippingCost) shippingCost.value = '';
                if (pricingInfo) pricingInfo.textContent = '';
                if (pricingError) pricingError.classList.add('hidden');
                return;
            }
            
            // Show loader
            if (pricingLoader) pricingLoader.classList.remove('hidden');
            if (pricingError) pricingError.classList.add('hidden');
            if (pricingInfo) pricingInfo.textContent = 'Mencari tarif...';
            
            try {
                // Fetch pricing from API
                const response = await fetch(`{{ route('admin.shipments.pricing.get') }}?origin_branch_id=${originId}&destination_branch_id=${destId}`);
                const data = await response.json();
                
                if (!data.success) {
                    throw new Error(data.message || 'Gagal mendapatkan tarif');
                }
                
                const totalCost = Math.round(data.pricing.base_price * weightValue);
                
                // Update shipping cost
                if (shippingCost) shippingCost.value = totalCost;
                if (pricingInfo) {
                    pricingInfo.textContent = `(${data.pricing.name} - Rp ${data.pricing.base_price.toLocaleString('id-ID')}/kg × ${weightValue} kg)`;
                    pricingInfo.classList.remove('text-red-500');
                    pricingInfo.classList.add('text-gray-500');
                }
                
            } catch (error) {
                console.error('Error calculating shipping cost:', error);
                if (shippingCost) shippingCost.value = '';
                if (pricingInfo) pricingInfo.textContent = '';
                if (pricingError) {
                    pricingError.textContent = error.message || 'Gagal menghitung tarif. Pastikan tarif untuk rute ini sudah ditambahkan.';
                    pricingError.classList.remove('hidden');
                }
            } finally {
                if (pricingLoader) pricingLoader.classList.add('hidden');
            }
        }
        
        // Listen to changes in origin, destination, and weight
        if (originBranch) {
            originBranch.addEventListener('change', calculateShippingCost);
        }
        if (destinationBranch) {
            destinationBranch.addEventListener('change', calculateShippingCost);
        }
        if (weight) {
            weight.addEventListener('input', calculateShippingCost);
            weight.addEventListener('change', calculateShippingCost);
        }
        
        // Calculate on initial load if values are present and type is non_cod
        const selectedType = document.querySelector('input[name="type"]:checked')?.value;
        if (selectedType === 'non_cod' && originBranch?.value && destinationBranch?.value && weight?.value) {
            calculateShippingCost();
        }

        // Initial update
        updateRadioVisuals();
        toggleCodDetails();

        // Add event listeners for type (COD/Non-COD)
        typeInputs.forEach(input => {
            // Listen for change event - input radio now covers entire label area
            input.addEventListener('change', function() {
                if (this.disabled) return;
                updateRadioVisuals();
                toggleCodDetails();
            });
        });

        const form = document.getElementById('shipmentForm');
        if (form) {
            form.addEventListener('submit', function() {
                const selectedType = document.querySelector('input[name="type"]:checked')?.value;
                const sourceType = document.querySelector('input[name="source_type"]:checked')?.value;
                if (selectedType === 'non_cod') {
                    if (shippingCost) shippingCost.removeAttribute('readonly');
                    if (codAmount) codAmount.value = '';
                    if (codShippingCost) codShippingCost.value = '';
                    if (codAdminFee) codAdminFee.value = '';
                } else {
                    if (shippingCost) shippingCost.value = '';
                    if (shippingCost) shippingCost.removeAttribute('readonly');
                }
                if (sourceType === 'ekspedisi_lain') {
                    if (senderPhone) senderPhone.value = '';
                } else {
                    if (externalResiNumber) externalResiNumber.value = '';
                    if (expeditionSelect) expeditionSelect.value = '';
                }
            });
        }
    });
</script>
@endsection

