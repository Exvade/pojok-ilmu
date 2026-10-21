@extends('layouts.app')

@section('title', 'Tindak lanjut training · Pojok Ilmu')

@section('content')
<div class="page-shell">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <p class="text-xs font-semibold uppercase tracking-wider text-brand-dark dark:text-green-400">Monitoring kompetensi</p>
            <h1 class="page-title mt-1">Tindak lanjut training</h1>
            <p class="page-description">Prioritas untuk penilaian OJT, remedi, dan training yang tidak mengalami perkembangan.</p>
        </div>
        <x-button :href="route('admin.training-reports.index')" variant="outline"><x-icon name="chart" class="h-4 w-4" />Laporan lengkap</x-button>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach($groups as $title => $enrollments)
            <x-card>
                <p class="text-sm font-medium text-gray-500">{{ $title }}</p>
                <p class="mt-2 text-3xl font-semibold">{{ $enrollments->count() }}</p>
            </x-card>
        @endforeach
    </div>

    @foreach($groups as $title => $enrollments)
        <x-card body-class="p-0">
            <x-slot name="header">
                <div>
                    <h2 class="font-heading text-lg font-bold">{{ $title }}</h2>
                    <p class="mt-1 text-xs text-gray-500">Tentukan tindak lanjut berdasarkan status training.</p>
                </div>
            </x-slot>
            @if($enrollments->isEmpty())
                <x-empty-state title="Tidak ada tindak lanjut" description="Belum ada data pada kategori ini." />
            @else
                <x-table>
                    <x-slot name="head">
                        <tr><th>Karyawan</th><th>Training</th><th>Pembaruan</th><th class="text-right">Aksi</th></tr>
                    </x-slot>
                    @foreach($enrollments as $enrollment)
                        <tr>
                            <td><p class="font-semibold">{{ $enrollment->manpower?->nama ?? 'Karyawan diarsipkan' }}</p><p class="mt-1 font-mono text-xs text-gray-500">{{ $enrollment->manpower?->mp_id ?? '-' }}</p></td>
                            <td><p class="font-semibold">{{ $enrollment->training?->judul ?? 'Training diarsipkan' }}</p><p class="mt-1 text-xs text-gray-500">Level {{ $enrollment->training?->level ?? '-' }}</p></td>
                            <td><p class="text-sm font-medium">{{ $enrollment->next_attempt_at?->isFuture() ? 'Menunggu masa remedi' : 'Dapat ditindaklanjuti' }}</p><p class="mt-1 text-xs text-gray-500">{{ $enrollment->next_attempt_at?->translatedFormat('d M Y, H:i') ?? $enrollment->updated_at?->translatedFormat('d M Y, H:i') }}</p></td>
                            <td class="text-right">
                                @if($enrollment->manpower)
                                    <x-button :href="route('admin.manpower.training-profile', $enrollment->manpower->id)" variant="ghost" size="sm" aria-label="Lihat profil {{ $enrollment->manpower->nama }}"><x-icon name="eye" class="h-4 w-4" /></x-button>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </x-table>
            @endif
        </x-card>
    @endforeach
</div>
@endsection