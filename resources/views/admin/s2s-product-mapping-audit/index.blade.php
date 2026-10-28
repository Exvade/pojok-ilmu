@extends('layouts.app')

@section('title', 'Audit mapping Produk S2S - Pojok Ilmu')

@section('content')
<div class="page-shell space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div><p class="text-xs font-semibold uppercase tracking-wider text-brand-dark dark:text-green-400">Integrasi S2S</p><h1 class="page-title mt-1">Audit mapping produk</h1><p class="page-description">Pastikan training produk sudah dihubungkan ke kode part S2S sebelum capaian OJT dikirim.</p></div>
        <x-button :href="route('admin.s2s-products.index')" variant="outline"><x-icon name="grid" class="h-4 w-4" />Produk S2S</x-button>
    </div>
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach([['Total training', $summary['total'], 'Seluruh training aktif.', null], ['Sudah terhubung', $summary['mapped'], 'Siap mengirim kompetensi ke S2S.', 'green'], ['Belum dihubungkan', $summary['unmapped'], 'Training umum boleh dibiarkan tanpa produk.', 'amber'], ['Produk nonaktif', $summary['inactive'], 'Perlu dipetakan ulang ke produk aktif.', 'red']] as [$label, $value, $description, $tone])
            <x-card bodyClass="p-5"><p class="text-xs font-semibold uppercase tracking-wider text-gray-500">{{ $label }}</p><p @class(['mt-2 text-3xl font-semibold', 'text-brand-dark dark:text-green-400' => $tone === 'green', 'text-amber-600 dark:text-amber-400' => $tone === 'amber', 'text-red-600 dark:text-red-400' => $tone === 'red', 'text-gray-900 dark:text-white' => $tone === null])>{{ $value }}</p><p class="mt-2 text-xs leading-5 text-gray-500">{{ $description }}</p></x-card>
        @endforeach
    </div>
    <form method="GET" class="grid gap-3 rounded-xl border border-gray-200 bg-white p-4 sm:grid-cols-[1fr_220px_auto] dark:border-gray-700 dark:bg-gray-800"><x-input name="search" :value="$search" placeholder="Cari kode atau nama training..." /><x-select-menu name="status" :options="['' => 'Semua status', 'mapped' => 'Sudah terhubung', 'unmapped' => 'Belum dihubungkan', 'inactive' => 'Produk nonaktif']" :selected="$filter" placeholder="Semua status" /><x-button type="submit" variant="secondary">Terapkan</x-button></form>
    <x-card bodyClass="p-0 overflow-hidden">
        <x-table>
            <x-slot name="head"><tr><th>Training</th><th>Level</th><th>Produk S2S</th><th>Status</th><th class="text-right">Aksi</th></tr></x-slot>
            @forelse($trainings as $training)
                <tr>
                    <td><p class="font-semibold">{{ $training->judul }}</p><p class="mt-1 font-mono text-xs text-gray-500">{{ $training->kode_training }}</p></td>
                    <td>Level {{ $training->level }}</td>
                    <td>
                        @forelse($training->s2sProducts as $product)
                            <p class="font-mono text-xs">{{ $product->kode_part }} @if($product->status === 'nonaktif')<span class="text-red-600">(nonaktif)</span>@endif</p>
                        @empty
                            <span class="text-gray-500">-</span>
                        @endforelse
                    </td>
                    <td>
                        @if($training->s2s_mapping_status === 'mapped')
                            <span class="rounded-full bg-green-100 px-2.5 py-1 text-xs font-semibold text-brand-dark dark:bg-green-950 dark:text-green-300">Terhubung</span>
                        @elseif($training->s2s_mapping_status === 'inactive')
                            <span class="rounded-full bg-red-100 px-2.5 py-1 text-xs font-semibold text-red-700 dark:bg-red-950 dark:text-red-300">Produk nonaktif</span>
                        @else
                            <span class="rounded-full bg-amber-100 px-2.5 py-1 text-xs font-semibold text-amber-800 dark:bg-amber-950 dark:text-amber-200">Belum dihubungkan</span>
                        @endif
                    </td>
                    <td class="text-right"><x-button :href="route('admin.trainings.index', ['edit' => $training->id])" variant="ghost" size="sm">Atur produk</x-button></td>
                </tr>
            @empty
                <tr><td colspan="5"><x-empty-state title="Tidak ada training" description="Tidak ada data yang sesuai dengan filter." /></td></tr>
            @endforelse
        </x-table>
    </x-card>
</div>
@endsection
