@extends('layouts.app')

@section('title', 'Alias Departemen - Pojok Ilmu MWT')

@section('content')
    <div class="page-shell max-w-5xl">
        <a href="{{ route('admin.imports.create', 'trainings') }}" class="mb-3 inline-flex items-center gap-1 text-xs font-bold text-gray-500 hover:text-brand-dark"><x-icon name="back" class="h-3.5 w-3.5" />Kembali ke impor training</a>
        <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="eyebrow">PENGATURAN DATA</p>
                <h1 class="page-title">Alias departemen</h1>
                <p class="page-description">Tambahkan singkatan yang boleh dipakai saat impor. Nama resmi dari HaRis tetap menjadi acuan sistem.</p>
            </div>
        </div>

        <x-card>
            <x-slot name="header"><div><h2 class="font-heading text-lg font-bold">Tambah alias</h2><p class="mt-1 text-xs text-gray-500">Pencocokan dilakukan secara utuh. Alias RM tidak akan pernah dianggap sebagai bagian dari Information Technology.</p></div></x-slot>
            <form action="{{ route('admin.department-aliases.store') }}" method="POST" class="grid gap-4 md:grid-cols-[minmax(0,1fr)_minmax(0,1fr)_auto] md:items-end">
                @csrf
                <div>
                    <label class="mb-1.5 block text-sm font-medium" for="departemen">Nama resmi HaRis</label>
                    <x-select-menu id="departemen" name="departemen" :options="$departments->mapWithKeys(fn ($department) => [$department => $department])->all()" :selected="old('departemen')" placeholder="Pilih departemen" />
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium" for="alias">Alias atau singkatan</label>
                    <x-input id="alias" name="alias" :value="old('alias')" placeholder="Contoh: IT" required />
                </div>
                <x-button type="submit"><x-icon name="plus" class="h-4 w-4" />Tambah alias</x-button>
            </form>
        </x-card>

        <x-card>
            <x-slot name="header"><div><h2 class="font-heading text-lg font-bold">Alias yang aktif</h2><p class="mt-1 text-xs text-gray-500">Alias bisa dipakai pada kolom departemen di template training.</p></div></x-slot>
            <x-table :headers="['Nama resmi HaRis', 'Alias', 'Aksi']">
                @forelse ($aliases as $alias)
                    <tr>
                        <td class="font-medium">{{ $alias->departemen }}</td>
                        <td><span class="rounded-full bg-green-100 px-2.5 py-1 font-mono text-xs font-semibold text-brand-dark dark:bg-green-950 dark:text-green-200">{{ $alias->alias }}</span></td>
                        <td class="w-16 text-center">
                            <form action="{{ route('admin.department-aliases.destroy', $alias) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <x-button type="submit" variant="ghost" size="sm" aria-label="Hapus alias {{ $alias->alias }}"><x-icon name="trash" class="h-4 w-4 text-red-600" /></x-button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="3"><x-empty-state title="Belum ada alias departemen" description="Tambahkan alias bila nama resmi HaRis dan singkatan yang biasa dipakai berbeda." /></td></tr>
                @endforelse
            </x-table>
        </x-card>
    </div>
@endsection
