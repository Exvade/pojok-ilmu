@extends('layouts.app')

@section('title', 'Sinkronisasi Kompetensi S2S - Pojok Ilmu MWT')

@section('content')
<div class="page-shell space-y-6">
    <div>
        <p class="text-xs font-semibold uppercase tracking-wider text-brand-dark dark:text-green-400">Integrasi S2S</p>
        <h1 class="page-title mt-1">Sinkronisasi kompetensi</h1>
        <p class="page-description">Riwayat pengiriman capaian kompetensi setelah OJT dinyatakan lulus.</p>
    </div>

    <form method="GET" class="grid gap-3 rounded-xl border border-gray-200 bg-white p-4 sm:grid-cols-[1fr_220px_auto] dark:border-gray-700 dark:bg-gray-800">
        <x-input name="search" :value="$search" placeholder="Cari NIK, produk, atau training..." />
        <x-select-menu name="status" :options="['' => 'Semua status', 'queued' => 'Menunggu', 'sending' => 'Sedang dikirim', 'succeeded' => 'Berhasil', 'failed' => 'Gagal', 'disabled' => 'Dinonaktifkan']" :selected="$status" placeholder="Semua status" />
        <x-button type="submit" variant="secondary">Terapkan</x-button>
    </form>

    <x-card bodyClass="p-0 overflow-hidden">
        <x-table>
            <x-slot name="head"><tr><th>Karyawan</th><th>Training / produk</th><th>Level</th><th>Status</th><th>Terakhir dikirim</th><th class="text-right">Aksi</th></tr></x-slot>
            @forelse($syncs as $sync)
                @php($tone = ['succeeded' => 'bg-green-100 text-brand-dark dark:bg-green-950 dark:text-green-300', 'failed' => 'bg-red-100 text-red-700 dark:bg-red-950 dark:text-red-300', 'disabled' => 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300', 'queued' => 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-200', 'sending' => 'bg-blue-100 text-blue-700 dark:bg-blue-950 dark:text-blue-300'][$sync->status] ?? 'bg-gray-100 text-gray-600')
                <tr>
                    <td><p class="font-semibold">{{ $sync->enrollment?->manpower?->nama ?? '-' }}</p><p class="mt-1 text-xs text-gray-500">{{ $sync->nik }}</p></td>
                    <td><p class="font-semibold">{{ $sync->judul_training }}</p><p class="mt-1 font-mono text-xs text-gray-500">{{ $sync->kode_part }}</p></td>
                    <td>Level {{ $sync->level }}</td>
                    <td><span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $tone }}">{{ ['queued' => 'Menunggu', 'sending' => 'Sedang dikirim', 'succeeded' => 'Berhasil', 'failed' => 'Gagal', 'disabled' => 'Dinonaktifkan'][$sync->status] ?? $sync->status }}</span>@if($sync->last_error)<p class="mt-1 max-w-xs text-xs text-red-600 dark:text-red-300">{{ $sync->last_error }}</p>@endif</td>
                    <td class="text-xs text-gray-500">{{ $sync->sent_at?->translatedFormat('d M Y, H:i') ?? '-' }}</td>
                    <td class="text-right">@if(in_array($sync->status, ['failed', 'disabled'], true))<form action="{{ route('admin.s2s-competency-syncs.retry', $sync) }}" method="POST" class="inline">@csrf<x-button type="submit" variant="outline" size="sm">Kirim ulang</x-button></form>@endif</td>
                </tr>
            @empty
                <tr><td colspan="6"><x-empty-state title="Belum ada riwayat sinkronisasi" description="Data akan muncul setelah OJT training yang terhubung ke produk S2S dinyatakan lulus." /></td></tr>
            @endforelse
        </x-table>
    </x-card>
    {{ $syncs->links() }}
</div>
@endsection
