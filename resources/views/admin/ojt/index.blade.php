@extends('layouts.app')

@section('title', 'OJT Produksi · Pojok Ilmu MWT')

@section('content')
    <div class="page-shell">
        <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-brand-dark dark:text-green-400">Penilaian praktik</p>
                <h1 class="page-title">Antrean OJT produksi</h1>
                <p class="page-description">Manpower di bawah ini sudah lulus post-test dan menunggu penilaian OJT oleh Leader.</p>
            </div>
            <span class="inline-flex w-fit items-center rounded-full border border-amber-200 bg-amber-50 px-3 py-1.5 text-xs font-semibold text-amber-800 dark:border-amber-900 dark:bg-amber-950/40 dark:text-amber-200">{{ $enrollments->total() }} menunggu OJT</span>
        </div>

        <x-card>
            <form action="{{ route('admin.ojt.index') }}" method="GET" class="grid gap-3 md:grid-cols-[minmax(0,1fr)_minmax(0,1fr)_auto_auto]">
                <x-select name="departemen" aria-label="Filter departemen">
                    <option value="">-- Semua Departemen --</option>
                    @foreach($departemens as $item)
                        <option value="{{ $item }}" @selected($departemen === $item)>{{ $item }}</option>
                    @endforeach
                </x-select>
                <x-input name="search" value="{{ $search }}" placeholder="Cari nama / NPK..." aria-label="Cari manpower" />
                <x-button type="submit" size="sm"><x-icon name="filter" class="h-4 w-4" />Terapkan</x-button>
                <x-button :href="route('admin.ojt.index')" variant="outline" size="sm"><x-icon name="reset" class="h-4 w-4" />Reset</x-button>
            </form>
        </x-card>

        <x-card body-class="p-0">
            <x-table>
                <x-slot name="head"><tr><th>Manpower</th><th>Training / level</th><th>Post-test</th><th>Status OJT</th><th class="text-right">Tindakan</th></tr></x-slot>
                @forelse($enrollments as $enrollment)
                    <tr>
                        <td><p class="font-semibold text-gray-900 dark:text-white">{{ $enrollment->manpower->nama }}</p><p class="mt-1 font-mono text-xs text-gray-500">{{ $enrollment->manpower->mp_id }} · {{ $enrollment->manpower->departemen }}</p></td>
                        <td><p class="font-semibold">{{ $enrollment->training->judul }}</p><p class="mt-1 text-xs text-gray-500">{{ $enrollment->training->kode_training }} · Level {{ $enrollment->training->level }}</p></td>
                        <td class="text-sm text-gray-600 dark:text-gray-300">{{ $enrollment->post_test_completed_at?->translatedFormat('d M Y, H:i') }}</td>
                        <td>@if($enrollment->ojt_status === 'rejected')<span class="inline-flex rounded-full bg-red-100 px-2.5 py-1 text-xs font-semibold text-red-700 dark:bg-red-950 dark:text-red-300">Perlu OJT ulang</span>@else<span class="inline-flex rounded-full bg-amber-100 px-2.5 py-1 text-xs font-semibold text-amber-800 dark:bg-amber-950 dark:text-amber-200">Menunggu OJT</span>@endif</td>
                        <td class="text-right"><x-button :href="route('admin.ojt.show', $enrollment)" size="sm" title="Isi OJT {{ $enrollment->manpower->nama }}"><x-icon name="clipboard" class="h-4 w-4" /><span>Isi OJT</span></x-button></td>
                    </tr>
                @empty
                    <tr><td colspan="5"><x-empty-state title="Tidak ada antrean OJT" description="Belum ada manpower yang lulus post-test dan menunggu penilaian OJT pada filter ini." /></td></tr>
                @endforelse
            </x-table>
            <div class="border-t border-gray-200 px-5 py-4 dark:border-gray-700">{{ $enrollments->links() }}</div>
        </x-card>
    </div>
@endsection
