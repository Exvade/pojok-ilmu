@extends('layouts.app')

@section('title', 'Penugasan Training - Pojok Ilmu')

@section('content')
    <div class="page-shell" x-data>
        <div class="flex flex-col gap-4 2xl:flex-row 2xl:items-end 2xl:justify-between">
            <div><h1 class="page-title">Penugasan training</h1><p class="page-description">Tentukan manpower yang wajib mempelajari setiap training. Pengalaman kerja tidak menggantikan proses training pada sistem.</p></div>
            <div class="w-full 2xl:w-auto">
                <x-button :href="route('admin.assignments.import')" variant="outline" class="w-full sm:w-auto"><x-icon name="download" class="h-4 w-4" />Impor penugasan</x-button>
                <form method="GET" class="mt-2 grid w-full gap-2 sm:grid-cols-[minmax(0,1fr)_auto] 2xl:w-[32rem]">
                    <x-select-menu name="departemen" :options="['' => 'Semua departemen'] + $departments->mapWithKeys(fn ($item) => [$item => $item])->all()" :selected="$department" aria-label="Filter departemen" class="w-full" />
                    <x-button type="submit" variant="secondary" class="w-full sm:w-auto"><x-icon name="filter" class="h-4 w-4" />Tampilkan</x-button>
                </form>
            </div>
        </div>

        <x-card bodyClass="p-5">
            <div class="grid gap-4 md:grid-cols-3"><div><p class="text-sm font-semibold">Cara pakai</p><p class="mt-1 text-xs leading-5 text-gray-500">Pilih training, lalu pilih manpower yang wajib mempelajarinya.</p></div><div><p class="text-sm font-semibold">Balok 1</p><p class="mt-1 text-xs leading-5 text-gray-500">Dicapai setelah post-test lulus.</p></div><div><p class="text-sm font-semibold">Balok 2</p><p class="mt-1 text-xs leading-5 text-gray-500">Dicapai setelah OJT disetujui untuk training tersebut.</p></div></div>
        </x-card>

        <div @class(['grid gap-4', 'md:grid-cols-2' => $products->count() > 1])>
            @forelse($products as $product)
                <x-card>
                    <div class="flex items-start justify-between gap-4"><div><p class="font-mono text-xs font-semibold text-brand-dark dark:text-green-400">{{ $product->kode_training }}</p><h2 class="mt-1 text-lg font-semibold">{{ $product->judul }}</h2><p class="mt-1 text-xs text-gray-500">{{ $product->departemen ?? 'Departemen belum diatur' }} · Level {{ $product->level }}</p></div><span class="rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-brand-dark dark:bg-green-950 dark:text-green-300">{{ $product->enrollments_count }} ditugaskan</span></div>
                    <p class="mt-4 text-sm text-gray-600 dark:text-gray-300">Semua manpower yang ditugaskan memulai dari proses training, lalu mengejar data Balok 1 dan Balok 2 di sistem.</p>
                    <x-slot name="footer"><x-button type="button" @click="$dispatch('open-modal', 'assign-{{ $product->id }}')" size="sm">Atur manpower <x-icon name="arrow" class="h-4 w-4" /></x-button></x-slot>
                </x-card>
                <x-modal name="assign-{{ $product->id }}" title="Tugaskan {{ $product->judul }}" maxWidth="xl">
                    @php($productManpowers = $manpowers->where('departemen', $product->departemen))
                    <form action="{{ route('admin.product-assignments.store') }}" method="POST" class="space-y-4">@csrf<input type="hidden" name="training_id" value="{{ $product->id }}"><p class="text-sm text-gray-600 dark:text-gray-300">Pilih manpower aktif dari departemen <strong>{{ $product->departemen }}</strong> yang wajib mengikuti training ini.</p><p class="rounded-md bg-gray-50 px-3 py-2 text-xs leading-5 text-gray-500 dark:bg-gray-800">Hapus centang untuk membatalkan penugasan yang belum dimulai. Training yang sudah dipelajari atau masuk OJT tetap dipertahankan sebagai riwayat kompetensi.</p><div class="max-h-80 space-y-2 overflow-y-auto rounded-lg border border-gray-200 p-3 dark:border-gray-700">@forelse($productManpowers as $manpower)@php($assignment = $product->enrollments->firstWhere('manpower_id', $manpower->id))<label @class(['flex items-center gap-3 rounded-md px-2 py-2', 'hover:bg-gray-50 dark:hover:bg-gray-800' => ! $assignment || $assignment->status === 'enrolled', 'bg-gray-50/70 dark:bg-gray-800/50' => $assignment && $assignment->status !== 'enrolled'])><input type="checkbox" name="manpower_ids[]" value="{{ $manpower->id }}" @checked($assignment) @disabled($assignment && $assignment->status !== 'enrolled') class="rounded border-gray-300 text-brand-dark focus:ring-brand-light"><span><span class="block text-sm font-semibold">{{ $manpower->nama }}</span><span class="text-xs text-gray-500">{{ $manpower->mp_id }} · {{ $manpower->bagian }}@if($assignment && $assignment->status !== 'enrolled') · Riwayat dipertahankan@endif</span></span></label>@empty<p class="p-3 text-sm text-gray-500">Tidak ada manpower aktif dari departemen ini.</p>@endforelse</div><div class="flex justify-end gap-2"><x-button type="button" variant="outline" @click="$dispatch('close-modal', 'assign-{{ $product->id }}')"><x-icon name="close" class="h-4 w-4" />Batal</x-button><x-button type="submit"><x-icon name="save" class="h-4 w-4" />Simpan penugasan</x-button></div></form>
                </x-modal>
            @empty
                <x-card class="xl:col-span-2"><x-empty-state title="Belum ada training" description="Tambahkan training pada menu Training terlebih dahulu." /></x-card>
            @endforelse
        </div>
    </div>
@endsection
