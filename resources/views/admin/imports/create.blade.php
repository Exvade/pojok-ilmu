@extends('layouts.app')

@section('title', 'Impor ' . $definition['label'] . ' - Pojok Ilmu MWT')

@section('content')
    <div class="page-shell max-w-4xl">
        @php
            $templateRoute = $type === 'assignments'
                ? route('admin.assignments.template')
                : route('admin.imports.template', $type);
            $storeRoute = $type === 'assignments'
                ? route('admin.assignments.import.store')
                : route('admin.imports.store', $type);
        @endphp
        <a href="{{ url()->previous() }}" class="mb-3 inline-flex items-center gap-1 text-xs font-bold text-gray-500 hover:text-brand-dark">&larr; Kembali</a>
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div><h1 class="page-title">Impor {{ $definition['label'] }}</h1><p class="page-description">{{ $definition['description'] }}</p></div>
            <x-button :href="$templateRoute" variant="secondary"><x-icon name="download" class="h-4 w-4" />Unduh template Excel</x-button>
        </div>

        <x-card>
            <x-slot name="header"><div><h2 class="font-heading text-lg font-bold">Cara pengisian</h2><p class="mt-1 text-xs text-gray-500">Gunakan header kolom dari template tanpa mengubah penamaannya.</p></div></x-slot>
            <ol class="space-y-3 text-sm text-gray-700 dark:text-gray-300">
                <li class="flex gap-3"><span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-md bg-green-100 text-xs font-bold text-brand-dark dark:bg-green-950 dark:text-green-300">1</span><span>Unduh template dan isi data pada baris setelah header.</span></li>
                <li class="flex gap-3"><span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-md bg-green-100 text-xs font-bold text-brand-dark dark:bg-green-950 dark:text-green-300">2</span><span>Simpan dalam format <strong>.xlsx</strong>, <strong>.xls</strong>, atau <strong>.csv</strong>.</span></li>
                <li class="flex gap-3"><span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-md bg-green-100 text-xs font-bold text-brand-dark dark:text-green-300">3</span><span>Sistem memvalidasi seluruh baris terlebih dahulu. Jika ada kesalahan, tidak ada data yang disimpan.</span></li>
            </ol>
            <div class="mt-5 rounded-lg border border-gray-200 bg-gray-50 p-4 text-xs dark:border-gray-700 dark:bg-gray-900"><p class="font-semibold text-gray-700 dark:text-gray-200">Kolom template</p><p class="mt-2 break-words font-mono leading-6 text-gray-600 dark:text-gray-300">{{ implode(' · ', $definition['headings']) }}</p></div>
            <ul class="mt-4 space-y-2 text-xs leading-5 text-gray-500 dark:text-gray-400">@foreach($definition['notes'] as $note)<li class="flex gap-2"><x-icon name="check" class="mt-0.5 h-3.5 w-3.5 shrink-0 text-brand-light" /><span>{{ $note }}</span></li>@endforeach</ul>
        </x-card>

        <x-card>
            <x-slot name="header"><div><h2 class="font-heading text-lg font-bold">Unggah file</h2><p class="mt-1 text-xs text-gray-500">Maksimal 5 MB. Impor aman dilakukan berulang; kode yang sama akan diperbarui kecuali bank soal.</p></div></x-slot>
            <form action="{{ $storeRoute }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div><label class="mb-1 block text-sm font-medium" for="import-file">File Excel</label><x-input id="import-file" type="file" name="file" accept=".xlsx,.xls,.csv" required />@error('file')<p class="mt-2 text-xs font-medium text-red-600 dark:text-red-400">{{ $message }}</p>@enderror</div>
                <div class="flex justify-end"><x-button type="submit"><x-icon name="upload" class="h-4 w-4" />Validasi dan impor data</x-button></div>
            </form>
        </x-card>
    </div>
@endsection
