@extends('layouts.app')

@section('title', 'Audit mapping Produk S2S - Pojok Ilmu')

@section('content')
<div class="page-shell space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div><p class="text-xs font-semibold uppercase tracking-wider text-brand-dark dark:text-green-400">Integrasi S2S</p><h1 class="page-title mt-1">Audit mapping produk</h1><p class="page-description">Pastikan training produk sudah dihubungkan ke kode part S2S sebelum capaian OJT dikirim.</p></div>
        <x-button :href="route('admin.s2s-products.index')" variant="outline"><x-icon name="grid" class="h-4 w-4" />Produk S2S</x-button>
    </div>
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
        @foreach([['Total training', $summary['total'], 'Seluruh training aktif.', null], ['Sudah terhubung', $summary['mapped'], 'Siap mengirim kompetensi ke S2S.', 'green'], ['Belum dihubungkan', $summary['unmapped'], 'Pilih produk dari saran, atau tandai sebagai training umum.', 'amber'], ['Training umum', $summary['general'], 'Tidak memerlukan produk S2S, misalnya 5R.', null], ['Produk nonaktif', $summary['inactive'], 'Perlu dipetakan ulang ke produk aktif.', 'red']] as [$label, $value, $description, $tone])
            @php
                $valueClass = match ($tone) {
                    'green' => 'text-brand-dark dark:text-green-400',
                    'amber' => 'text-amber-600 dark:text-amber-400',
                    'red' => 'text-red-600 dark:text-red-400',
                    default => 'text-gray-900 dark:text-white',
                };
            @endphp
            <x-card bodyClass="p-5">
                <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">{{ $label }}</p>
                <p class="mt-2 text-3xl font-semibold {{ $valueClass }}">{{ $value }}</p>
                <p class="mt-2 text-xs leading-5 text-gray-500">{{ $description }}</p>
            </x-card>
        @endforeach
    </div>
    <form method="GET" class="grid gap-3 rounded-xl border border-gray-200 bg-white p-4 sm:grid-cols-[1fr_220px_auto] dark:border-gray-700 dark:bg-gray-800"><x-input name="search" :value="$search" placeholder="Cari kode atau nama training..." /><x-select-menu name="status" :options="['' => 'Semua status', 'mapped' => 'Sudah terhubung', 'unmapped' => 'Belum dihubungkan', 'general' => 'Training umum', 'inactive' => 'Produk nonaktif']" :selected="$filter" placeholder="Semua status" /><x-button type="submit" variant="secondary">Terapkan</x-button></form>
    <x-card bodyClass="p-0 overflow-hidden">
        <x-table>
            <x-slot name="head"><tr><th>Training</th><th>Level</th><th>Produk S2S</th><th>Status</th><th class="text-right">Aksi</th></tr></x-slot>
            @forelse($trainings as $training)
                <tr>
                    <td><p class="font-semibold">{{ $training->judul }}</p><p class="mt-1 font-mono text-xs text-gray-500">{{ $training->kode_training }}</p></td>
                    <td>Level {{ $training->level }}</td>
                    <td class="min-w-[18rem]">
                        @forelse($training->s2sProducts as $product)
                            <p class="font-mono text-xs">{{ $product->kode_part }} <span class="font-sans text-gray-500">{{ $product->nama_part }}</span> @if($product->status === 'nonaktif')<span class="text-red-600">(nonaktif)</span>@endif</p>
                        @empty
                            @if($training->s2s_mapping_status === 'general')
                                <span class="text-gray-500">Tidak memerlukan produk</span>
                            @elseif($training->suggested_products->isNotEmpty())
                                <form method="POST" action="{{ route('admin.s2s-product-mapping-audit.attach', $training->id) }}" class="space-y-2" data-product-suggestions>
                                    @csrf
                                    <p class="text-xs font-semibold text-gray-600 dark:text-gray-300">Saran produk dengan nama mirip:</p>
                                    @foreach($training->suggested_products as $product)
                                        <label class="flex items-start gap-2 text-xs" for="saran-{{ $training->id }}-{{ $product->id }}">
                                            <input type="checkbox" id="saran-{{ $training->id }}-{{ $product->id }}" name="product_ids[]" value="{{ $product->id }}" class="mt-0.5 rounded border-gray-300 text-brand-dark focus:ring-brand-light">
                                            <span><span class="font-mono">{{ $product->kode_part }}</span> <span class="text-gray-500">{{ $product->nama_part }}</span></span>
                                        </label>
                                    @endforeach
                                    <x-button type="submit" size="sm"><x-icon name="save" class="h-4 w-4" />Hubungkan yang dipilih</x-button>
                                </form>
                            @else
                                <span class="text-xs text-gray-500">Tidak ada produk dengan nama mirip. Cari manual lewat tombol Atur produk.</span>
                            @endif
                        @endforelse
                    </td>
                    <td>
                        @if($training->s2s_mapping_status === 'mapped')
                            <span class="whitespace-nowrap rounded-full bg-green-100 px-2.5 py-1 text-xs font-semibold text-brand-dark dark:bg-green-950 dark:text-green-300">Terhubung</span>
                        @elseif($training->s2s_mapping_status === 'inactive')
                            <span class="whitespace-nowrap rounded-full bg-red-100 px-2.5 py-1 text-xs font-semibold text-red-700 dark:bg-red-950 dark:text-red-300">Produk nonaktif</span>
                        @elseif($training->s2s_mapping_status === 'general')
                            <span class="whitespace-nowrap rounded-full bg-gray-100 px-2.5 py-1 text-xs font-semibold text-gray-700 dark:bg-gray-800 dark:text-gray-300">Training umum</span>
                        @else
                            <span class="whitespace-nowrap rounded-full bg-amber-100 px-2.5 py-1 text-xs font-semibold text-amber-800 dark:bg-amber-950 dark:text-amber-200">Belum dihubungkan</span>
                        @endif
                    </td>
                    <td class="text-right">
                        <div class="flex flex-col items-end gap-1.5">
                            <x-button :href="route('admin.trainings.index', ['edit' => $training->id])" variant="ghost" size="sm">Atur produk</x-button>
                            @if(in_array($training->s2s_mapping_status, ['unmapped', 'general'], true))
                                <form method="POST" action="{{ route('admin.s2s-product-mapping-audit.general', $training->id) }}">
                                    @csrf
                                    <input type="hidden" name="is_general" value="{{ $training->s2s_mapping_status === 'unmapped' ? 1 : 0 }}">
                                    <x-button type="submit" variant="outline" size="sm">{{ $training->s2s_mapping_status === 'unmapped' ? 'Jadikan training umum' : 'Batalkan training umum' }}</x-button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5"><x-empty-state title="Tidak ada training" description="Tidak ada data yang sesuai dengan filter." /></td></tr>
            @endforelse
        </x-table>
    </x-card>
</div>
@endsection
