@extends('layouts.app')

@section('title', 'Ringkasan training manpower · Pojok Ilmu MWT')

@section('content')
    @php
        $totalBalokSatu = $enrollments->where('block', 1)->count();
        $totalBalokDua = $enrollments->where('block', 2)->count();
        $levels = collect(range(1, 3));
    @endphp

    <div class="page-shell">
        <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
            <div><p class="text-xs font-semibold uppercase tracking-wider text-brand-dark dark:text-green-400">Ringkasan training</p><h1 class="page-title">Matriks kompetensi manpower</h1><p class="page-description">Setiap kolom menunjukkan training yang ditugaskan pada level tersebut, bukan satu kolom untuk setiap training.</p></div>
            @if ($departemenFilter !== '')<span class="inline-flex w-fit rounded-full border border-green-200 bg-green-50 px-3 py-1.5 text-xs font-semibold text-brand-dark dark:border-green-900 dark:bg-green-950/40 dark:text-green-300">{{ $departemenFilter }}</span>@endif
        </div>

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <x-card><p class="text-xs font-semibold uppercase tracking-wider text-gray-500">Manpower aktif</p><p class="mt-2 text-3xl font-semibold">{{ $manpowerIds->count() }}</p></x-card>
            <x-card><p class="text-xs font-semibold uppercase tracking-wider text-gray-500">Training ditugaskan</p><p class="mt-2 text-3xl font-semibold">{{ $products->count() }}</p></x-card>
            <x-card><p class="text-xs font-semibold uppercase tracking-wider text-gray-500">Balok 1</p><p class="mt-2 text-3xl font-semibold text-amber-600">{{ $totalBalokSatu }}</p><p class="mt-1 text-xs text-gray-500">Lulus post-test.</p></x-card>
            <x-card><p class="text-xs font-semibold uppercase tracking-wider text-gray-500">Balok 2</p><p class="mt-2 text-3xl font-semibold text-brand-dark dark:text-green-400">{{ $totalBalokDua }}</p><p class="mt-1 text-xs text-gray-500">Mampu mengerjakan dengan bimbingan.</p></x-card>
        </div>

        <x-card>
            <x-slot name="header">
                <div class="flex w-full flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                    <div><h2 class="font-heading text-lg font-bold">Penugasan dan balok kompetensi</h2><p class="mt-1 text-xs text-gray-500">Empat balok menunjukkan tahapan kompetensi. Balok 3 dan 4 ditampilkan sebagai arah pengembangan berikutnya.</p></div>
                    <form action="{{ route('admin.dashboard') }}" method="GET" data-auto-search class="grid w-full gap-3 sm:grid-cols-[minmax(0,1fr)_minmax(0,1fr)_auto_auto] lg:max-w-2xl">
                        <x-select-menu name="departemen" :options="['' => 'Semua departemen'] + $departemens->mapWithKeys(fn ($item) => [$item => $item])->all()" :selected="$departemenFilter" aria-label="Filter departemen" />
                        <x-input type="text" name="search" aria-label="Cari manpower" :value="$search" placeholder="Cari nama / NIK..." />
                        <x-button type="submit" size="sm"><x-icon name="filter" class="h-4 w-4" />Terapkan</x-button>
                        <x-button :href="route('admin.dashboard')" variant="outline" size="sm"><x-icon name="reset" class="h-4 w-4" />Reset</x-button>
                    </form>
                </div>
            </x-slot>

            <x-table>
                <x-slot name="head">
                    <tr><th class="min-w-56">Manpower</th><th class="w-16 text-center">Profil</th>@foreach ($levels as $level)<th class="min-w-64 text-center"><span class="text-[10px] font-semibold uppercase tracking-wider text-brand-dark dark:text-green-400">Level {{ $level }}</span><span class="mt-1 block text-xs font-normal text-gray-500">Training yang ditugaskan</span></th>@endforeach</tr>
                </x-slot>
                @forelse ($manpowers as $manpower)
                    <tr class="align-top transition-colors hover:bg-gray-50 dark:hover:bg-gray-800/50">
                        <td><p class="font-semibold">{{ $manpower->nama }}</p><p class="mt-1 font-mono text-xs text-gray-500">NIK: {{ $manpower->nik }}</p><p class="mt-1 text-xs text-gray-400">{{ $manpower->departemen }} · {{ $manpower->bagian }}</p></td>
                        <td class="text-center"><x-button :href="route('admin.manpower.training-profile', $manpower->id)" variant="ghost" size="sm" aria-label="Lihat profil training {{ $manpower->nama }}" title="Lihat profil training"><x-icon name="eye" class="h-4 w-4" /></x-button></td>
                        @foreach ($levels as $level)
                            @php($levelEnrollments = $manpower->enrollments->filter(fn ($enrollment) => $enrollment->training?->level === $level))
                            <td>
                                @forelse ($levelEnrollments as $enrollment)
                                    <div class="border-b border-gray-100 py-2 last:border-0 dark:border-gray-800">
                                        <p class="text-sm font-semibold leading-5">{{ $enrollment->training->judul }}</p>
                                        <x-competency-blocks :value="$enrollment->block" class="mt-2" />
                                    </div>
                                @empty
                                    <span class="text-xs text-gray-400">Tidak ada training ditugaskan</span>
                                @endforelse
                            </td>
                        @endforeach
                    </tr>
                @empty
                    <tr><td colspan="6"><x-empty-state title="Manpower tidak ditemukan" description="Ubah filter departemen atau pencarian, lalu coba kembali." /></td></tr>
                @endforelse
            </x-table>

            <x-slot name="footer"><div class="mt-4">{{ $manpowers->links() }}</div></x-slot>
        </x-card>
    </div>
@endsection
