<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>@yield('title', 'GPL Expres')</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        body {
            font-family: 'Inter', 'Poppins', sans-serif;
        }
    </style>
</head>
<body class="bg-gray-50 text-gray-900 antialiased" x-data="{ sidebarOpen: false }">
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        <aside 
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
            class="fixed lg:static inset-y-0 left-0 z-50 w-64 bg-white border-r border-gray-200 transform transition-transform duration-300 ease-in-out lg:translate-x-0"
        >
            <!-- Sidebar Header -->
            <div class="flex items-center justify-between h-16 px-6 border-b border-gray-200">
                <a href="{{ route('dashboard') }}" class="text-xl font-bold text-[#F4C430]">GPL Expres</a>
                <button @click="sidebarOpen = false" class="lg:hidden text-gray-500 hover:text-gray-700">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Sidebar Navigation -->
            <nav class="flex-1 px-4 py-6 space-y-1 overflow-y-auto" x-data="{ 
                openMenus: {
                    keuangan: false,
                    management: false,
                    setting: false,
                    laporan: false
                },
                toggleMenu(menu) {
                    this.openMenus[menu] = !this.openMenus[menu];
                }
            }">
                @if(Auth::user()->isOwner())
                    <!-- Owner Sidebar -->
                    <a href="{{ route('owner.dashboard') }}" class="flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('owner.*') ? 'bg-[#F4C430]/10 text-[#F4C430]' : 'text-gray-700 hover:bg-gray-100' }}">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                        </svg>
                        Dashboard
                    </a>
                    
                    <!-- Performance -->
                    <a href="{{ route('admin.performance.manager') }}" class="flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('admin.performance.*') ? 'bg-[#F4C430]/10 text-[#F4C430]' : 'text-gray-700 hover:bg-gray-100' }}">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                        Performance
                    </a>
                    
                    <!-- Laporan -->
                    <div>
                        <button @click="toggleMenu('laporan')" class="w-full flex items-center justify-between px-4 py-3 text-sm font-medium rounded-lg transition-colors text-gray-700 hover:bg-gray-100">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                Laporan
                            </div>
                            <svg class="w-4 h-4 transition-transform" :class="{ 'rotate-180': openMenus.laporan }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div x-show="openMenus.laporan" x-transition class="ml-4 mt-1 space-y-1">
                            <a href="{{ route('admin.reports.cod') }}" class="flex items-center px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-gray-100 transition-colors">
                                <span class="w-2 h-2 bg-gray-400 rounded-full mr-3"></span>
                                Laporan COD
                            </a>
                            <a href="{{ route('admin.reports.non-cod') }}" class="flex items-center px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-gray-100 transition-colors">
                                <span class="w-2 h-2 bg-gray-400 rounded-full mr-3"></span>
                                Laporan Non-COD
                            </a>
                        </div>
                    </div>
                    
                    <!-- Zones -->
                    <a href="{{ route('admin.zones.index') }}" class="flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('admin.zones.*') ? 'bg-[#F4C430]/10 text-[#F4C430]' : 'text-gray-700 hover:bg-gray-100' }}">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                        </svg>
                        Zones
                    </a>
                    
                    <!-- Keuangan -->
                    <a href="{{ route('admin.finance.index') }}" class="flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('admin.finance.*') ? 'bg-[#F4C430]/10 text-[#F4C430]' : 'text-gray-700 hover:bg-gray-100' }}">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Keuangan
                    </a>
                    
                    <!-- Management Data -->
                    <div>
                        <button @click="toggleMenu('management')" class="w-full flex items-center justify-between px-4 py-3 text-sm font-medium rounded-lg transition-colors text-gray-700 hover:bg-gray-100">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4" />
                                </svg>
                                Management Data
                            </div>
                            <svg class="w-4 h-4 transition-transform" :class="{ 'rotate-180': openMenus.management }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div x-show="openMenus.management" x-transition class="ml-4 mt-1 space-y-1">
                            <a href="{{ route('admin.branches.index') }}" class="flex items-center px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-gray-100 transition-colors">
                                <span class="w-2 h-2 bg-gray-400 rounded-full mr-3"></span>
                                Cabang
                            </a>
                            <a href="{{ route('admin.users.index') }}" class="flex items-center px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-gray-100 transition-colors">
                                <span class="w-2 h-2 bg-gray-400 rounded-full mr-3"></span>
                                User
                            </a>
                            <a href="{{ route('admin.kurirs.index') }}" class="flex items-center px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-gray-100 transition-colors">
                                <span class="w-2 h-2 bg-gray-400 rounded-full mr-3"></span>
                                Kurir
                            </a>
                            <a href="{{ route('admin.pricing.index') }}" class="flex items-center px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-gray-100 transition-colors">
                                <span class="w-2 h-2 bg-gray-400 rounded-full mr-3"></span>
                                Tarif Harga
                            </a>
                        </div>
                    </div>
                    
                    <!-- Setting -->
                    <div>
                        <button @click="toggleMenu('setting')" class="w-full flex items-center justify-between px-4 py-3 text-sm font-medium rounded-lg transition-colors text-gray-700 hover:bg-gray-100">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                Setting
                            </div>
                            <svg class="w-4 h-4 transition-transform" :class="{ 'rotate-180': openMenus.setting }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div x-show="openMenus.setting" x-transition class="ml-4 mt-1 space-y-1">
                            <a href="{{ route('owner.settings.fonnte') }}" class="flex items-center px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-gray-100 transition-colors">
                                <span class="w-2 h-2 bg-gray-400 rounded-full mr-3"></span>
                                Setting Fonnte
                            </a>
                            <a href="{{ route('owner.settings.midtrans') }}" class="flex items-center px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-gray-100 transition-colors">
                                <span class="w-2 h-2 bg-gray-400 rounded-full mr-3"></span>
                                Setting Midtrans
                            </a>
                        </div>
                    </div>
                    
                @elseif(Auth::user()->isManager())
                    <!-- Manager Sidebar -->
                    <a href="{{ route('manager.dashboard') }}" class="flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('manager.dashboard') ? 'bg-[#F4C430]/10 text-[#F4C430]' : 'text-gray-700 hover:bg-gray-100' }}">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                        </svg>
                        Dashboard
                    </a>
                    
                    <!-- Performance -->
                    <a href="{{ route('manager.performance.manager') }}" class="flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('manager.performance.*') ? 'bg-[#F4C430]/10 text-[#F4C430]' : 'text-gray-700 hover:bg-gray-100' }}">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                        Performance
                    </a>
                    
                    <!-- Data Barang Keluar Masuk -->
                    <a href="{{ route('manager.barang-keluar-masuk') }}" class="flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('manager.barang-keluar-masuk') ? 'bg-[#F4C430]/10 text-[#F4C430]' : 'text-gray-700 hover:bg-gray-100' }}">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                        Data Barang Keluar Masuk
                    </a>
                    
                    <!-- Data Admin -->
                    <a href="{{ route('admin.users.index', ['role' => 'admin']) }}" class="flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('admin.users.*') && request('role') == 'admin' ? 'bg-[#F4C430]/10 text-[#F4C430]' : 'text-gray-700 hover:bg-gray-100' }}">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                        Data Admin
                    </a>
                    
                    <!-- Data Kurir -->
                    <a href="{{ route('admin.kurirs.index', ['role' => 'kurir']) }}" class="flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('admin.kurirs.*') && request('role') == 'kurir' ? 'bg-[#F4C430]/10 text-[#F4C430]' : 'text-gray-700 hover:bg-gray-100' }}">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        Data Kurir
                    </a>
                    
                    <!-- Laporan -->
                    <div>
                        <button @click="toggleMenu('laporan')" class="w-full flex items-center justify-between px-4 py-3 text-sm font-medium rounded-lg transition-colors text-gray-700 hover:bg-gray-100">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                Laporan
                            </div>
                            <svg class="w-4 h-4 transition-transform" :class="{ 'rotate-180': openMenus.laporan }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div x-show="openMenus.laporan" x-transition class="ml-4 mt-1 space-y-1">
                            <a href="{{ route('manager.reports.cod') }}" class="flex items-center px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-gray-100 transition-colors">
                                <span class="w-2 h-2 bg-gray-400 rounded-full mr-3"></span>
                                Laporan COD
                            </a>
                            <a href="{{ route('manager.reports.non-cod') }}" class="flex items-center px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-gray-100 transition-colors">
                                <span class="w-2 h-2 bg-gray-400 rounded-full mr-3"></span>
                                Laporan Non-COD
                            </a>
                        </div>
                    </div>
                    
                    <!-- Setting -->
                    <div>
                        <button @click="toggleMenu('setting')" class="w-full flex items-center justify-between px-4 py-3 text-sm font-medium rounded-lg transition-colors text-gray-700 hover:bg-gray-100">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                Setting
                            </div>
                            <svg class="w-4 h-4 transition-transform" :class="{ 'rotate-180': openMenus.setting }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div x-show="openMenus.setting" x-transition class="ml-4 mt-1 space-y-1">
                            <a href="{{ route('profile.index') }}" class="flex items-center px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-gray-100 transition-colors">
                                <span class="w-2 h-2 bg-gray-400 rounded-full mr-3"></span>
                                Profil
                            </a>
                            <a href="#" class="flex items-center px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-gray-100 transition-colors">
                                <span class="w-2 h-2 bg-gray-400 rounded-full mr-3"></span>
                                Pengaturan Cabang
                            </a>
                        </div>
                    </div>
                    
                @elseif(Auth::user()->isAdmin())
                    <!-- Admin Sidebar -->
                    <a href="{{ route('dashboard') }}" class="flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('dashboard') ? 'bg-[#F4C430]/10 text-[#F4C430]' : 'text-gray-700 hover:bg-gray-100' }}">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                        </svg>
                        Dashboard
                    </a>
                    <a href="{{ route('admin.shipments.index') }}" class="flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('admin.shipments.*') ? 'bg-[#F4C430]/10 text-[#F4C430]' : 'text-gray-700 hover:bg-gray-100' }}">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Manajemen Paket
                    </a>
                    <a href="{{ route('admin.shipments.assign.form') }}" class="flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('admin.shipments.assign.*') ? 'bg-[#F4C430]/10 text-[#F4C430]' : 'text-gray-700 hover:bg-gray-100' }}">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                        Assign Paket
                    </a>
                    
                    <!-- Setting Profil -->
                    <a href="{{ route('profile.index') }}" class="flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('profile.*') ? 'bg-[#F4C430]/10 text-[#F4C430]' : 'text-gray-700 hover:bg-gray-100' }}">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        Setting Profil
                    </a>
                @elseif(Auth::user()->isKurir())
                    <!-- Kurir Sidebar -->
                    <a href="{{ route('courier.dashboard') }}" class="flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('courier.dashboard') ? 'bg-[#F4C430]/10 text-[#F4C430]' : 'text-gray-700 hover:bg-gray-100' }}">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                        </svg>
                        Dashboard
                    </a>
                    <a href="{{ route('courier.scan') }}" class="flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('courier.scan*') ? 'bg-[#F4C430]/10 text-[#F4C430]' : 'text-gray-700 hover:bg-gray-100' }}">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                        </svg>
                        Scan Resi
                    </a>
                    <a href="{{ route('courier.my-packages') }}" class="flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('courier.my-packages') ? 'bg-[#F4C430]/10 text-[#F4C430]' : 'text-gray-700 hover:bg-gray-100' }}">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                        Paket Saya
                    </a>
                    <a href="{{ route('profile.index') }}" class="flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('profile.*') ? 'bg-[#F4C430]/10 text-[#F4C430]' : 'text-gray-700 hover:bg-gray-100' }}">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        Setting Profil
                    </a>
                @elseif(Auth::user()->isUser())
                    <!-- User Sidebar -->
                    <a href="{{ route('dashboard') }}" class="flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('dashboard') ? 'bg-[#F4C430]/10 text-[#F4C430]' : 'text-gray-700 hover:bg-gray-100' }}">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                        </svg>
                        Dashboard
                    </a>
                    <a href="{{ route('user.packages.history') }}" class="flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('user.packages.*') ? 'bg-[#F4C430]/10 text-[#F4C430]' : 'text-gray-700 hover:bg-gray-100' }}">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Riwayat Paket
                    </a>
                    <a href="{{ route('profile.index') }}" class="flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('profile.*') ? 'bg-[#F4C430]/10 text-[#F4C430]' : 'text-gray-700 hover:bg-gray-100' }}">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        Setting Profil
                    </a>
                @endif
            </nav>

            <!-- Sidebar Footer -->
            <div class="px-4 py-4 border-t border-gray-200">
                <div class="flex items-center mb-3">
                    <div class="flex-shrink-0">
                        <div class="w-10 h-10 bg-[#F4C430]/10 rounded-full flex items-center justify-center">
                            <span class="text-[#F4C430] font-semibold text-sm">{{ substr(Auth::user()->name, 0, 1) }}</span>
                        </div>
                    </div>
                    <div class="ml-3 flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900 truncate">{{ Auth::user()->name }}</p>
                        <p class="text-xs text-gray-500 truncate">{{ Auth::user()->email }}</p>
                    </div>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full flex items-center justify-center px-4 py-2 text-sm font-medium text-red-600 bg-red-50 rounded-lg hover:bg-red-100 transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                        Logout
                    </button>
                </form>
            </div>
        </aside>

        <!-- Overlay for mobile -->
        <div 
            x-show="sidebarOpen"
            @click="sidebarOpen = false"
            x-transition:enter="transition-opacity ease-linear duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition-opacity ease-linear duration-300"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-gray-600 bg-opacity-75 z-40 lg:hidden"
            style="display: none;"
        ></div>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Top Bar -->
            <header class="bg-white border-b border-gray-200 h-16 flex items-center justify-between px-4 lg:px-8">
                <button @click="sidebarOpen = true" class="lg:hidden text-gray-500 hover:text-gray-700">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
                <div class="flex-1 lg:ml-0 ml-4">
                    <h1 class="text-lg font-semibold text-gray-900">@yield('page-title', 'Dashboard')</h1>
                </div>
                <div class="flex items-center space-x-3 lg:space-x-4">
                    <div class="flex items-center space-x-3">
                        <div class="text-right hidden sm:block">
                            <p class="text-sm font-medium text-gray-900">{{ Auth::user()->name }}</p>
                            <p class="text-xs text-gray-500">{{ Auth::user()->email }}</p>
                        </div>
                        <div class="flex-shrink-0">
                            <div class="w-10 h-10 bg-[#F4C430]/10 rounded-full flex items-center justify-center border-2 border-[#F4C430]/20 overflow-hidden">
                                @if(Auth::user()->avatar_url)
                                    <img src="{{ Auth::user()->avatar_url }}" alt="{{ Auth::user()->name }}" class="w-full h-full rounded-full object-cover" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                    <span class="text-[#F4C430] font-semibold text-sm" style="display: none;">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</span>
                                @else
                                    <span class="text-[#F4C430] font-semibold text-sm">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    <span class="hidden lg:inline-block px-3 py-1 text-xs font-semibold rounded-full 
                        @if(Auth::user()->isOwner()) bg-purple-100 text-purple-700
                        @elseif(Auth::user()->isManager()) bg-blue-100 text-blue-700
                        @elseif(Auth::user()->isAdmin()) bg-[#F4C430]/10 text-[#F4C430]
                        @elseif(Auth::user()->isKurir()) bg-green-100 text-green-700
                        @else bg-gray-100 text-gray-700
                        @endif">
                        @if(Auth::user()->isOwner()) Owner
                        @elseif(Auth::user()->isManager()) Manager
                        @elseif(Auth::user()->isAdmin()) Admin
                        @elseif(Auth::user()->isKurir()) Kurir
                        @else User
                        @endif
                    </span>
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-1 overflow-y-auto bg-gray-50 p-4 lg:p-8">
                @if(session('success'))
                    <div class="mb-6 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg">
                        {{ session('success') }}
                    </div>
                @endif

                @if($errors->any())
                    <div class="mb-6 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg">
                        <ul class="list-disc list-inside">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>

