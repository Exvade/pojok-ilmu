@extends('layouts.app')

@section('title', 'Master Skill - Pojok Ilmu MWT')

@section('content')


<div class="page-shell" x-data>
    
    <!-- Page Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="page-title">Kompetensi & target level</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">Manajemen daftar keahlian dan standar target level per jabatan/posisi</p>
        </div>
        <div class="flex items-center gap-3 flex-wrap">
            <x-button @click="$dispatch('open-modal', 'add-target-modal')" variant="secondary" size="md">
                Atur Target Level
            </x-button>
            <x-button @click="$dispatch('open-modal', 'add-skill-modal')" variant="primary" size="md">
                Tambah Skill Baru
            </x-button>
        </div>
    </div>

    <!-- Master Skill List -->
    <x-card>
        <x-slot name="header">
            <h3 class="font-bold font-heading text-lg">Daftar Master Skill</h3>
        </x-slot>

        <div class="overflow-x-auto">
            <x-table><x-slot name="head">
                    <tr>
                        <th class="py-3 px-4">Kode Skill</th>
                        <th class="py-3 px-4">Nama Skill</th>
                        <th class="py-3 px-4">Kategori</th>
                        <th class="py-3 px-4">Deskripsi</th>
                        <th class="py-3 px-4 text-center">Aksi</th>
                    </tr>
                </x-slot>
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

    <x-card>
        <x-slot name="header"><h2 class="text-base font-semibold">Target kompetensi per jabatan</h2><p class="mt-1 text-xs text-gray-500">Daftar aturan penugasan pelatihan yang sudah ditetapkan.</p></x-slot>
        <x-table :headers="['Departemen', 'Jabatan', 'Kompetensi', 'Target']">
            @forelse($targetLevels as $target)
                <tr><td>{{ $target->departemen }}</td><td>{{ $target->bagian }}</td><td>{{ $target->skill?->nama_skill ?? 'Kompetensi tidak tersedia' }}</td><td class="whitespace-nowrap">Level {{ $target->target_level }}</td></tr>
            @empty
                <tr><td colspan="4"><x-empty-state title="Belum ada target level" description="Gunakan Atur Target Level untuk menetapkan kebutuhan kompetensi karyawan." /></td></tr>
            @endforelse
        </x-table>
    </x-card>

    <!-- Modal Tambah Skill -->
    <x-modal name="add-skill-modal" :show="$errors->any() && old('_modal') === 'add-skill-modal'" title="Tambah Master Skill Baru">
        <form action="{{ route('admin.skills.store') }}" method="POST" class="space-y-4">
            @csrf
            <input type="hidden" name="_modal" value="add-skill-modal">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1" for="add-skill-modal-kode_skill">Kode Skill</label>
                <x-input type="text" name="kode_skill" placeholder="MISAL: SKILL-KZN-01" required class="uppercase" id="add-skill-modal-kode_skill" />
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1" for="add-skill-modal-nama_skill">Nama Skill</label>
                <x-input type="text" name="nama_skill" placeholder="MISAL: Pengenalan Kaizen" required id="add-skill-modal-nama_skill" />
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1" for="add-skill-modal-kategori">Kategori</label>
                <x-input type="text" name="kategori" placeholder="MISAL: Continues Improvement (CI)" required id="add-skill-modal-kategori" />
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1" for="add-skill-modal-deskripsi">Deskripsi</label>
                <x-textarea name="deskripsi" rows="3" placeholder="Penjelasan singkat keahlian..." id="add-skill-modal-deskripsi"></x-textarea>
            </div>
            <div class="flex justify-end gap-2 pt-4">
                <x-button type="button" @click="$dispatch('close-modal', 'add-skill-modal')" variant="outline">Batal</x-button>
                <x-button type="submit" variant="primary">Simpan Skill</x-button>
            </div>
        </form>
    </x-modal>

    <!-- Modal Atur Target Level -->
    <x-modal name="add-target-modal" :show="$errors->any() && old('_modal') === 'add-target-modal'" title="Atur Target Level Skill per Jabatan">
        <form action="{{ route('admin.skills.target-level') }}" method="POST" class="space-y-4">
            @csrf
            <input type="hidden" name="_modal" value="add-target-modal">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1" for="add-target-modal-skill_id">Pilih Skill</label>
                <x-select name="skill_id" required id="add-target-modal-skill_id">
                    <option value="">-- Pilih Skill --</option>
                    @foreach($skills as $sk)
                        <option value="{{ $sk->id }}">{{ $sk->kode_skill }} - {{ $sk->nama_skill }}</option>
                    @endforeach
                </x-select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1" for="add-target-modal-departemen">Departemen</label>
                <x-select name="departemen" required id="add-target-modal-departemen">
                    <option value="">-- Pilih departemen dari HaRis --</option>
                    @foreach($departments as $department)<option value="{{ $department }}">{{ $department }}</option>@endforeach
                </x-select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1" for="add-target-modal-bagian">Jabatan</label>
                <x-select name="bagian" required id="add-target-modal-bagian">
                    <option value="">-- Pilih jabatan dari HaRis --</option>
                    @foreach($jobTitles as $jobTitle)<option value="{{ $jobTitle }}">{{ $jobTitle }}</option>@endforeach
                </x-select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1" for="add-target-modal-target_level">Target Level (Level 1 s/d 4)</label>
                <x-select name="target_level" required id="add-target-modal-target_level">
                    <option value="1">Level 1 (Pemula / Trainee)</option>
                    <option value="2">Level 2 (Mandiri / Operator)</option>
                    <option value="3">Level 3 (Mahir / Senior Operator)</option>
                    <option value="4">Level 4 (Ahli / Instruktur / Leader)</option>
                </x-select>
            </div>
            <div class="flex justify-end gap-2 pt-4">
                <x-button type="button" @click="$dispatch('close-modal', 'add-target-modal')" variant="outline">Batal</x-button>
                <x-button type="submit" variant="primary">Simpan Target</x-button>
            </div>
        </form>
    </x-modal>
</div>
@endsection
