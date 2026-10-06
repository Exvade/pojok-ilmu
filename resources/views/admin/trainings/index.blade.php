@extends('layouts.app')

@section('title', 'Program Training - Pojok Ilmu MWT')

@section('content')


<div class="page-shell" x-data>
    
    <!-- Page Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="page-title">Kurikulum & modul</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">Daftar program pelatihan mandiri untuk memenuhi target level skill operator</p>
        </div>
        <x-button @click="$dispatch('open-modal', 'add-training-modal')" variant="primary" size="md">
            Tambah pelatihan
        </x-button>
    </div>

    <div class="grid gap-4 rounded-lg border border-gray-200 bg-white p-5 dark:border-gray-700 dark:bg-gray-800 md:grid-cols-3">
        @foreach(['Tentukan pelatihan' => 'Pilih kompetensi dan level yang dituju.', 'Isi materi pembelajaran' => 'Tambahkan teks, video, gambar, atau PDF.', 'Siapkan evaluasi' => 'Susun soal dan tetapkan nilai kelulusan.'] as $label => $hint)
            <div class="flex gap-3"><span class="flex h-7 w-7 shrink-0 items-center justify-center rounded bg-green-50 text-xs font-semibold text-brand-dark dark:bg-green-950 dark:text-green-300">{{ $loop->iteration }}</span><div><p class="text-sm font-semibold">{{ $label }}</p><p class="mt-1 text-xs leading-5 text-gray-500">{{ $hint }}</p></div></div>
        @endforeach
    </div>
    <!-- Training List Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($trainings as $tr)
            <x-card class="hover:shadow-none transition-all duration-300 flex flex-col justify-between">
                <x-slot name="header">
                    <div class="flex items-center justify-between">
                        <span class="px-2.5 py-1 text-xs font-mono font-bold rounded-lg bg-green-100 dark:bg-green-950/60 text-brand-dark dark:text-green-400">
                            {{ $tr->kode_training }}
                        </span>
                        <span class="px-2.5 py-1 text-xs font-bold rounded-lg bg-blue-100 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300">
                            Level {{ $tr->level }}
                        </span>
                    </div>
                </x-slot>

                <div class="space-y-3">
                    <h3 class="font-bold font-heading text-lg text-gray-900 dark:text-gray-100 leading-snug">{{ $tr->judul }}</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 line-clamp-2">{{ $tr->deskripsi ?? 'Tidak ada deskripsi' }}</p>
                    
                    <div class="flex items-center gap-4 text-xs font-semibold text-gray-600 dark:text-gray-300 pt-2 border-t border-gray-100 dark:border-gray-700">
                        <div>
                            <span class="text-gray-400">Skill:</span> {{ $tr->skill?->nama_skill ?? 'Kompetensi diarsipkan' }}
                        </div>
                    </div>

                    <div class="flex items-center justify-between text-xs text-gray-500 pt-1">
                        <div>Materi: <strong>{{ $tr->materials->count() }} Modul</strong></div>
                        <div>Nilai kelulusan: <strong>{{ $tr->passing_grade }}%</strong></div>
                    </div>
                </div>

                <x-slot name="footer">
                    <div class="flex items-center justify-between pt-2">
                        <a href="{{ route('admin.trainings.show', $tr->id) }}" class="inline-flex items-center text-xs font-bold text-brand-dark dark:text-green-400 hover:underline">
                            Kelola materi <x-icon name="arrow" class="h-4 w-4" />
                        </a>
                        <form action="{{ route('admin.trainings.destroy', $tr->id) }}" method="POST" data-confirm="Hapus program training ini?">
                            @csrf
                            @method('DELETE')
                            <x-button type="submit" variant="ghost" size="sm" class="text-red-600" aria-label="Hapus" title="Hapus"><x-icon name="trash" class="h-4 w-4" /></x-button>
                        </form>
                    </div>
                </x-slot>
            </x-card>
        @empty
            <div class="col-span-full">
                <x-empty-state title="Belum ada pelatihan" description="Klik tombol Tambah pelatihan untuk menambahkan modul." />
            </div>
        @endforelse
    </div>

    <!-- Modal Simpan pelatihan Baru -->
    <x-modal name="add-training-modal" :show="$errors->any() && old('_modal') === 'add-training-modal'" title="Tambah pelatihan">
        <form action="{{ route('admin.trainings.store') }}" method="POST" class="space-y-4">
            @csrf
            <input type="hidden" name="_modal" value="add-training-modal">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1" for="add-training-modal-kode_training">Kode Training</label>
                <x-input type="text" name="kode_training" placeholder="MISAL: TRN-KZN-L1" required class="uppercase" id="add-training-modal-kode_training" />
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1" for="add-training-modal-skill_id">Pilih Relasi Skill</label>
                <x-select name="skill_id" required id="add-training-modal-skill_id">
                    <option value="">-- Pilih Skill --</option>
                    @foreach($skills as $sk)
                        <option value="{{ $sk->id }}">{{ $sk->kode_skill }} - {{ $sk->nama_skill }}</option>
                    @endforeach
                </x-select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1" for="add-training-modal-level">Level Kompetensi</label>
                <x-select name="level" required id="add-training-modal-level">
                    <option value="1">Level 1 (Trainee / Pemula)</option>
                    <option value="2">Level 2 (Operator)</option>
                    <option value="3">Level 3 (Senior Operator)</option>
                    <option value="4">Level 4 (Leader / Instruktur)</option>
                </x-select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1" for="add-training-modal-judul">Judul Training</label>
                <x-input type="text" name="judul" placeholder="MISAL: Modul 1: Konsep Dasar Kaizen & Eliminasi Waste" required id="add-training-modal-judul" />
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1" for="add-training-modal-deskripsi">Deskripsi Ringkas</label>
                <x-textarea name="deskripsi" rows="3" placeholder="Ringkasan isi pelatihan..." id="add-training-modal-deskripsi"></x-textarea>
            </div>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1" for="add-training-modal-passing_grade">Nilai kelulusan (%)</label>
                    <x-input type="number" name="passing_grade" value="80" min="50" max="100" required id="add-training-modal-passing_grade" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1" for="add-training-modal-max_retries">Batas percobaan</label>
                    <x-input type="number" name="max_retries" value="3" min="1" required id="add-training-modal-max_retries" />
                </div>
            </div>
            <div class="flex justify-end gap-2 pt-4">
                <x-button type="button" @click="$dispatch('close-modal', 'add-training-modal')" variant="outline">Batal</x-button>
                <x-button type="submit" variant="primary">Simpan pelatihan</x-button>
            </div>
        </form>
    </x-modal>
</div>
@endsection
