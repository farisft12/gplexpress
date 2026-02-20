@extends('layouts.app')

@section('title', 'Detail User - GPL Express')
@section('page-title', 'Detail User')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="mb-6 lg:mb-8">
        <a href="{{ route('admin.users.index') }}" class="text-[#F4C430] hover:underline mb-4 inline-block text-sm lg:text-base">← Kembali</a>
        <h1 class="text-2xl lg:text-3xl font-bold text-gray-900">Detail User</h1>
    </div>

    <div class="bg-white rounded-xl shadow-sm p-6 lg:p-8">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Nama</label>
                <p class="text-lg font-semibold text-gray-900">{{ $user->name }}</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                <p class="text-lg text-gray-900">{{ $user->email }}</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Role</label>
                <span class="px-3 py-1 text-sm font-semibold rounded-full 
                    {{ $user->role === 'owner' ? 'bg-purple-100 text-purple-700' : 
                       ($user->role === 'manager' ? 'bg-blue-100 text-blue-700' : 
                       ($user->role === 'admin' ? 'bg-[#F4C430]/10 text-[#F4C430]' : 
                       ($user->role === 'kurir' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700'))) }}">
                    {{ ucfirst($user->role) }}
                </span>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                <span class="px-3 py-1 text-sm font-semibold rounded-full {{ $user->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                    {{ $user->status === 'active' ? 'Aktif' : 'Tidak Aktif' }}
                </span>
            </div>

            @if($user->branch)
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Cabang</label>
                <p class="text-lg text-gray-900">{{ $user->branch->name }}</p>
            </div>
            @endif

            @if($user->phone)
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Telepon</label>
                <p class="text-lg text-gray-900">{{ $user->phone }}</p>
            </div>
            @endif

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Dibuat</label>
                <p class="text-lg text-gray-900">{{ $user->created_at->format('d M Y H:i') }}</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Diperbarui</label>
                <p class="text-lg text-gray-900">{{ $user->updated_at->format('d M Y H:i') }}</p>
            </div>
        </div>

        @if(Auth::user()->isOwner())
        <div class="flex gap-4 mt-8">
            <a href="{{ route('admin.users.edit', $user) }}" class="bg-[#F4C430] text-white px-8 py-3 rounded-lg font-semibold hover:bg-[#E6B020] transition-colors">
                Edit
            </a>
            <a href="{{ route('admin.users.index') }}" class="bg-gray-200 text-gray-700 px-8 py-3 rounded-lg font-semibold hover:bg-gray-300 transition-colors">
                Kembali
            </a>
        </div>
        @else
        <div class="flex gap-4 mt-8">
            <a href="{{ route('admin.users.index') }}" class="bg-gray-200 text-gray-700 px-8 py-3 rounded-lg font-semibold hover:bg-gray-300 transition-colors">
                Kembali
            </a>
        </div>
        @endif
    </div>
</div>
@endsection




