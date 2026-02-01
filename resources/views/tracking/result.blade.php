<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lacak Paket - {{ $shipment->resi_number }} - GPL Expres</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50">
    <div class="min-h-screen">
        <!-- Header -->
        <header class="bg-[#F4C430] text-white">
            <div class="max-w-4xl mx-auto px-4 py-3">
                <div class="flex justify-between items-center">
                    <a href="{{ route('home') }}" class="text-lg font-bold">GPL EXPRESS</a>
                    <a href="{{ route('tracking.index') }}" class="text-sm hover:underline">Lacak Lagi</a>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="max-w-4xl mx-auto px-4 py-6">
            <!-- Waybill Number -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-[#F4C430] mb-2">Nomor Resi</label>
                <div class="flex items-center gap-2 bg-white border border-gray-300 rounded-lg px-4 py-3">
                    <span class="font-mono text-gray-900 flex-1">{{ $shipment->resi_number }}</span>
                </div>
            </div>

            <!-- Progress Bar -->
            <div class="bg-white rounded-lg border border-gray-200 p-6 mb-6">
                <div class="relative">
                    <!-- Progress Line -->
                    <div class="absolute top-6 left-0 right-0 h-0.5 border-t-2 border-dashed border-gray-300"></div>
                    
                    <!-- Progress Stages -->
                    <div class="relative flex justify-between">
                        @php
                            // Mapping status ke stage image
                            // Stage 1 = Pickup
                            // Stage 2 = Diproses
                            // Stage 3 = Dalam Pengiriman
                            // Stage 4 = Sampai di Cabang Tujuan
                            // Stage 5 = Diterima
                            $stages = [
                                ['name' => 'Pickup', 'image' => asset('img/Stage 1.png'), 'status' => 'pickup'],
                                ['name' => 'Diproses', 'image' => asset('img/Stage 2.png'), 'status' => 'diproses'],
                                ['name' => 'Dalam Pengiriman', 'image' => asset('img/Stage 3.png'), 'status' => 'dalam_pengiriman'],
                                ['name' => 'Sampai di Cabang Tujuan', 'image' => asset('img/Stage 4.png'), 'status' => 'sampai_di_cabang_tujuan'],
                                ['name' => 'Diterima', 'image' => asset('img/Stage 5.png'), 'status' => 'diterima'],
                            ];
                            
                            // Tentukan status saat ini
                            $currentStatus = $shipment->status;
                        @endphp
                        
                        @foreach($stages as $index => $stage)
                            @php
                                // Tentukan status bulatan berdasarkan posisi relatif terhadap status saat ini
                                $stageStatus = $stage['status'];
                                $statusOrder = ['pickup', 'diproses', 'dalam_pengiriman', 'sampai_di_cabang_tujuan', 'diterima'];
                                
                                $currentIndex = array_search($currentStatus, $statusOrder);
                                $stageIndex = array_search($stageStatus, $statusOrder);
                                
                                $isCompleted = $stageIndex < $currentIndex;
                                $isActive = $stageIndex === $currentIndex;
                                
                                // Tentukan warna, border, dan glow
                                if ($isActive) {
                                    // Stage aktif: berwarna dengan border tebal dan glow effect
                                    $circleBg = 'bg-white';
                                    $circleBorder = 'border-4 border-[#F4C430]';
                                    $glowEffect = 'shadow-lg shadow-[#F4C430]/50';
                                    $grayscale = ''; // Tidak grayscale, tetap berwarna
                                } elseif ($isCompleted) {
                                    // Stage selesai: berwarna (tanpa background hijau)
                                    $circleBg = 'bg-white';
                                    $circleBorder = 'border-2 border-gray-300';
                                    $glowEffect = '';
                                    $grayscale = '';
                                } else {
                                    // Stage belum: hitam putih (grayscale)
                                    $circleBg = 'bg-gray-200';
                                    $circleBorder = 'border-2 border-gray-300';
                                    $glowEffect = '';
                                    $grayscale = 'grayscale';
                                }
                            @endphp
                            
                            <div class="flex flex-col items-center relative z-10">
                                <div class="w-12 h-12 rounded-full {{ $circleBg }} {{ $circleBorder }} {{ $glowEffect }} flex items-center justify-center mb-2 shadow-sm transition-all duration-300 overflow-hidden">
                                    <img src="{{ $stage['image'] }}" 
                                         alt="{{ $stage['name'] }}" 
                                         class="w-full h-full object-contain p-1 {{ $grayscale }} transition-all duration-300">
                                </div>
                                <p class="text-xs text-center text-gray-700 font-medium max-w-[60px]">{{ $stage['name'] }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Detail Button -->
            <div class="mb-6">
                <button onclick="toggleDetail()" class="w-full bg-[#F4C430] text-white py-3 rounded-lg font-semibold hover:bg-[#E6B020] transition-colors">
                    Detail
                </button>
            </div>

            <!-- Detail Section (Collapsible) -->
            <div id="detailSection" class="bg-white rounded-lg border border-gray-200 p-6 mb-6 hidden">
                <!-- Package Info -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-6 pb-6 border-b border-gray-200">
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900 mb-3">Pengirim</h3>
                        <div class="space-y-2 text-sm">
                            <p><span class="text-gray-600">Nama:</span> <span class="font-medium text-gray-900">{{ $shipment->sender_name }}</span></p>
                            <p><span class="text-gray-600">HP:</span> <a href="tel:{{ $shipment->sender_phone }}" class="font-medium text-blue-600">{{ $shipment->sender_phone }}</a></p>
                            <p><span class="text-gray-600">Alamat:</span></p>
                            <p class="font-medium text-gray-900">{{ $shipment->sender_address }}</p>
                        </div>
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900 mb-3">Penerima</h3>
                        <div class="space-y-2 text-sm">
                            <p><span class="text-gray-600">Nama:</span> <span class="font-medium text-gray-900">{{ $shipment->receiver_name }}</span></p>
                            <p><span class="text-gray-600">HP:</span> <a href="tel:{{ $shipment->receiver_phone }}" class="font-medium text-blue-600">{{ $shipment->receiver_phone }}</a></p>
                            <p><span class="text-gray-600">Alamat:</span></p>
                            <p class="font-medium text-gray-900">{{ $shipment->receiver_address }}</p>
                        </div>
                    </div>
                </div>

                <!-- COD Info -->
                @if($shipment->isCOD())
                    <div class="mb-6 pb-6 border-b border-gray-200">
                        <h3 class="text-sm font-semibold text-gray-900 mb-3">Informasi COD</h3>
                        <div class="flex justify-between items-center">
                            <div>
                                <p class="text-xs text-gray-600 mb-1">Nilai COD</p>
                                <p class="text-lg font-semibold text-gray-900">Rp {{ number_format($shipment->cod_amount, 0, ',', '.') }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-600 mb-1">Status</p>
                                <span class="inline-block px-3 py-1 text-xs font-medium rounded {{ $shipment->cod_status === 'lunas' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
                                    {{ $shipment->cod_status === 'lunas' ? 'Lunas' : 'Belum Lunas' }}
                                </span>
                            </div>
                        </div>
                    </div>
                @endif

                @if($shipment->courier)
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900 mb-3">Kurir</h3>
                        <p class="text-sm text-gray-900">{{ $shipment->courier->name }}</p>
                    </div>
                @endif
            </div>

            <!-- Timeline -->
            <div class="bg-white rounded-lg border border-gray-200 p-6">
                <div class="relative">
                    <!-- Timeline Line -->
                    <div class="absolute left-4 top-0 bottom-0 w-0.5 border-l-2 border-dashed border-gray-300"></div>
                    
                    <!-- Timeline Events (Reversed - newest first) -->
                    <div class="space-y-6">
                        @php
                            $histories = $shipment->statusHistories->sortByDesc('created_at');
                        @endphp
                        
                        @forelse($histories as $index => $history)
                            <div class="relative flex gap-4">
                                <!-- Timeline Dot -->
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 rounded-full {{ $index === 0 ? 'bg-[#F4C430]' : 'bg-white border-2 border-gray-300' }} flex items-center justify-center relative z-10">
                                        @if($index === 0)
                                            <div class="w-3 h-3 bg-white rounded-full"></div>
                                        @endif
                                    </div>
                                </div>
                                
                                <!-- Event Content -->
                                <div class="flex-1 min-w-0 pb-6 {{ !$loop->last ? 'border-b border-gray-100' : '' }}">
                                    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-start gap-2">
                                        <div class="flex-1">
                                            <p class="font-semibold text-gray-900 text-sm mb-1">
                                                {{ ucfirst(str_replace('_', ' ', $history->status)) }}
                                            </p>
                                            @if($history->notes)
                                                <p class="text-sm text-gray-600 mb-1">{{ $history->notes }}</p>
                                            @endif
                                            @if($history->location)
                                                <p class="text-xs text-gray-500">📍 {{ $history->location }}</p>
                                            @endif
                                            @if($history->updater)
                                                <p class="text-xs text-gray-500 mt-1">Oleh: {{ $history->updater->name }}</p>
                                            @endif
                                        </div>
                                        <div class="text-left sm:text-right flex-shrink-0">
                                            <p class="text-xs font-medium text-gray-900">
                                                {{ $history->created_at->format('Y-m-d') }}
                                            </p>
                                            <p class="text-xs text-gray-500">
                                                {{ $history->created_at->format('H:i:s') }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-4">
                                <p class="text-sm text-gray-500">Belum ada riwayat status</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Action Button -->
            <div class="mt-6">
                <a href="{{ route('tracking.index') }}" 
                   class="block w-full text-center bg-white border border-gray-300 text-gray-700 py-3 rounded-lg font-medium hover:bg-gray-50 transition-colors">
                    Lacak Paket Lain
                </a>
            </div>
        </main>

        <!-- Footer -->
        <footer class="bg-white border-t border-gray-200 mt-12">
            <div class="max-w-4xl mx-auto px-4 py-4">
                <p class="text-xs text-gray-500 text-center">
                    &copy; {{ date('Y') }} GPL Expres. All rights reserved.
                </p>
            </div>
        </footer>
    </div>

    <script>
        function toggleDetail() {
            const section = document.getElementById('detailSection');
            section.classList.toggle('hidden');
        }
    </script>
</body>
</html>
