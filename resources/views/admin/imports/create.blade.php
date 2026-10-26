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
            $confirmRoute = $type === 'assignments'
                ? route('admin.assignments.import.confirm')
                : route('admin.imports.confirm', $type);
            $preview = $preview ?? null;
        @endphp
        <a href="{{ url()->previous() }}" class="mb-3 inline-flex items-center gap-1 text-xs font-bold text-gray-500 hover:text-brand-dark">&larr; Kembali</a>
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div><h1 class="page-title">Impor {{ $definition['label'] }}</h1><p class="page-description">{{ $definition['description'] }}</p></div>
            <div class="flex flex-wrap gap-2">
                @if ($type === 'trainings')
                    <x-button :href="route('admin.department-aliases.index')" variant="outline"><x-icon name="pencil" class="h-4 w-4" />Kelola alias departemen</x-button>
                @endif
                <x-button :href="$templateRoute" data-no-loading variant="secondary"><x-icon name="download" class="h-4 w-4" />Unduh template Excel</x-button>
            </div>
        </div>

        <x-card>
            <x-slot name="header"><div><h2 class="font-heading text-lg font-bold">Cara pengisian</h2><p class="mt-1 text-xs text-gray-500">Gunakan header kolom dari template tanpa mengubah penamaannya.</p></div></x-slot>
            <ol class="space-y-3 text-sm text-gray-700 dark:text-gray-300">
                <li class="flex gap-3"><span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-md bg-green-100 text-xs font-bold text-brand-dark dark:bg-green-950 dark:text-green-300">1</span><span>Unduh template dan isi data pada baris setelah header.</span></li>
                <li class="flex gap-3"><span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-md bg-green-100 text-xs font-bold text-brand-dark dark:bg-green-950 dark:text-green-300">2</span><span>Simpan dalam format <strong>.xlsx</strong>, <strong>.xls</strong>, atau <strong>.csv</strong>.</span></li>
                <li class="flex gap-3"><span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-md bg-green-100 text-xs font-bold text-brand-dark dark:text-green-300">3</span><span>Periksa pratinjau sebelum menyimpan. Hanya baris yang siap diimpor akan disimpan; baris yang perlu diperbaiki tidak ikut disimpan.</span></li>
            </ol>
            <div class="mt-5 rounded-lg border border-gray-200 bg-gray-50 p-4 text-xs dark:border-gray-700 dark:bg-gray-900"><p class="font-semibold text-gray-700 dark:text-gray-200">Kolom template</p><p class="mt-2 break-words font-mono leading-6 text-gray-600 dark:text-gray-300">{{ implode(' · ', $definition['headings']) }}</p></div>
            <ul class="mt-4 space-y-2 text-xs leading-5 text-gray-500 dark:text-gray-400">@foreach($definition['notes'] as $note)<li class="flex gap-2"><x-icon name="check" class="mt-0.5 h-3.5 w-3.5 shrink-0 text-brand-light" /><span>{{ $note }}</span></li>@endforeach</ul>
        </x-card>

        <x-card>
            <x-slot name="header"><div><h2 class="font-heading text-lg font-bold">Unggah file</h2><p class="mt-1 text-xs text-gray-500">Maksimal 5 MB. Begitu file dipilih, sistem langsung menampilkan pratinjau. Data belum disimpan pada tahap ini.</p></div></x-slot>
            <form x-data="importUploader"
                x-ref="importForm"
                @submit.prevent="upload()"
                action="{{ $storeRoute }}" method="POST" enctype="multipart/form-data">
                @csrf
                <x-input x-ref="importFile" id="import-file" type="file" name="file" accept=".xlsx,.xls,.csv" class="sr-only" required @change="submitFile($event.target.files)" />
                <div
                    role="button"
                    tabindex="0"
                    @click="$refs.importFile.click()"
                    @keydown.enter.prevent="$refs.importFile.click()"
                    @keydown.space.prevent="$refs.importFile.click()"
                    @dragenter.prevent="dragging = true"
                    @dragover.prevent="dragging = true"
                    @dragleave.prevent="dragging = false"
                    @drop.prevent="dragging = false; submitFile($event.dataTransfer.files)"
                    :class="dragging ? 'border-brand-light bg-green-50 dark:bg-green-950/30' : 'border-dashed border-gray-300 bg-gray-50 hover:border-brand-light hover:bg-green-50/60 dark:border-gray-600 dark:bg-gray-900 dark:hover:bg-green-950/20'"
                    class="relative flex min-h-48 cursor-pointer flex-col items-center justify-center rounded-xl border-2 px-6 py-8 text-center transition focus:outline-none focus:ring-2 focus:ring-brand-light/30"
                    aria-label="Pilih atau seret file Excel untuk pratinjau impor"
                >
                    <span class="flex h-12 w-12 items-center justify-center rounded-xl bg-green-100 text-brand-dark dark:bg-green-950 dark:text-green-300"><x-icon name="upload" class="h-6 w-6" /></span>
                    <p class="mt-4 text-sm font-semibold text-gray-800 dark:text-gray-100">Seret file Excel ke sini</p>
                    <p class="mt-1 text-xs text-gray-500">atau klik untuk memilih file .xlsx, .xls, atau .csv</p>
                    <p x-show="fileName" x-text="fileName" class="mt-4 max-w-full truncate rounded-full bg-white px-3 py-1 text-xs font-semibold text-brand-dark shadow-sm dark:bg-gray-800 dark:text-green-300"></p>
                    <div x-show="loading" x-cloak class="absolute inset-0 flex flex-col items-center justify-center rounded-xl bg-white/90 text-sm font-semibold text-brand-dark dark:bg-gray-900/90 dark:text-green-300"><span class="h-7 w-7 animate-spin rounded-full border-2 border-current border-t-transparent"></span><span class="mt-3">Membaca file dan menyiapkan pratinjau</span></div>
                </div>
                @error('file')<p class="mt-2 text-xs font-medium text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
            </form>
        </x-card>

        @if ($preview)
            @php($invalidCount = count($preview['rows']) - $preview['valid_count'])
            @if($type === 'assignments' && $assignmentTargets->isNotEmpty())
                <x-card body-class="p-0">
                    <x-slot name="header"><div><h2 class="font-heading text-lg font-bold">Target penugasan setelah impor</h2><p class="mt-1 text-xs text-gray-500">Perhitungan menggabungkan penugasan yang sudah ada dengan file ini. Ini peringatan, bukan penghambat impor.</p></div></x-slot>
                    <x-table>
                        <x-slot name="head"><tr><th>Karyawan</th><th>Level 1</th><th>Level 2</th><th>Level 3</th><th>Hasil</th></tr></x-slot>
                        @foreach($assignmentTargets as $target)
                            <tr>
                                <td>
                                    <p class="font-semibold">{{ $target['manpower']->nama }}</p>
                                    <p class="mt-1 font-mono text-xs text-gray-500">{{ $target['manpower']->nik }}</p>
                                </td>
                                @foreach([1 => 6, 2 => 5, 3 => 3] as $level => $minimum)
                                    <td>{{ $target['summary']['levels'][$level] }} dari {{ $minimum }}</td>
                                @endforeach
                                <td>
                                    @if($target['summary']['meets_target'])
                                        <span class="rounded-full bg-green-100 px-2.5 py-1 text-xs font-semibold text-brand-dark dark:bg-green-950/70 dark:text-green-200">Target terpenuhi</span>
                                    @else
                                        <div class="flex flex-wrap gap-1.5">
                                            @foreach($target['summary']['gaps'] as $level => $gap)
                                                @if($gap > 0)
                                                    <span class="rounded-full bg-amber-100 px-2.5 py-1 text-xs font-semibold text-amber-800 dark:bg-amber-950/70 dark:text-amber-200">Level {{ $level }} kurang {{ $gap }}</span>
                                                @endif
                                            @endforeach
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </x-table>
                </x-card>
            @endif
            <x-card data-import-preview>
                <x-slot name="header">
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h2 class="font-heading text-lg font-bold">Pratinjau impor</h2>
                            <p class="mt-1 text-xs text-gray-500">Data belum disimpan. Periksa hasil validasi sebelum melanjutkan.</p>
                        </div>
                        <div class="flex flex-wrap gap-2 text-xs font-semibold">
                            <span class="rounded-full bg-green-100 px-3 py-1 text-brand-dark dark:bg-green-950 dark:text-green-200">{{ $preview['valid_count'] }} siap diimpor</span>
                            @if ($invalidCount > 0)
                                <span class="rounded-full bg-red-100 px-3 py-1 text-red-700 dark:bg-red-950 dark:text-red-200">{{ $invalidCount }} perlu diperbaiki</span>
                            @endif
                        </div>
                    </div>
                </x-slot>

                <x-table class="max-h-[32rem]" :headers="array_merge(['Baris', 'Status'], $definition['headings'], ['Keterangan'])">
                    @foreach ($preview['rows'] as $row)
                        <tr class="{{ $row['is_valid'] ? '' : 'bg-red-50/70 dark:bg-red-950/20' }}">
                            <td class="whitespace-nowrap font-medium">{{ $row['row'] ?: '-' }}</td>
                            <td class="whitespace-nowrap">
                                @if ($row['is_valid'])
                                    <span class="rounded-full bg-green-100 px-2 py-1 text-xs font-semibold text-brand-dark dark:bg-green-950 dark:text-green-200">Siap diimpor</span>
                                @else
                                    <span class="rounded-full bg-red-100 px-2 py-1 text-xs font-semibold text-red-700 dark:bg-red-950 dark:text-red-200">Perlu diperbaiki</span>
                                @endif
                            </td>
                            @foreach ($definition['headings'] as $heading)
                                <td class="min-w-36 align-top">{{ $row['data'][$heading] ?? '-' }}</td>
                            @endforeach
                            <td class="min-w-64 align-top text-xs leading-5 {{ $row['is_valid'] ? 'text-gray-500' : 'font-medium text-red-700 dark:text-red-300' }}">
                                @if ($row['is_valid'])
                                    @if ($row['inherited_training'] ?? false)
                                        Mengikuti kode training: {{ $row['resolved_training_code'] }}.
                                    @elseif (! empty($row['department_resolution']))
                                        Akan disimpan dengan departemen: {{ $row['department_resolution'] }}.
                                    @else
                                        Data sesuai dan siap disimpan.
                                    @endif
                                @else
                                    {{ implode(' ', $row['errors']) }}
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </x-table>

                <div class="mt-5 flex flex-col-reverse gap-3 border-t border-gray-200 pt-4 sm:flex-row sm:items-center sm:justify-between dark:border-gray-700">
                    <p class="text-xs leading-5 text-gray-500">Baris yang perlu diperbaiki tidak akan disimpan. Unggah file baru bila ingin memperbaiki data tersebut.</p>
                    @if ($preview['valid_count'] > 0)
                        <form action="{{ $confirmRoute }}" method="POST">
                            @csrf
                            <input type="hidden" name="preview_token" value="{{ $preview['token'] }}">
                            <x-button type="submit"><x-icon name="check" class="h-4 w-4" />Simpan {{ $preview['valid_count'] }} baris</x-button>
                        </form>
                    @endif
                </div>
            </x-card>
        @endif
    </div>
@endsection
