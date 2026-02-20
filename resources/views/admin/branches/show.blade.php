@extends('layouts.app')

@section('title', 'Kelola Kurir/Admin Cabang - GPL Express')
@section('page-title', 'Kelola Kurir/Admin Cabang')

@section('content')
<div class="max-w-6xl mx-auto">
    <div class="mb-6 lg:mb-8">
        <a href="{{ route('admin.branches.index') }}" class="text-[#F4C430] hover:underline mb-4 inline-block text-sm lg:text-base">← Kembali</a>
        <h1 class="text-2xl lg:text-3xl font-bold text-gray-900">Kelola Kurir/Admin - {{ $branch->name }}</h1>
        <p class="text-gray-600 mt-2">Kode: {{ $branch->code }}</p>
    </div>

    <!-- Branch Info -->
    <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <p class="text-sm text-gray-600">Alamat</p>
                <p class="font-medium text-gray-900">{{ $branch->address }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-600">Manager</p>
                <p class="font-medium text-gray-900">{{ $branch->manager ? $branch->manager->name : '-' }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-600">Total Kurir/Admin</p>
                <p class="font-medium text-gray-900">{{ $branch->users->count() }} kurir/admin</p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Admin Section -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-lg font-bold text-gray-900">Admin di Cabang</h2>
                <button onclick="openAddModal('admin')" class="bg-[#F4C430] text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-[#E6B020] transition-colors">
                    + Tambah Admin
                </button>
            </div>

            @if($branch->admins->count() > 0)
                <div class="space-y-2">
                    @foreach($branch->admins as $admin)
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <div>
                                <p class="font-medium text-gray-900">{{ $admin->name }}</p>
                                <p class="text-xs text-gray-500">{{ $admin->email }}</p>
                            </div>
                            <form method="POST" action="{{ route('admin.branches.remove-user', [$branch, $admin]) }}" class="inline" onsubmit="return confirm('Hapus admin dari cabang ini?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-800 text-sm">Hapus</button>
                            </form>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-gray-500 text-center py-4">Belum ada admin di cabang ini</p>
            @endif
        </div>

        <!-- Kurir Section -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-lg font-bold text-gray-900">Kurir di Cabang</h2>
                <button onclick="openAddModal('kurir')" class="bg-[#F4C430] text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-[#E6B020] transition-colors">
                    + Tambah Kurir
                </button>
            </div>

            @if($branch->kurirs->count() > 0)
                <div class="space-y-2">
                    @foreach($branch->kurirs as $kurir)
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <div>
                                <p class="font-medium text-gray-900">{{ $kurir->name }}</p>
                                <p class="text-xs text-gray-500">{{ $kurir->email }}</p>
                            </div>
                            <form method="POST" action="{{ route('admin.branches.remove-user', [$branch, $kurir]) }}" class="inline" onsubmit="return confirm('Hapus kurir dari cabang ini?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-800 text-sm">Hapus</button>
                            </form>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-gray-500 text-center py-4">Belum ada kurir di cabang ini</p>
            @endif
        </div>
    </div>
</div>

<!-- Add User Modal -->
<div 
    x-data="{ 
        open: false, 
        role: '',
        openModal(r) {
            this.role = r;
            this.open = true;
        }
    }"
    x-show="open"
    x-transition
    @click.away="open = false"
    class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4"
    style="display: none;"
    id="addModal"
>
    <div class="bg-white rounded-xl shadow-xl max-w-md w-full p-6 max-h-[90vh] overflow-y-auto" @click.stop>
        <h3 class="text-xl font-bold text-gray-900 mb-4">Tambah <span x-text="role === 'admin' ? 'Admin' : 'Kurir'"></span></h3>

        <form method="POST" action="{{ route('admin.branches.assign-users', $branch) }}" id="assignForm">
            @csrf

            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Pilih <span x-text="role === 'admin' ? 'Admin' : 'Kurir'"></span></label>
                <div class="max-h-60 overflow-y-auto border border-gray-300 rounded-lg">
                    <!-- Admin List -->
                    <template x-if="role === 'admin'">
                        <div>
                            @forelse($availableAdmins as $admin)
                                <label class="flex items-center p-3 hover:bg-gray-50 cursor-pointer border-b border-gray-200 last:border-0">
                                    <input type="checkbox" name="user_ids[]" value="{{ $admin->id }}" 
                                        {{ $admin->branch_id == $branch->id ? 'checked' : '' }}
                                        class="rounded border-gray-300 text-[#F4C430] focus:ring-[#F4C430]">
                                    <div class="ml-3 flex-1">
                                        <p class="font-medium text-gray-900">{{ $admin->name }}</p>
                                        <p class="text-xs text-gray-500">{{ $admin->email }}</p>
                                        @if($admin->branch_id && $admin->branch_id != $branch->id)
                                            <p class="text-xs text-orange-600">Cabang: {{ $admin->branch ? $admin->branch->name : '-' }}</p>
                                        @endif
                                    </div>
                                </label>
                            @empty
                                <p class="p-4 text-sm text-gray-500 text-center">Tidak ada admin yang tersedia</p>
                            @endforelse
                        </div>
                    </template>
                    <!-- Kurir List -->
                    <template x-if="role === 'kurir'">
                        <div>
                            @forelse($availableKurirs as $kurir)
                                <label class="flex items-center p-3 hover:bg-gray-50 cursor-pointer border-b border-gray-200 last:border-0">
                                    <input type="checkbox" name="user_ids[]" value="{{ $kurir->id }}"
                                        {{ $kurir->branch_id == $branch->id ? 'checked' : '' }}
                                        class="rounded border-gray-300 text-[#F4C430] focus:ring-[#F4C430]">
                                    <div class="ml-3 flex-1">
                                        <p class="font-medium text-gray-900">{{ $kurir->name }}</p>
                                        <p class="text-xs text-gray-500">{{ $kurir->email }}</p>
                                        @if($kurir->branch_id && $kurir->branch_id != $branch->id)
                                            <p class="text-xs text-orange-600">Cabang: {{ $kurir->branch ? $kurir->branch->name : '-' }}</p>
                                        @endif
                                    </div>
                                </label>
                            @empty
                                <p class="p-4 text-sm text-gray-500 text-center">Tidak ada kurir yang tersedia</p>
                            @endforelse
                        </div>
                    </template>
                </div>
            </div>

            <div class="flex gap-4">
                <button type="submit" class="flex-1 bg-[#F4C430] text-white px-6 py-3 rounded-lg font-semibold hover:bg-[#E6B020] transition-colors">
                    Simpan
                </button>
                <button type="button" @click="open = false" class="px-6 py-3 bg-gray-200 text-gray-700 rounded-lg font-semibold hover:bg-gray-300 transition-colors">
                    Batal
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openAddModal(role) {
        const modal = document.getElementById('addModal');
        if (window.Alpine && modal) {
            const component = Alpine.$data(modal);
            if (component && component.openModal) {
                component.openModal(role);
            }
        }
    }
</script>
@endsection

