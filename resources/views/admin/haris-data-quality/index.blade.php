@extends('layouts.app')

@section('title', 'Kualitas data HaRis · Pojok Ilmu')

@section('content')
    <div class="page-shell">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-brand-dark dark:text-green-400">Master karyawan</p>
                <h1 class="page-title mt-1">Kualitas data HaRis</h1>
                <p class="page-description">Temukan data karyawan yang perlu dilengkapi HR sebelum memengaruhi sinkronisasi dan penugasan training.</p>
            </div>
            <x-button :href="route('admin.haris-sync.index')" variant="outline"><x-icon name="reset" class="h-4 w-4" />Sinkronisasi HaRis</x-button>
        </div>

        @if($error)
            <x-card><div class="flex items-start gap-3 text-sm text-red-700 dark:text-red-300"><x-icon name="close" class="mt-0.5 h-5 w-5 shrink-0" /><p>{{ $error }}</p></div></x-card>
        @else
            <x-card>
                <x-slot name="header"><div><h2 class="font-heading text-lg font-bold">Ringkasan pemeriksaan</h2><p class="mt-1 text-xs text-gray-500">Berdasarkan {{ $report['total'] }} data karyawan dari respons API HaRis saat halaman ini dimuat. Hasil disimpan sementara hingga 10 menit.</p></div></x-slot>
                <div class="grid gap-4 sm:grid-cols-3">
                    <div><p class="text-xs font-semibold uppercase tracking-wider text-gray-500">Plant belum lengkap</p><p class="mt-2 text-3xl font-semibold text-amber-600 dark:text-amber-400">{{ $summary['plant'] }}</p><p class="mt-1 text-xs text-gray-500">Tidak dapat dikenali pada cakupan plant.</p></div>
                    <div><p class="text-xs font-semibold uppercase tracking-wider text-gray-500">Departemen belum lengkap</p><p class="mt-2 text-3xl font-semibold text-amber-600 dark:text-amber-400">{{ $summary['department'] }}</p><p class="mt-1 text-xs text-gray-500">Menyulitkan penugasan training yang tepat.</p></div>
                    <div><p class="text-xs font-semibold uppercase tracking-wider text-gray-500">Jabatan belum diisi</p><p class="mt-2 text-3xl font-semibold text-amber-600 dark:text-amber-400">{{ $summary['jobTitle'] }}</p><p class="mt-1 text-xs text-gray-500">Perlu dilengkapi untuk konteks kompetensi.</p></div>
                </div>
            </x-card>

            <x-card body-class="p-0">
                <x-slot name="header"><div><h2 class="font-heading text-lg font-bold">Data yang perlu ditindaklanjuti</h2><p class="mt-1 text-xs text-gray-500">Perbaikan dilakukan pada HaRis, lalu jalankan sinkronisasi kembali setelah data diperbarui.</p></div></x-slot>
                @if($report['issues']->isEmpty())
                    <x-empty-state title="Data HaRis sudah lengkap" description="Tidak ditemukan plant, departemen, atau jabatan yang kosong pada respons API saat ini." />
                @else
                    <x-table>
                        <x-slot name="head"><tr><th>Karyawan</th><th>Departemen HaRis</th><th>Plant HaRis</th><th>Jabatan</th><th>Perlu diperbaiki</th></tr></x-slot>
                        @foreach($report['issues'] as $issue)
                            <tr>
                                <td><p class="font-semibold">{{ $issue['nama'] ?? '-' }}</p><p class="mt-1 font-mono text-xs text-gray-500">{{ $issue['nik'] ?? 'NIK kosong' }}</p></td>
                                <td><p>{{ $issue['departemen'] ?? '-' }}</p><p class="mt-1 font-mono text-xs text-gray-500">{{ $issue['kode_departemen'] ?? 'Kode kosong' }}</p></td>
                                <td><p>{{ $issue['plant'] ?? '-' }}</p><p class="mt-1 text-xs text-gray-500">ID: {{ $issue['plant_id'] ?? '-' }}</p></td>
                                <td>{{ $issue['jabatan'] ?? '-' }}</td>
                                <td><div class="flex flex-wrap gap-1.5">@foreach($issue['problems'] as $problem)<span class="rounded-full bg-amber-100 px-2.5 py-1 text-xs font-semibold text-amber-800 dark:bg-amber-950/70 dark:text-amber-200">{{ $problem }}</span>@endforeach</div></td>
                            </tr>
                        @endforeach
                    </x-table>
                @endif
            </x-card>
        @endif
    </div>
@endsection
