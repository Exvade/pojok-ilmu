@extends('layouts.app')

@section('title', 'Riwayat Aktivitas · Pojok Ilmu')

@section('content')
<div class="page-shell">
    <div>
        <p class="text-xs font-semibold uppercase tracking-[0.16em] text-brand-dark">Superadmin</p>
        <h1 class="page-title mt-1">Riwayat aktivitas</h1>
        <p class="page-description">Jejak perubahan akses, penugasan, dan keputusan OJT.</p>
    </div>

    <x-card>
        <form method="GET" data-auto-search class="grid gap-3 sm:grid-cols-[minmax(0,1fr)_12rem_auto]">
            <x-input name="search" :value="$search" placeholder="Cari nama pelaku..." />
            <x-select name="category"><option value="">Semua aktivitas</option><option value="access" @selected($category === 'access')>Akses pengelola</option><option value="assignment" @selected($category === 'assignment')>Penugasan training</option><option value="ojt" @selected($category === 'ojt')>Penilaian OJT</option></x-select>
            <x-button type="submit"><x-icon name="search" class="h-4 w-4" />Cari</x-button>
        </form>
    </x-card>

    <x-card body-class="p-0">
        <x-table>
            <x-slot name="head"><tr><th>Waktu</th><th>Aktivitas</th><th>Pelaku</th><th>Rincian</th></tr></x-slot>
            @forelse($logs as $log)
                @php
                    $data = $log->data ?? [];
                    $categoryClass = match ($log->category) {
                        'access' => 'bg-sky-100 text-sky-800 dark:bg-sky-950 dark:text-sky-300',
                        'ojt' => 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300',
                        default => 'bg-green-100 text-brand-dark dark:bg-green-950 dark:text-green-300',
                    };
                    $eventLabel = match ($log->event) {
                        'access.updated' => 'Akses diubah',
                        'ojt.approved' => 'OJT disetujui',
                        'ojt.rejected' => 'OJT perlu diulang',
                        'assignment.canceled' => 'Penugasan dibatalkan',
                        default => 'Training ditugaskan',
                    };
                @endphp
                <tr>
                    <td class="whitespace-nowrap text-sm text-gray-600 dark:text-gray-300">{{ $log->created_at?->translatedFormat('d M Y, H:i') }}</td>
                    <td><span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $categoryClass }}">{{ $eventLabel }}</span></td>
                    <td><p class="font-medium">{{ $log->actor?->name ?? 'Sistem' }}</p><p class="mt-1 font-mono text-xs text-gray-500">{{ $log->actor?->user_id ?? '-' }}</p></td>
                    <td class="max-w-md text-sm text-gray-600 dark:text-gray-300">{{ $data['manpower_name'] ?? ($data['training_title'] ?? '-') }}@if(isset($data['manpower_nik']))<span class="block mt-1 font-mono text-xs text-gray-500">{{ $data['manpower_nik'] }}</span>@endif</td>
                </tr>
            @empty
                <tr><td colspan="4"><x-empty-state title="Belum ada aktivitas" description="Perubahan baru akan tercatat di halaman ini." /></td></tr>
            @endforelse
        </x-table>
        @if($logs->hasPages())
            <div class="border-t border-gray-200 px-5 py-4 dark:border-gray-700">{{ $logs->links() }}</div>
        @endif
    </x-card>
</div>
@endsection
