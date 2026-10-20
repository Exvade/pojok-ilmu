@extends('layouts.app')

@section('title', 'Riwayat Penugasan Training - Pojok Ilmu')

@section('content')
    <div class="page-shell space-y-5">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-brand-dark dark:text-green-400">Audit penugasan</p>
                <h1 class="page-title mt-1">{{ $training->judul }}</h1>
                <p class="page-description">{{ $training->kode_training }} · {{ $training->departmentLabel() }} · Level {{ $training->level }}</p>
            </div>
            <x-button :href="route('admin.product-assignments.index')" variant="outline">
                <x-icon name="back" class="h-4 w-4" />Kembali ke penugasan
            </x-button>
        </div>

        <x-card bodyClass="p-0">
            <x-slot name="header">
                <div>
                    <h2 class="font-heading text-lg font-bold">Riwayat perubahan</h2>
                    <p class="mt-1 text-xs text-gray-500">Mencatat penugasan dan pembatalan yang dilakukan pengelola.</p>
                </div>
            </x-slot>
            <x-table>
                <x-slot name="head"><tr><th>Waktu</th><th>Manpower</th><th>Tindakan</th><th>Status</th><th>Pengelola</th><th>Catatan</th></tr></x-slot>
                @forelse($logs as $log)
                    <tr>
                        <td class="whitespace-nowrap text-sm text-gray-600 dark:text-gray-300">{{ $log->created_at?->translatedFormat('d M Y, H:i') }}</td>
                        <td><p class="font-semibold">{{ $log->manpower?->nama ?? 'Karyawan diarsipkan' }}</p><p class="mt-1 font-mono text-xs text-gray-500">{{ $log->manpower?->mp_id ?? '-' }}</p></td>
                        <td><span @class(['inline-flex rounded-full px-2.5 py-1 text-xs font-semibold', 'bg-green-100 text-brand-dark dark:bg-green-950 dark:text-green-300' => $log->action === 'assigned', 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-200' => $log->action === 'canceled'])>{{ $log->action === 'assigned' ? 'Ditugaskan' : 'Dibatalkan' }}</span></td>
                        <td class="text-sm text-gray-600 dark:text-gray-300">{{ $log->status_before ? ucfirst($log->status_before) . ' → ' : '' }}{{ ucfirst($log->status_after) }}</td>
                        <td class="text-sm">{{ $log->actor?->name ?? 'Sistem' }}</td>
                        <td class="max-w-xs text-sm text-gray-600 dark:text-gray-300">{{ $log->reason ?? '-' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6"><x-empty-state title="Belum ada riwayat perubahan" description="Riwayat akan tercatat saat penugasan disimpan atau dibatalkan." /></td></tr>
                @endforelse
            </x-table>
            @if($logs->hasPages())
                <x-slot name="footer">{{ $logs->links() }}</x-slot>
            @endif
        </x-card>
    </div>
@endsection
