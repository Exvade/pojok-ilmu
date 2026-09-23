@extends('layouts.app')

@section('title', 'Master Skill - Pojok Ilmu MWT')

@section('content')
@include('layouts.navigation')

<div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8 space-y-8" x-data="{ showSkillModal: false, showTargetModal: false }">
    
    <!-- Page Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-extrabold font-heading text-brand-dark dark:text-green-400">Master Skill & Target Level</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">Manajemen daftar keahlian dan standar target level per jabatan/posisi</p>
        </div>
        <div class="flex items-center gap-3">
            <x-button @click="showTargetModal = true" variant="secondary" size="md">
                + Setting Target Level
            </x-button>
            <x-button @click="showSkillModal = true" variant="primary" size="md">
                + Tambah Skill Baru
            </x-button>
        </div>
    </div>

    <!-- Master Skill List -->
    <x-card>
        <x-slot name="header">
            <h3 class="font-bold font-heading text-lg">Daftar Master Skill</h3>
        </x-slot>

        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 text-xs font-bold uppercase">
                    <tr>
                        <th class="py-3 px-4">Kode Skill</th>
                        <th class="py-3 px-4">Nama Skill</th>
                        <th class="py-3 px-4">Kategori</th>
                        <th class="py-3 px-4">Deskripsi</th>
                        <th class="py-3 px-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($skills as $sk)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                            <td class="py-3.5 px-4 font-mono font-bold text-brand-dark dark:text-green-400">{{ $sk->kode_skill }}</td>
                            <td class="py-3.5 px-4 font-semibold text-gray-900 dark:text-gray-100">{{ $sk->nama_skill }}</td>
                            <td class="py-3.5 px-4">
                                <span class="px-2.5 py-1 text-xs font-semibold rounded-lg bg-green-100 dark:bg-green-950/60 text-brand-dark dark:text-green-400">
                                    {{ $sk->kategori }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-gray-500 dark:text-gray-400 text-xs">{{ $sk->deskripsi ?? '-' }}</td>
                            <td class="py-3.5 px-4 text-center">
                                <form action="{{ route('admin.skills.destroy', $sk->id) }}" method="POST" onsubmit="return confirm('Hapus skill ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-2 text-red-600 hover:bg-red-50 dark:hover:bg-red-950/30 rounded-lg transition-all" title="Hapus">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
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
                </tbody>
            </table>
        </div>
    </x-card>

    <!-- Modal Tambah Skill -->
    <x-modal name="add-skill-modal" show="showSkillModal" @close="showSkillModal = false" title="Tambah Master Skill Baru">
        <form action="{{ route('admin.skills.store') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Kode Skill</label>
                <x-input type="text" name="kode_skill" placeholder="MISAL: SKILL-KZN-01" required class="uppercase" />
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nama Skill</label>
                <x-input type="text" name="nama_skill" placeholder="MISAL: Pengenalan Kaizen" required />
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Kategori</label>
                <x-input type="text" name="kategori" placeholder="MISAL: Continues Improvement (CI)" required />
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Deskripsi</label>
                <textarea name="deskripsi" rows="3" class="w-full px-3 py-2 rounded-xl text-sm border border-gray-300 dark:border-gray-700 dark:bg-gray-800" placeholder="Penjelasan singkat keahlian..."></textarea>
            </div>
            <div class="flex justify-end gap-2 pt-4">
                <x-button type="button" @click="showSkillModal = false" variant="outline">Batal</x-button>
                <x-button type="submit" variant="primary">Simpan Skill</x-button>
            </div>
        </form>
    </x-modal>

    <!-- Modal Setting Target Level -->
    <x-modal name="add-target-modal" show="showTargetModal" @close="showTargetModal = false" title="Setting Target Level Skill per Jabatan">
        <form action="{{ route('admin.skills.target-level') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Pilih Skill</label>
                <select name="skill_id" required class="w-full px-3 py-2.5 rounded-xl border border-gray-300 dark:border-gray-700 dark:bg-gray-800 text-sm">
                    <option value="">-- Pilih Skill --</option>
                    @foreach($skills as $sk)
                        <option value="{{ $sk->id }}">{{ $sk->kode_skill }} - {{ $sk->nama_skill }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Departemen</label>
                <x-input type="text" name="departemen" placeholder="MISAL: PRODUKSI PLASTIK" required class="uppercase" />
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Bagian / Stasiun Kerja</label>
                <x-input type="text" name="bagian" placeholder="MISAL: ASSEMBLY" required class="uppercase" />
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Target Level (Level 1 s/d 4)</label>
                <select name="target_level" required class="w-full px-3 py-2.5 rounded-xl border border-gray-300 dark:border-gray-700 dark:bg-gray-800 text-sm font-bold">
                    <option value="1">Level 1 (Pemula / Trainee)</option>
                    <option value="2">Level 2 (Mandiri / Operator)</option>
                    <option value="3">Level 3 (Mahir / Senior Operator)</option>
                    <option value="4">Level 4 (Ahli / Instruktur / Leader)</option>
                </select>
            </div>
            <div class="flex justify-end gap-2 pt-4">
                <x-button type="button" @click="showTargetModal = false" variant="outline">Batal</x-button>
                <x-button type="submit" variant="primary">Simpan & Sync Matrix</x-button>
            </div>
        </form>
    </x-modal>
</div>
@endsection
