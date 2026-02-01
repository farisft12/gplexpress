<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lacak Paket - GPL Expres</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { font-family: 'Inter', 'Poppins', sans-serif; }
    </style>
</head>
<body class="bg-gray-50">
    <div class="min-h-screen flex flex-col">
        <!-- Header -->
        <header class="bg-white shadow-sm">
            <div class="container mx-auto px-4 py-4">
                <a href="{{ route('home') }}" class="text-xl font-bold text-gray-900">GPL Expres</a>
            </div>
        </header>

        <!-- Main Content -->
        <main class="flex-1 container mx-auto px-4 py-12">
            <div class="max-w-2xl mx-auto">
                <div class="text-center mb-8">
                    <h1 class="text-4xl font-bold text-gray-900 mb-4">Lacak Paket Anda</h1>
                    <p class="text-gray-600">Masukkan nomor resi untuk melihat status pengiriman</p>
                </div>

                <!-- Tracking Form -->
                <div class="bg-white rounded-xl shadow-lg p-8 mb-8">
                    <form method="POST" action="{{ route('tracking.track') }}" class="space-y-6">
                        @csrf
                        
                        @if ($errors->any())
                            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
                                <strong class="font-semibold">Error:</strong>
                                <span class="block sm:inline">{{ $errors->first() }}</span>
                            </div>
                        @endif

                        <div>
                            <label for="resi_number" class="block text-sm font-medium text-gray-700 mb-2">
                                Nomor Resi
                            </label>
                            <input 
                                type="text" 
                                id="resi_number" 
                                name="resi_number" 
                                value="{{ old('resi_number', request('resi_number')) }}"
                                required
                                autofocus
                                placeholder="Contoh: GPL20240116123456"
                                class="w-full px-4 py-4 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-[#F4C430] focus:border-[#F4C430] outline-none text-lg @error('resi_number') border-red-500 @enderror"
                            >
                            @error('resi_number')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <button 
                            type="submit" 
                            class="w-full bg-[#F4C430] text-white px-6 py-4 rounded-lg font-semibold text-lg hover:bg-[#E6B020] transition-colors shadow-md"
                        >
                            Lacak Paket
                        </button>
                    </form>
                </div>

                <!-- Info -->
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 text-center">
                    <p class="text-sm text-blue-800">
                        <strong>Tips:</strong> Nomor resi biasanya dimulai dengan "GPL" diikuti tanggal dan kode unik.
                    </p>
                </div>
            </div>
        </main>

        <!-- Footer -->
        <footer class="bg-white border-t border-gray-200 mt-12">
            <div class="container mx-auto px-4 py-6 text-center text-sm text-gray-600">
                <p>&copy; {{ date('Y') }} GPL Expres. All rights reserved.</p>
            </div>
        </footer>
    </div>
</body>
</html>

