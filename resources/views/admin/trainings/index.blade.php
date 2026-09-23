@extends('layouts.app')

@section('title', 'Program Training - Pojok Ilmu MWT')

@section('content')
@include('layouts.navigation')

<div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8 space-y-8" x-data="{ showTrainingModal: false }">
    
    <!-- Page Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-extrabold font-heading text-brand-dark dark:text-green-400">Program Pelatihan & Modul</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">Daftar program pelatihan mandiri untuk memenuhi target level skill operator</p>
        </div>
        <x-button @click="showTrainingModal = true" variant="primary" size="md">
            + Buat Program Training Baru
        </x-button>
    </div>

    <!-- Training List Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($trainings as $tr)
            <x-card class="hover:shadow-xl transition-all duration-300 flex flex-col justify-between">
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
                            <span class="text-gray-400">Skill:</span> {{ $tr->skill->nama_skill }}
                        </div>
                    </div>

                    <div class="flex items-center justify-between text-xs text-gray-500 pt-1">
                        <div>Materi: <strong>{{ $tr->materials->count() }} Modul</strong></div>
                        <div>Passing Grade: <strong>{{ $tr->passing_grade }}%</strong></div>
                    </div>
                </div>

                <x-slot name="footer">
                    <div class="flex items-center justify-between pt-2">
                        <a href="{{ route('admin.trainings.show', $tr->id) }}" class="inline-flex items-center text-xs font-bold text-brand-dark dark:text-green-400 hover:underline">
                            Kelola Modul & Kuis &rarr;
                        </a>
                        <form action="{{ route('admin.trainings.destroy', $tr->id) }}" method="POST" onsubmit="return confirm('Hapus program training ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-xs text-red-600 hover:underline">Hapus</button>
                        </form>
                    </div>
                </x-slot>
            </x-card>
        @empty
            <div class="col-span-full">
                <x-empty-state title="Belum Ada Program Training" description="Klik tombol + Buat Program Training Baru untuk menambahkan modul." />
            </div>
        @endforelse
    </div>

    <!-- Modal Buat Training Baru -->
    <x-modal name="add-training-modal" show="showTrainingModal" @close="showTrainingModal = false" title="Buat Program Training Baru">
        <form action="{{ route('admin.trainings.store') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Kode Training</label>
                <x-input type="text" name="kode_training" placeholder="MISAL: TRN-KZN-L1" required class="uppercase" />
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Pilih Relasi Skill</label>
                <select name="skill_id" required class="w-full px-3 py-2.5 rounded-xl border border-gray-300 dark:border-gray-700 dark:bg-gray-800 text-sm">
                    <option value="">-- Pilih Skill --</option>
                    @foreach($skills as $sk)
                        <option value="{{ $sk->id }}">{{ $sk->kode_skill }} - {{ $sk->nama_skill }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Level Kompetensi</label>
                <select name="level" required class="w-full px-3 py-2.5 rounded-xl border border-gray-300 dark:border-gray-700 dark:bg-gray-800 text-sm font-bold">
                    <option value="1">Level 1 (Trainee / Pemula)</option>
                    <option value="2">Level 2 (Operator)</option>
                    <option value="3">Level 3 (Senior Operator)</option>
                    <option value="4">Level 4 (Leader / Instruktur)</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Judul Training</label>
                <x-input type="text" name="judul" placeholder="MISAL: Modul 1: Konsep Dasar Kaizen & Eliminasi Waste" required />
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Deskripsi Ringkas</label>
                <textarea name="deskripsi" rows="3" class="w-full px-3 py-2 rounded-xl text-sm border border-gray-300 dark:border-gray-700 dark:bg-gray-800" placeholder="Ringkasan isi pelatihan..."></textarea>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Passing Grade (%)</label>
                    <x-input type="number" name="passing_grade" value="80" min="50" max="100" required />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Max Retries (Remedi)</label>
                    <x-input type="number" name="max_retries" value="3" min="1" required />
                </div>
            </div>
            <div class="flex justify-end gap-2 pt-4">
                <x-button type="button" @click="showTrainingModal = false" variant="outline">Batal</x-button>
                <x-button type="submit" variant="primary">Buat Training</x-button>
            </div>
        </form>
    </x-modal>
</div>
@endsection
