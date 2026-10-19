@extends('layouts.app')

@section('title', 'Penugasan Training - Pojok Ilmu')

@section('content')
    <div class="page-shell" x-data>
        <div class="flex flex-col gap-4 2xl:flex-row 2xl:items-end 2xl:justify-between">
            <div>
                <h1 class="page-title">Penugasan training</h1>
                <p class="page-description">Tentukan manpower yang wajib mempelajari setiap training. Pengalaman kerja tidak menggantikan proses training pada sistem.</p>
            </div>
            <div class="w-full 2xl:w-auto">
                <x-button :href="route('admin.assignments.import')" variant="outline" class="w-full sm:w-auto">
                    <x-icon name="download" class="h-4 w-4" />Impor penugasan
                </x-button>
                <form method="GET" class="mt-2 grid w-full gap-2 sm:grid-cols-[minmax(0,1fr)_auto] 2xl:w-[32rem]">
                    <x-select-menu name="departemen" :options="['' => 'Semua departemen'] + $departments->mapWithKeys(fn ($item) => [$item => $item])->all()" :selected="$department" aria-label="Filter departemen" class="w-full" />
                    <x-button type="submit" variant="secondary" class="w-full sm:w-auto">
                        <x-icon name="filter" class="h-4 w-4" />Tampilkan
                    </x-button>
                </form>
            </div>
        </div>

        <x-card bodyClass="p-5">
            <div class="grid gap-4 md:grid-cols-3">
                <div><p class="text-sm font-semibold">Cara pakai</p><p class="mt-1 text-xs leading-5 text-gray-500">Pilih training, lalu pilih manpower yang wajib mempelajarinya.</p></div>
                <div><p class="text-sm font-semibold">Balok 1</p><p class="mt-1 text-xs leading-5 text-gray-500">Menandakan manpower lulus post-test.</p></div>
                <div><p class="text-sm font-semibold">Balok 2</p><p class="mt-1 text-xs leading-5 text-gray-500">Menandakan manpower mampu mengerjakan dengan bimbingan; dibuktikan melalui OJT lulus.</p></div>
            </div>
        </x-card>

        <div @class(['grid gap-4', 'md:grid-cols-2' => $products->count() > 1])>
            @forelse ($products as $product)
                <x-card>
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="font-mono text-xs font-semibold text-brand-dark dark:text-green-400">{{ $product->kode_training }}</p>
                            <h2 class="mt-1 text-lg font-semibold">{{ $product->judul }}</h2>
                            <p class="mt-1 text-xs text-gray-500">{{ $product->departmentLabel() }} · Level {{ $product->level }}</p>
                        </div>
                        <span class="rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-brand-dark dark:bg-green-950 dark:text-green-300">{{ $product->enrollments_count }} ditugaskan</span>
                    </div>
                    <p class="mt-4 text-sm text-gray-600 dark:text-gray-300">Semua manpower yang ditugaskan memulai dari proses training, lalu mengejar data Balok 1 dan Balok 2 di sistem.</p>
                    <x-slot name="footer">
                        <x-button type="button" @click="$dispatch('open-modal', 'assign-{{ $product->id }}')" size="sm">
                            Atur manpower <x-icon name="arrow" class="h-4 w-4" />
                        </x-button>
                    </x-slot>
                </x-card>

                <x-modal name="assign-{{ $product->id }}" title="Tugaskan {{ $product->judul }}" maxWidth="xl">
                    @php
                        $productManpowers = $manpowers->filter(fn ($manpower) => $product->appliesToDepartment($manpower->trainingDepartment()));
                        $manpowerSearchValues = $productManpowers
                            ->map(fn ($manpower) => implode(' ', [
                                $manpower->nama,
                                $manpower->mp_id,
                                $manpower->trainingDepartment(),
                                $manpower->bagian,
                            ]))
                            ->values();
                    @endphp

                    <form action="{{ route('admin.product-assignments.store') }}" method="POST" class="space-y-4" x-data="{ query: '', searchValues: @js($manpowerSearchValues), normalize(value) { return String(value).toLocaleLowerCase('id-ID') }, matches(value) { return this.normalize(value).includes(this.normalize(this.query)) }, filteredCount() { return this.searchValues.filter((value) => this.matches(value)).length }, selectAll() { this.$refs.manpowerList.querySelectorAll('input[type=checkbox]:not(:disabled)').forEach((input) => input.checked = true) }, clearAll() { this.$refs.manpowerList.querySelectorAll('input[type=checkbox]:not(:disabled)').forEach((input) => input.checked = false) } }">
                        @csrf
                        <input type="hidden" name="training_id" value="{{ $product->id }}">
                        <p class="text-sm text-gray-600 dark:text-gray-300">Pilih manpower aktif dari <strong>{{ $product->departmentLabel() }}</strong> yang wajib mengikuti training ini.</p>
                        <p class="rounded-md bg-gray-50 px-3 py-2 text-xs leading-5 text-gray-500 dark:bg-gray-800">Hapus centang untuk membatalkan penugasan yang belum dimulai. Training yang sudah dipelajari atau masuk OJT tetap dipertahankan sebagai riwayat kompetensi.</p>
                        <div class="flex flex-wrap items-center justify-between gap-2 rounded-lg border border-gray-200 bg-gray-50 p-2.5 dark:border-gray-700 dark:bg-gray-800/60">
                            <p class="px-1 text-xs text-gray-500">{{ $department !== '' ? 'Manpower departemen ' . $department : 'Seluruh manpower yang sesuai dengan training ini' }}</p>
                            <div class="flex flex-wrap gap-2"><x-button type="button" variant="secondary" size="sm" @click="selectAll()"><x-icon name="check" class="h-4 w-4" />Pilih semua</x-button><x-button type="button" variant="outline" size="sm" @click="clearAll()"><x-icon name="reset" class="h-4 w-4" />Kosongkan pilihan</x-button></div>
                        </div>

                        @if ($productManpowers->isNotEmpty())
                            <div>
                                <label for="search-manpower-{{ $product->id }}" class="mb-1.5 block text-sm font-medium">Cari manpower</label>
                                <x-input id="search-manpower-{{ $product->id }}" type="text" x-model="query" placeholder="Cari nama, NPK, atau bagian..." autocomplete="off" />
                                <p class="mt-1.5 text-xs text-gray-500" x-show="query" x-cloak>
                                    <span x-text="filteredCount()"></span> manpower ditemukan
                                </p>
                            </div>
                        @endif

                        <div x-ref="manpowerList" class="max-h-80 space-y-2 overflow-y-auto rounded-lg border border-gray-200 p-3 dark:border-gray-700">
                            @forelse ($productManpowers as $manpower)
                                @php
                                    $assignment = $product->enrollments->firstWhere('manpower_id', $manpower->id);
                                    $canCancelAssignment = ! $assignment || $assignment->status === 'enrolled';
                                    $searchValue = implode(' ', [$manpower->nama, $manpower->mp_id, $manpower->trainingDepartment(), $manpower->departemen, $manpower->bagian]);
                                @endphp
                                <label x-show="matches(@js($searchValue))" @class([
                                    'flex items-center gap-3 rounded-md px-2 py-2',
                                    'hover:bg-gray-50 dark:hover:bg-gray-800' => $canCancelAssignment,
                                    'bg-gray-50/70 dark:bg-gray-800/50' => ! $canCancelAssignment,
                                ])>
                                    <input type="checkbox" name="manpower_ids[]" value="{{ $manpower->id }}" @checked($assignment !== null) @disabled(! $canCancelAssignment) class="rounded border-gray-300 text-brand-dark focus:ring-brand-light">
                                    <span>
                                        <span class="block text-sm font-semibold">{{ $manpower->nama }}</span>
                                        <span class="text-xs text-gray-500">
                                            {{ $manpower->mp_id }} · {{ $manpower->bagian }}
                                            @unless ($canCancelAssignment)
                                                · Riwayat dipertahankan
                                            @endunless
                                        </span>
                                    </span>
                                </label>
                            @empty
                                <p class="p-3 text-sm text-gray-500">Tidak ada manpower aktif dari departemen yang dipilih.</p>
                            @endforelse
                            @if ($productManpowers->isNotEmpty())
                                <p x-show="query && filteredCount() === 0" x-cloak class="p-3 text-center text-sm text-gray-500">Manpower tidak ditemukan.</p>
                            @endif
                        </div>

                        <div class="flex justify-end gap-2">
                            <x-button type="button" variant="outline" @click="$dispatch('close-modal', 'assign-{{ $product->id }}')"><x-icon name="close" class="h-4 w-4" />Batal</x-button>
                            <x-button type="submit"><x-icon name="save" class="h-4 w-4" />Simpan penugasan</x-button>
                        </div>
                    </form>
                </x-modal>
            @empty
                <x-card class="xl:col-span-2"><x-empty-state title="Belum ada training" description="Tambahkan training pada menu Training terlebih dahulu." /></x-card>
            @endforelse
        </div>
    </div>
@endsection
