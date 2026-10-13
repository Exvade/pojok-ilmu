@extends('layouts.app')

@section('title', 'Ringkasan training manpower · Pojok Ilmu MWT')

@section('content')
    @php
        $totalBalokSatu = $enrollments->filter(fn ($enrollment) => $enrollment->post_test_completed_at && ! ($enrollment->status === 'passed' && $enrollment->ojt_status === 'approved'))->count();
        $totalBalokDua = $enrollments->filter(fn ($enrollment) => $enrollment->status === 'passed' && $enrollment->ojt_status === 'approved')->count();
    @endphp

    <div class="page-shell">
        <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-brand-dark dark:text-green-400">Ringkasan training</p>
                <h1 class="page-title">Matriks training manpower</h1>
                <p class="page-description">Tampilkan training yang benar-benar ditugaskan pada manpower di departemen terpilih.</p>
            </div>
            @if($departemenFilter !== '')
                <span class="inline-flex w-fit items-center rounded-full border border-green-200 bg-green-50 px-3 py-1.5 text-xs font-semibold text-brand-dark dark:border-green-900 dark:bg-green-950/40 dark:text-green-300">{{ $departemenFilter }}</span>
            @endif
        </div>

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <x-card><p class="text-xs font-semibold uppercase tracking-wider text-gray-500">Manpower aktif</p><p class="mt-2 text-3xl font-semibold">{{ $manpowerIds->count() }}</p><p class="mt-1 text-xs text-gray-500">Sesuai filter yang dipilih.</p></x-card>
            <x-card><p class="text-xs font-semibold uppercase tracking-wider text-gray-500">Training ditugaskan</p><p class="mt-2 text-3xl font-semibold">{{ $products->count() }}</p><p class="mt-1 text-xs text-gray-500">Training yang memiliki penugasan.</p></x-card>
            <x-card><p class="text-xs font-semibold uppercase tracking-wider text-gray-500">Balok 1</p><p class="mt-2 text-3xl font-semibold text-amber-600">{{ $totalBalokSatu }}</p><p class="mt-1 text-xs text-gray-500">Lulus post-test, menunggu OJT.</p></x-card>
            <x-card><p class="text-xs font-semibold uppercase tracking-wider text-gray-500">Balok 2</p><p class="mt-2 text-3xl font-semibold text-brand-dark">{{ $totalBalokDua }}</p><p class="mt-1 text-xs text-gray-500">Training yang sudah disetujui OJT.</p></x-card>
        </div>

        <x-card>
            <x-slot name="header">
                <div class="flex w-full flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                    <div><h2 class="font-heading text-lg font-bold">Penugasan dan capaian training</h2><p class="mt-1 text-xs text-gray-500">Kolom hanya berisi training yang sudah ditugaskan pada manpower hasil filter.</p></div>
                    <form action="{{ route('admin.dashboard') }}" method="GET" class="grid w-full gap-3 sm:grid-cols-[minmax(0,1fr)_minmax(0,1fr)_auto_auto] lg:max-w-2xl">
                        <x-select name="departemen" aria-label="Filter departemen">
                            <option value="">-- Semua Departemen --</option>
                            @foreach($departemens as $departemen)
                                <option value="{{ $departemen }}" @selected($departemenFilter === $departemen)>{{ $departemen }}</option>
                            @endforeach
                        </x-select>
                        <x-input type="text" name="search" aria-label="Cari manpower" value="{{ $search }}" placeholder="Cari nama / NPK..." />
                        <x-button type="submit" variant="primary" size="sm"><x-icon name="filter" class="h-4 w-4" />Terapkan</x-button>
                        <x-button :href="route('admin.dashboard', ['departemen' => ''])" variant="outline" size="sm"><x-icon name="reset" class="h-4 w-4" />Reset</x-button>
                    </form>
                </div>
            </x-slot>

            @if($products->isEmpty())
                <x-empty-state title="Belum ada training yang ditugaskan" description="Pilih departemen atau tugaskan training kepada manpower terlebih dahulu." />
            @else
                <div class="overflow-x-auto">
                    <x-table>
                        <x-slot name="head">
                            <tr class="bg-gray-100 text-xs font-bold uppercase tracking-wider text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                                <th class="rounded-tl-xl px-4 py-3.5">Manpower / NPK</th>
                                <th class="px-4 py-3.5">Departemen / Jabatan</th>
                                @foreach($products as $product)
                                    <th class="min-w-48 border-l border-gray-200 px-4 py-3.5 text-center dark:border-gray-700"><span class="block text-[10px] font-semibold text-brand-dark dark:text-green-400">Level {{ $product->level }}</span><span class="mt-1 block">{{ $product->judul }}</span></th>
                                @endforeach
                            </tr>
                        </x-slot>
                        @forelse($manpowers as $manpower)
                            <tr class="transition-colors hover:bg-gray-50 dark:hover:bg-gray-800/50">
                                <td class="whitespace-nowrap px-4 py-4 font-semibold text-gray-900 dark:text-gray-100"><div>{{ $manpower->nama }}</div><span class="font-mono text-xs text-gray-400">NPK: {{ $manpower->mp_id }}</span></td>
                                <td class="whitespace-nowrap px-4 py-4 text-xs text-gray-600 dark:text-gray-400"><div>{{ $manpower->departemen }}</div><span class="text-gray-400">{{ $manpower->bagian }}</span></td>
                                @foreach($products as $product)
                                    @php($enrollment = $manpower->enrollments->firstWhere('training_id', $product->id))
                                    <td class="border-l border-gray-200 px-4 py-4 text-center dark:border-gray-700">
                                        @if(! $enrollment)
                                            <span class="text-xs text-gray-400">Tidak ditugaskan</span>
                                        @elseif($enrollment->block === 2)
                                            <span class="inline-flex rounded-full bg-green-100 px-2.5 py-1 text-xs font-semibold text-brand-dark dark:bg-green-950 dark:text-green-300">Balok 2</span><p class="mt-1 text-[11px] text-gray-500">Mampu menjalankan</p>
                                        @elseif($enrollment->block === 1)
                                            <span class="inline-flex rounded-full bg-amber-100 px-2.5 py-1 text-xs font-semibold text-amber-800 dark:bg-amber-950 dark:text-amber-200">Balok 1</span><p class="mt-1 text-[11px] text-gray-500">Menunggu OJT</p>
                                        @else
                                            <span class="inline-flex rounded-full bg-gray-100 px-2.5 py-1 text-xs font-semibold text-gray-600 dark:bg-gray-700 dark:text-gray-300">Belum mulai</span>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @empty
                            <tr><td colspan="{{ 2 + $products->count() }}" class="px-4 py-8 text-center"><x-empty-state title="Manpower tidak ditemukan" description="Ubah filter departemen atau pencarian, lalu coba kembali." /></td></tr>
                        @endforelse
                    </x-table>
                </div>
            @endif

            <x-slot name="footer"><div class="mt-4">{{ $manpowers->links() }}</div></x-slot>
        </x-card>
    </div>
@endsection
