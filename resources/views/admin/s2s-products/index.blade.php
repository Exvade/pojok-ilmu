@extends('layouts.app')

@section('title', 'Produk S2S - Pojok Ilmu MWT')

@section('content')
<div class="page-shell space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <p class="text-xs font-semibold uppercase tracking-wider text-brand-dark dark:text-green-400">Integrasi S2S</p>
            <h1 class="page-title mt-1">Produk S2S</h1>
            <p class="page-description">Katalog kode part dari S2S untuk dihubungkan ke training Pojok Ilmu.</p>
        </div>
        @if(auth()->user()->is_superadmin)
            <form action="{{ route('admin.s2s-products.sync') }}" method="POST">@csrf
                <x-button type="submit"><x-icon name="reset" class="h-4 w-4" />Sinkronkan produk</x-button>
            </form>
        @endif
    </div>

    <x-card>
        <div class="flex flex-col gap-1 text-sm text-gray-600 dark:text-gray-300 sm:flex-row sm:items-center sm:justify-between">
            <span>Produk aktif dapat dipilih ketika menambah atau mengubah training.</span>
            <span class="text-xs text-gray-500">{{ $lastSyncedAt ? 'Terakhir diperbarui ' . \Illuminate\Support\Carbon::parse($lastSyncedAt)->translatedFormat('d M Y, H:i') : 'Belum pernah disinkronkan' }}</span>
        </div>
    </x-card>

    <form method="GET" class="grid gap-3 rounded-xl border border-gray-200 bg-white p-4 sm:grid-cols-[1fr_220px_auto] dark:border-gray-700 dark:bg-gray-800">
        <x-input name="search" :value="$search" placeholder="Cari kode atau nama part..." />
        <x-select-menu name="status" :options="['' => 'Semua status', 'aktif' => 'Aktif', 'nonaktif' => 'Nonaktif']" :selected="$status" placeholder="Semua status" />
        <x-button type="submit" variant="secondary">Terapkan</x-button>
    </form>

    <x-card bodyClass="p-0 overflow-hidden">
        <x-table>
            <x-slot name="head"><tr><th>Kode part</th><th>Nama produk</th><th>Status</th><th>Dipakai training</th><th>Data S2S diperbarui</th></tr></x-slot>
            @forelse($products as $product)
                <tr>
                    <td class="font-mono text-xs font-semibold text-brand-dark dark:text-green-300">{{ $product->kode_part }}</td>
                    <td class="font-medium">{{ $product->nama_part ?: '-' }}</td>
                    <td><span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $product->status === 'aktif' ? 'bg-green-100 text-brand-dark dark:bg-green-950 dark:text-green-300' : 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300' }}">{{ ucfirst($product->status) }}</span></td>
                    <td>{{ $product->trainings_count }} training</td>
                    <td class="text-xs text-gray-500">{{ $product->s2s_updated_at?->translatedFormat('d M Y, H:i') ?? '-' }}</td>
                </tr>
            @empty
                <tr><td colspan="5"><x-empty-state title="Belum ada produk S2S" description="Masukkan konfigurasi S2S lalu jalankan sinkronisasi produk." /></td></tr>
            @endforelse
        </x-table>
    </x-card>

    {{ $products->links() }}
</div>
@endsection
