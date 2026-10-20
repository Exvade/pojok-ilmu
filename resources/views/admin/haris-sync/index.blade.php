@extends('layouts.app')

@section('title', 'Sinkronisasi HaRis · Pojok Ilmu')

@section('content')
    <div class="page-shell">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-brand-dark dark:text-green-400">Master karyawan</p>
                <h1 class="page-title mt-1">Sinkronisasi HaRis</h1>
                <p class="page-description">Perbarui data master manpower dari HaRis secara manual saat diperlukan.</p>
            </div>
            <form action="{{ route('admin.haris-sync.store') }}" method="POST" data-confirm="Data manpower akan diperbarui dari HaRis. Data yang tidak lagi ada dalam scope sinkronisasi dapat dinonaktifkan." data-confirm-label="Mulai sinkronisasi">
                @csrf
                <x-button type="submit"><x-icon name="reset" class="h-4 w-4" />Sinkronkan sekarang</x-button>
            </form>
        </div>

        <x-card>
            <x-slot name="header"><div><h2 class="font-heading text-lg font-bold">Informasi sinkronisasi</h2><p class="mt-1 text-xs text-gray-500">Sinkronisasi otomatis tetap berjalan sesuai jadwal server. Tombol ini digunakan untuk pembaruan manual.</p></div></x-slot>
            @php($latestLog = $logs->first())
            <div class="grid gap-4 sm:grid-cols-3">
                <div><p class="text-xs font-semibold uppercase tracking-wider text-gray-500">Sinkronisasi terakhir</p><p class="mt-2 font-semibold">{{ $latestLog?->started_at?->translatedFormat('d M Y, H:i') ?? 'Belum pernah' }}</p></div>
                <div><p class="text-xs font-semibold uppercase tracking-wider text-gray-500">Status terakhir</p><p class="mt-2 font-semibold {{ $latestLog?->status === 'success' ? 'text-brand-dark dark:text-green-300' : ($latestLog ? 'text-red-600 dark:text-red-300' : 'text-gray-500') }}">{{ $latestLog ? ($latestLog->status === 'success' ? 'Berhasil' : 'Gagal') : '-' }}</p></div>
                <div><p class="text-xs font-semibold uppercase tracking-wider text-gray-500">Data diproses terakhir</p><p class="mt-2 font-semibold">{{ $latestLog?->employee_count ?? 0 }} manpower</p></div>
            </div>
        </x-card>

        <x-card body-class="p-0">
            <x-slot name="header"><div><h2 class="font-heading text-lg font-bold">Riwayat sinkronisasi</h2><p class="mt-1 text-xs text-gray-500">Sepuluh proses sinkronisasi terakhir, baik manual maupun terjadwal.</p></div></x-slot>
            @if($logs->isEmpty())
                <x-empty-state title="Belum ada riwayat" description="Riwayat akan tercatat setelah sinkronisasi HaRis pertama berhasil atau gagal dijalankan." />
            @else
                <div class="overflow-x-auto">
                    <x-table>
                        <x-slot name="head"><tr><th>Waktu mulai</th><th>Status</th><th>Data diproses</th><th>Dinonaktifkan</th><th>Keterangan</th></tr></x-slot>
                        @foreach($logs as $log)
                            <tr>
                                <td class="whitespace-nowrap text-sm">{{ $log->started_at?->translatedFormat('d M Y, H:i') }}</td>
                                <td><span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $log->status === 'success' ? 'bg-green-100 text-brand-dark dark:bg-green-950 dark:text-green-300' : 'bg-red-100 text-red-700 dark:bg-red-950 dark:text-red-300' }}">{{ $log->status === 'success' ? 'Berhasil' : 'Gagal' }}</span></td>
                                <td>{{ $log->employee_count }}</td>
                                <td>{{ $log->deactivated_count }}</td>
                                <td class="min-w-72 text-sm text-gray-600 dark:text-gray-300">{{ $log->message }}</td>
                            </tr>
                        @endforeach
                    </x-table>
                </div>
            @endif
        </x-card>
    </div>
@endsection
