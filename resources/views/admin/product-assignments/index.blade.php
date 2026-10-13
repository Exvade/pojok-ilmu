@extends('layouts.app')

@section('title', 'Penugasan Product - Pojok Ilmu')

@section('content')
    <div class="page-shell" x-data>
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div><h1 class="page-title">Penugasan product</h1><p class="page-description">Tentukan manpower yang wajib mempelajari setiap product. Pengalaman kerja tidak menggantikan proses training pada sistem.</p></div>
            <form method="GET" class="flex w-full flex-col gap-2 sm:w-auto sm:flex-row sm:items-center"><x-select name="departemen" aria-label="Filter departemen" class="h-10 w-full py-0 sm:w-72"><option value="">Semua departemen</option>@foreach($departments as $item)<option value="{{ $item }}" @selected($department === $item)>{{ $item }}</option>@endforeach</x-select><x-button type="submit" variant="secondary" class="h-10">Tampilkan</x-button></form>
        </div>

        <x-card bodyClass="p-5">
            <div class="grid gap-4 md:grid-cols-3"><div><p class="text-sm font-semibold">Cara pakai</p><p class="mt-1 text-xs leading-5 text-gray-500">Pilih product, lalu pilih manpower yang wajib menjalankan training tersebut.</p></div><div><p class="text-sm font-semibold">Balok 1</p><p class="mt-1 text-xs leading-5 text-gray-500">Dicapai setelah post-test lulus.</p></div><div><p class="text-sm font-semibold">Balok 2</p><p class="mt-1 text-xs leading-5 text-gray-500">Dicapai setelah OJT disetujui dan menunjukkan manpower mampu menjalankan product.</p></div></div>
        </x-card>

        <div class="grid gap-4 xl:grid-cols-2">
            @forelse($products as $product)
                <x-card>
                    <div class="flex items-start justify-between gap-4"><div><p class="font-mono text-xs font-semibold text-brand-dark dark:text-green-400">{{ $product->kode_training }}</p><h2 class="mt-1 text-lg font-semibold">{{ $product->judul }}</h2><p class="mt-1 text-xs text-gray-500">{{ $product->departemen ?? 'Departemen belum diatur' }} · Level {{ $product->level }}</p></div><span class="rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-brand-dark dark:bg-green-950 dark:text-green-300">{{ $product->enrollments_count }} ditugaskan</span></div>
                    <p class="mt-4 text-sm text-gray-600 dark:text-gray-300">Semua manpower yang ditugaskan memulai dari proses training, lalu mengejar data Balok 1 dan Balok 2 di sistem.</p>
                    <x-slot name="footer"><x-button type="button" @click="$dispatch('open-modal', 'assign-{{ $product->id }}')" size="sm">Atur manpower <x-icon name="arrow" class="h-4 w-4" /></x-button></x-slot>
                </x-card>
                <x-modal name="assign-{{ $product->id }}" title="Tugaskan {{ $product->judul }}" maxWidth="xl">
                    @php($productManpowers = $manpowers->where('departemen', $product->departemen))
                    @php($assignedManpowerIds = $product->enrollments->pluck('manpower_id'))
                    <form action="{{ route('admin.product-assignments.store') }}" method="POST" class="space-y-4">@csrf<input type="hidden" name="training_id" value="{{ $product->id }}"><p class="text-sm text-gray-600 dark:text-gray-300">Pilih manpower aktif dari departemen <strong>{{ $product->departemen }}</strong> yang wajib mengikuti training produk ini.</p><div class="max-h-80 space-y-2 overflow-y-auto rounded-lg border border-gray-200 p-3 dark:border-gray-700">@forelse($productManpowers as $manpower)<label class="flex items-center gap-3 rounded-md px-2 py-2 hover:bg-gray-50 dark:hover:bg-gray-800"><input type="checkbox" name="manpower_ids[]" value="{{ $manpower->id }}" @checked($assignedManpowerIds->contains($manpower->id)) class="rounded border-gray-300 text-brand-dark focus:ring-brand-light"><span><span class="block text-sm font-semibold">{{ $manpower->nama }}</span><span class="text-xs text-gray-500">{{ $manpower->mp_id }} · {{ $manpower->bagian }}</span></span></label>@empty<p class="p-3 text-sm text-gray-500">Tidak ada manpower aktif dari departemen ini.</p>@endforelse</div><div class="flex justify-end gap-2"><x-button type="button" variant="outline" @click="$dispatch('close-modal', 'assign-{{ $product->id }}')">Batal</x-button><x-button type="submit">Simpan penugasan</x-button></div></form>
                </x-modal>
            @empty
                <x-card class="xl:col-span-2"><x-empty-state title="Belum ada product" description="Tambahkan product pada menu Produk & training terlebih dahulu." /></x-card>
            @endforelse
        </div>
    </div>
@endsection
