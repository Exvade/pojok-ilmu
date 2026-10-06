@extends('layouts.app')

@section('title', 'Kelola Role · Pojok Ilmu')

@section('content')
<div class="page-shell">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-brand-dark">Superadmin</p>
            <h1 class="page-title mt-1">Kelola role pengelola</h1>
            <p class="page-description">Berikan akses Kompetensi & Target, Kurikulum & Modul, atau keduanya kepada karyawan aktif dari HaRis.</p>
        </div>
        <x-button :href="route('admin.dashboard')" variant="outline">Kembali ke matriks</x-button>
    </div>

    <x-card>
        <form method="GET" class="flex flex-col gap-3 sm:flex-row">
            <label class="sr-only" for="role-search">Cari karyawan</label>
            <x-input id="role-search" name="search" :value="$search" placeholder="Cari nama, NIK, atau departemen" class="sm:max-w-md" />
            <x-button type="submit">Cari</x-button>
            @if($search)<x-button :href="route('admin.roles.index')" variant="outline">Reset</x-button>@endif
        </form>
    </x-card>

    <x-card body-class="p-0">
        <x-table>
            <x-slot name="head"><tr><th>Karyawan</th><th>Departemen / Jabatan</th><th>Akses saat ini</th><th class="text-right">Tindakan</th></tr></x-slot>
            @forelse($manpowers as $person)
                <tr>
                    <td><p class="font-semibold text-gray-900 dark:text-white">{{ $person->nama }}</p><p class="mt-1 font-mono text-xs text-gray-500">{{ $person->nik }}</p></td>
                    <td><p>{{ $person->departemen }}</p><p class="mt-1 text-xs text-gray-500">{{ $person->jabatan ?? $person->bagian }}</p></td>
                    <td>
                        @if($person->user?->is_superadmin)
                            <span class="inline-flex rounded-md bg-amber-100 px-2 py-1 text-xs font-semibold text-amber-800">Superadmin</span>
                        @elseif($person->user?->can_manage_competency || $person->user?->can_manage_curriculum)
                            <div class="flex flex-wrap gap-1">
                                @if($person->user->can_manage_competency)<span class="rounded-md bg-blue-100 px-2 py-1 text-xs font-medium text-blue-800">Kompetensi</span>@endif
                                @if($person->user->can_manage_curriculum)<span class="rounded-md bg-green-100 px-2 py-1 text-xs font-medium text-green-800">Kurikulum</span>@endif
                            </div>
                        @else
                            <span class="text-sm text-gray-500">Karyawan</span>
                        @endif
                    </td>
                    <td class="text-right">
                        @if($person->user?->is_superadmin)
                            <span class="text-xs text-gray-400">Terkunci</span>
                        @else
                            <x-button :href="route('admin.roles.edit', $person)" size="sm" variant="outline">Atur akses</x-button>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="4"><x-empty-state title="Karyawan tidak ditemukan" description="Ubah kata kunci pencarian atau sinkronkan HaRis." /></td></tr>
            @endforelse
        </x-table>
        <div class="border-t border-gray-200 px-5 py-4 dark:border-gray-700">{{ $manpowers->links() }}</div>
    </x-card>
</div>
@endsection
