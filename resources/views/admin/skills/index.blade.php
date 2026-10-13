@extends('layouts.app')

@section('title', 'Master Skill - Pojok Ilmu MWT')

@section('content')


<div class="page-shell" x-data>
    
    <!-- Page Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="page-title">Master kompetensi</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">Daftar kompetensi yang dapat digunakan dalam materi dan struktur leveling.</p>
        </div>
        <div class="flex items-center gap-3 flex-wrap">
            <x-button :href="route('admin.imports.create', 'skills')" variant="secondary" size="md"><x-icon name="download" class="h-4 w-4" />Impor Excel</x-button>
            <x-button @click="$dispatch('open-modal', 'add-skill-modal')" variant="primary" size="md">
                <x-icon name="plus" class="h-4 w-4" />Tambah kompetensi
            </x-button>
        </div>
    </div>

    <!-- Master Skill List -->
    <x-card>
        <x-slot name="header">
            <h3 class="font-bold font-heading text-lg">Daftar kompetensi</h3>
            <p class="mt-1 text-xs text-gray-500">Kompetensi adalah topik atau kemampuan yang menjadi dasar penyusunan training.</p>
        </x-slot>

        <div class="overflow-x-auto">
            <x-table class="min-w-[980px]"><x-slot name="head">
                    <tr>
                        <th class="w-36 py-3 px-4">Kode Skill</th>
                        <th class="py-3 px-4">Nama Skill</th>
                        <th class="w-64 py-3 px-4">Kategori</th>
                        <th class="py-3 px-4">Deskripsi</th>
                        <th class="w-20 py-3 px-4 text-center">Aksi</th>
                    </tr>
                </x-slot>
                    @forelse($skills as $sk)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                            <td class="py-3.5 px-4 font-mono font-bold text-brand-dark dark:text-green-400">{{ $sk->kode_skill }}</td>
                            <td class="py-3.5 px-4 font-semibold text-gray-900 dark:text-gray-100">{{ $sk->nama_skill }}</td>
                            <td class="w-64 py-3.5 px-4">
                                <span class="inline-flex whitespace-nowrap px-2.5 py-1 text-xs font-semibold rounded-md bg-green-100 dark:bg-green-950/60 text-brand-dark dark:text-green-400">
                                    {{ $sk->kategori }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-gray-500 dark:text-gray-400 text-xs">{{ $sk->deskripsi ?? '-' }}</td>
                            <td class="py-3.5 px-4 text-center">
                                <form action="{{ route('admin.skills.destroy', $sk->id) }}" method="POST" data-confirm="Hapus skill ini?">
                                    @csrf
                                    @method('DELETE')
                                    <x-button type="submit" variant="ghost" size="sm" class="text-red-600" aria-label="Hapus skill" title="Hapus skill"><x-icon name="trash" class="h-4 w-4" /></x-button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-8">
                                <x-empty-state title="Belum Ada Skill" description="Silakan tambah master skill baru." />
                            </td>
                        </tr>
                    @endforelse
                </x-table>
        </div>
    </x-card>

    <x-modal name="add-skill-modal" :show="$errors->any() && old('_modal') === 'add-skill-modal'" title="Tambah kompetensi">
        <form action="{{ route('admin.skills.store') }}" method="POST" class="space-y-4">
            @csrf
            <input type="hidden" name="_modal" value="add-skill-modal">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1" for="add-skill-modal-kode_skill">Kode kompetensi</label>
                <x-input type="text" name="kode_skill" placeholder="MISAL: QC-FINAL-SOP" required class="uppercase" id="add-skill-modal-kode_skill" />
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1" for="add-skill-modal-nama_skill">Nama kompetensi</label>
                <x-input type="text" name="nama_skill" placeholder="MISAL: SOP/WI Final Inspection" required id="add-skill-modal-nama_skill" />
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1" for="add-skill-modal-kategori">Kategori</label>
                <x-input type="text" name="kategori" placeholder="MISAL: Continues Improvement (CI)" required id="add-skill-modal-kategori" />
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1" for="add-skill-modal-deskripsi">Deskripsi</label>
                <x-textarea name="deskripsi" rows="3" placeholder="Penjelasan singkat kompetensi..." id="add-skill-modal-deskripsi"></x-textarea>
            </div>
            <div class="flex justify-end gap-2 pt-4">
                <x-button type="button" @click="$dispatch('close-modal', 'add-skill-modal')" variant="outline"><x-icon name="close" class="h-4 w-4" />Batal</x-button>
                <x-button type="submit" variant="primary"><x-icon name="save" class="h-4 w-4" />Simpan kompetensi</x-button>
            </div>
        </form>
    </x-modal>

</div>
@endsection
