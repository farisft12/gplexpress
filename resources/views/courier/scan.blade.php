@extends('layouts.app')

@section('title', 'Scan Resi - Ambil Paket - GPL Expres')
@section('page-title', 'Scan Resi - Ambil Paket')

@section('content')
<div>
    <div class="mb-6 lg:mb-8">
        <h1 class="text-2xl lg:text-3xl font-bold text-gray-900">Scan Resi - Ambil Paket</h1>
        <p class="text-sm text-gray-600 mt-2">Scan atau masukkan nomor resi untuk mengambil paket</p>
    </div>

    @if(session('success'))
        <div class="bg-green-50 border-l-4 border-green-400 text-green-700 p-4 mb-6 rounded-lg" role="alert">
            <p class="font-bold">Berhasil!</p>
            <p>{{ session('success') }}</p>
        </div>
    @endif

    @if($errors->any())
        <div class="bg-red-50 border-l-4 border-red-400 text-red-700 p-4 mb-6 rounded-lg" role="alert">
            <p class="font-bold">Error!</p>
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Scan Form -->
    <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
        <form method="POST" action="{{ route('courier.scan.resi') }}" id="scanForm" class="space-y-4">
            @csrf
            <div>
                <label for="resi_number" class="block text-sm font-medium text-gray-700 mb-2">
                    Nomor Resi
                </label>
                <div class="flex space-x-2">
                    <input 
                        type="text" 
                        id="resi_number" 
                        name="resi_number" 
                        value="{{ old('resi_number') }}"
                        placeholder="Masukkan atau scan nomor resi"
                        class="flex-1 px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#F4C430] focus:border-transparent outline-none text-lg"
                        autofocus
                        required
                    >
                    <button 
                        type="button"
                        onclick="openQRScanner()"
                        class="px-4 py-3 bg-blue-600 text-white rounded-lg font-semibold hover:bg-blue-700 transition-colors"
                        title="Scan QR Code"
                    >
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path>
                        </svg>
                    </button>
                </div>
                <p class="text-xs text-gray-500 mt-1">Gunakan scanner barcode, scan QR code, atau ketik manual nomor resi</p>
            </div>
            <button 
                type="submit" 
                class="w-full bg-[#F4C430] text-white px-6 py-3 rounded-lg font-semibold hover:bg-[#E6B020] transition-colors"
            >
                Ambil Paket
            </button>
        </form>
    </div>

    <!-- QR Scanner Modal -->
    <div id="qrScannerModal" class="fixed inset-0 bg-gray-900 bg-opacity-75 z-50 hidden">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-xl shadow-xl max-w-md w-full">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">Scan QR Code</h3>
                        <button onclick="closeQRScanner()" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                    <div class="mb-4">
                        <video id="qrVideo" class="w-full rounded-lg" autoplay playsinline></video>
                        <canvas id="qrCanvas" class="hidden"></canvas>
                    </div>
                    <p class="text-sm text-gray-600 text-center">Arahkan kamera ke QR code resi</p>
                </div>
            </div>
        </div>
    </div>

    <!-- My Packages Link -->
    <div class="bg-white rounded-xl shadow-sm p-6">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-lg font-semibold text-gray-900 mb-1">Paket Saya</h3>
                <p class="text-sm text-gray-600">Lihat daftar paket yang sudah Anda ambil</p>
            </div>
            <a href="{{ route('courier.my-packages') }}" class="bg-gray-900 text-white px-4 py-2 rounded-lg font-semibold hover:bg-gray-800 transition-colors">
                Lihat Paket
            </a>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.min.js"></script>
<script>
let qrStream = null;
let qrScanning = false;

// Auto-focus on input and handle barcode scanner
document.addEventListener('DOMContentLoaded', function() {
    const resiInput = document.getElementById('resi_number');
    
    // Auto-submit when Enter is pressed (for barcode scanner)
    resiInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter' && this.value.trim()) {
            e.preventDefault();
            this.form.submit();
        }
    });
    
    // Focus on input
    resiInput.focus();
});

function openQRScanner() {
    const modal = document.getElementById('qrScannerModal');
    const video = document.getElementById('qrVideo');
    modal.classList.remove('hidden');
    qrScanning = true;
    
    navigator.mediaDevices.getUserMedia({ 
        video: { 
            facingMode: 'environment' // Use back camera on mobile
        } 
    })
    .then(function(stream) {
        qrStream = stream;
        video.srcObject = stream;
        video.play();
        scanQRCode();
    })
    .catch(function(err) {
        alert('Tidak dapat mengakses kamera: ' + err.message);
        closeQRScanner();
    });
}

function closeQRScanner() {
    qrScanning = false;
    if (qrStream) {
        qrStream.getTracks().forEach(track => track.stop());
        qrStream = null;
    }
    document.getElementById('qrScannerModal').classList.add('hidden');
    const video = document.getElementById('qrVideo');
    video.srcObject = null;
}

function scanQRCode() {
    const video = document.getElementById('qrVideo');
    const canvas = document.getElementById('qrCanvas');
    const context = canvas.getContext('2d');
    
    if (!qrScanning || !video.videoWidth) {
        return;
    }
    
    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;
    context.drawImage(video, 0, 0, canvas.width, canvas.height);
    
    const imageData = context.getImageData(0, 0, canvas.width, canvas.height);
    const code = jsQR(imageData.data, imageData.width, imageData.height);
    
    if (code) {
        // QR code detected
        document.getElementById('resi_number').value = code.data;
        closeQRScanner();
        // Auto submit form
        document.getElementById('scanForm').submit();
    } else {
        // Continue scanning
        requestAnimationFrame(scanQRCode);
    }
}
</script>
@endsection


