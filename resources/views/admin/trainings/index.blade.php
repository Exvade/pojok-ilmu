@extends('layouts.app')

@section('title', 'Program Training - Pojok Ilmu MWT')

@section('content')


<div class="page-shell" x-data>
    {{-- Legacy framework form retained temporarily for historical references.
    
    <!-- Page Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="page-title">Kurikulum & modul</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">Susun pelatihan mandiri sebagai syarat kelulusan pada level framework.</p>
        </div>
        <div class="flex flex-wrap gap-2"><x-button :href="route('admin.imports.create', 'materials')" variant="outline" size="md">Impor materi</x-button><x-button :href="route('admin.imports.create', 'trainings')" variant="secondary" size="md">Impor sub-course</x-button><x-button @click="$dispatch('open-modal', 'add-training-modal')" variant="primary" size="md">Tambah pelatihan</x-button></div>
    </div>

    <div class="grid gap-4 rounded-lg border border-gray-200 bg-white p-5 dark:border-gray-700 dark:bg-gray-800 md:grid-cols-3">
        @foreach(['Masukkan ke level framework' => 'Training menjadi salah satu syarat lulus pada level tersebut.', 'Isi materi pembelajaran' => 'Tambahkan teks, video, gambar, atau PDF.', 'Siapkan evaluasi' => 'Susun pre-test, post-test, assessment, dan OJT bila diperlukan.'] as $label => $hint)
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
                            Syarat Level {{ $tr->level }}
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
                    @if($tr->framework)<p class="text-xs text-gray-500">Masuk struktur: <strong>{{ $tr->framework->nama }}</strong></p>@else<p class="text-xs text-amber-700 dark:text-amber-300">Belum dimasukkan ke struktur leveling.</p>@endif
                    @if($tr->requires_ojt)<span class="inline-flex rounded-md bg-purple-50 px-2 py-1 text-xs font-semibold text-purple-700 dark:bg-purple-950 dark:text-purple-300">Memerlukan OJT</span>@endif

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
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1" for="add-training-modal-framework_id">Masuk ke jalur kompetensi</label>
                <x-select name="framework_id" id="add-training-modal-framework_id">
                    <option value="">-- Pelatihan mandiri, belum masuk struktur --</option>
                    @foreach($frameworks as $framework)<option value="{{ $framework->id }}">{{ $framework->departemen }} — {{ $framework->nama }}</option>@endforeach
                </x-select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1" for="add-training-modal-skill_id">Kompetensi terkait</label>
                <x-select name="skill_id" required id="add-training-modal-skill_id">
                    <option value="">-- Pilih Skill --</option>
                    @foreach($skills as $sk)
                        <option value="{{ $sk->id }}">{{ $sk->kode_skill }} - {{ $sk->nama_skill }}</option>
                    @endforeach
                </x-select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1" for="add-training-modal-level">Syarat kelulusan untuk level</label>
                <x-select name="level" required id="add-training-modal-level">
                    <option value="1">Level 1 (Trainee / Pemula)</option>
                    <option value="2">Level 2 (Operator)</option>
                    <option value="3">Level 3 (Senior Operator)</option>
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
            <label class="flex items-start gap-3 rounded-md border border-purple-200 bg-purple-50 p-3 text-sm text-purple-900 dark:border-purple-900 dark:bg-purple-950/30 dark:text-purple-100">
                <input type="checkbox" name="requires_ojt" value="1" class="mt-0.5">
                <span><strong>Wajib OJT setelah assessment.</strong><br><span class="text-xs">Level baru hanya tercapai setelah assessor menyetujui praktik kerja.</span></span>
            </label>
            <div class="flex justify-end gap-2 pt-4">
                <x-button type="button" @click="$dispatch('close-modal', 'add-training-modal')" variant="outline">Batal</x-button>
                <x-button type="submit" variant="primary">Simpan pelatihan</x-button>
            </div>
        </form>
    </x-modal>
    --}}

    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <p class="text-xs font-semibold uppercase tracking-wider text-brand-dark dark:text-green-400">Pengaturan training</p>
            <h1 class="page-title">Training</h1>
            <p class="page-description">Atur level, isi materi, lalu tugaskan manpower yang perlu mempelajari setiap training.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <x-button :href="route('admin.s2s-product-mapping-audit.index')" variant="outline"><x-icon name="clipboard" class="h-4 w-4" />Audit mapping</x-button>
            <x-button :href="route('admin.s2s-products.index')" variant="outline"><x-icon name="grid" class="h-4 w-4" />Produk S2S</x-button>
            <x-button :href="route('admin.imports.create', 'materials')" variant="outline"><x-icon name="download" class="h-4 w-4" />Impor materi</x-button>
            <x-button :href="route('admin.imports.create', 'trainings')" variant="secondary"><x-icon name="download" class="h-4 w-4" />Impor training</x-button>
            <x-button @click="$dispatch('open-modal', 'add-product-modal')"><x-icon name="plus" class="h-4 w-4" />Tambah training</x-button>
        </div>
    </div>

    <x-card bodyClass="p-5">
        <div class="grid gap-5 md:grid-cols-3">
            <div class="flex gap-3"><span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-md bg-green-100 text-xs font-bold text-brand-dark dark:bg-green-950 dark:text-green-300">1</span><div><p class="text-sm font-semibold">Buat training</p><p class="mt-1 text-xs leading-5 text-gray-500">Contoh: BOX BATTERY berada pada Level 1.</p></div></div>
            <div class="flex gap-3"><span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-md bg-green-100 text-xs font-bold text-brand-dark dark:bg-green-950 dark:text-green-300">2</span><div><p class="text-sm font-semibold">Isi training</p><p class="mt-1 text-xs leading-5 text-gray-500">Tambahkan pre-test, materi, dan post-test.</p></div></div>
            <div class="flex gap-3"><span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-md bg-green-100 text-xs font-bold text-brand-dark dark:bg-green-950 dark:text-green-300">3</span><div><p class="text-sm font-semibold">Tugaskan manpower</p><p class="mt-1 text-xs leading-5 text-gray-500">Tentukan siapa yang perlu belajar pada menu Penugasan training.</p></div></div>
        </div>
    </x-card>

    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        @forelse($trainings as $training)
            @php($trainingDepartments = $training->departmentList())
            <x-card class="flex flex-col">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0"><p class="font-mono text-xs font-semibold text-brand-dark dark:text-green-400">{{ $training->kode_training }}</p><h2 class="mt-2 text-lg font-semibold leading-snug">{{ $training->judul }}</h2></div>
                    <span class="inline-flex shrink-0 items-center whitespace-nowrap rounded-full bg-green-100 px-2.5 py-1 text-xs font-semibold leading-5 text-brand-dark dark:bg-green-950 dark:text-green-300">Level {{ $training->level }}</span>
                </div>
                <p class="mt-3 text-sm text-gray-600 dark:text-gray-300">{{ $training->deskripsi ?: 'Training belum memiliki keterangan.' }}</p>
                <dl class="mt-5 grid gap-3 border-t border-gray-100 pt-4 text-xs dark:border-gray-700">
                    <div class="flex justify-between gap-3"><dt class="text-gray-500">Departemen</dt><dd class="text-right font-semibold" title="{{ $training->departmentLabel() }}">{{ count($trainingDepartments) === 0 ? 'Semua departemen' : (count($trainingDepartments) === 1 ? $trainingDepartments[0] : count($trainingDepartments) . ' departemen') }}</dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-gray-500">Produk S2S</dt><dd class="text-right font-semibold">{{ $training->s2sProducts->isEmpty() ? 'Belum dihubungkan' : $training->s2sProducts->pluck('kode_part')->implode(', ') }}</dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-gray-500">Isi training</dt><dd class="font-semibold">{{ $training->materials->count() }} materi · {{ $training->preTest ? 'Pre-test' : 'Tanpa pre-test' }} · {{ $training->postTest ? 'Post-test' : 'Post-test belum ada' }}</dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-gray-500">Ditugaskan</dt><dd class="font-semibold">{{ $training->enrollments_count }} manpower</dd></div>
                </dl>
                <x-slot name="footer"><div class="flex items-center justify-between gap-2"><x-button :href="route('admin.trainings.show', $training->id)" variant="secondary" size="sm">Atur isi <x-icon name="arrow" class="h-4 w-4" /></x-button><div class="flex items-center"><x-button type="button" variant="ghost" size="sm" class="text-brand-dark" @click="$dispatch('open-modal', 'edit-training-{{ $training->id }}')" aria-label="Ubah training" title="Ubah training"><x-icon name="pencil" class="h-4 w-4" /></x-button><form action="{{ route('admin.trainings.destroy', $training->id) }}" method="POST" data-confirm="Hapus training ini?">@csrf @method('DELETE')<x-button type="submit" variant="ghost" size="sm" class="text-red-600" aria-label="Hapus training" title="Hapus training"><x-icon name="trash" class="h-4 w-4" /></x-button></form></div></div></x-slot>
            </x-card>
            <x-modal name="edit-training-{{ $training->id }}" :show="($errors->any() && old('_modal') === 'edit-training-'.$training->id) || request('edit') === $training->id" title="Ubah training" maxWidth="xl">
                <form action="{{ route('admin.trainings.update', $training->id) }}" method="POST" class="space-y-4">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="_modal" value="edit-training-{{ $training->id }}">
                    <div class="grid gap-4 sm:grid-cols-2"><div><label class="mb-1 block text-sm font-medium" for="edit-{{ $training->id }}-kode">Kode training</label><x-input id="edit-{{ $training->id }}-kode" name="kode_training" :value="$training->kode_training" required class="uppercase" /></div><div><label class="mb-1 block text-sm font-medium" for="edit-{{ $training->id }}-departemen">Departemen</label><x-select-menu id="edit-{{ $training->id }}-departemen" name="departemens" :options="['__all__' => 'Semua departemen (training umum)'] + $departments->mapWithKeys(fn ($department) => [$department => $department])->all()" :selected="$training->departmentList()" placeholder="Pilih departemen" multiple /><p class="mt-1.5 text-xs text-gray-500">Pilih satu atau beberapa departemen. Gunakan opsi semua departemen untuk training umum.</p></div></div>
                    <div class="grid gap-4 sm:grid-cols-2"><div><label class="mb-1 block text-sm font-medium" for="edit-{{ $training->id }}-judul">Nama training</label><x-input id="edit-{{ $training->id }}-judul" name="judul" :value="$training->judul" required /></div><div><label class="mb-1 block text-sm font-medium" for="edit-{{ $training->id }}-level">Level training</label><x-select-menu id="edit-{{ $training->id }}-level" name="level" :options="[1 => 'Level 1', 2 => 'Level 2', 3 => 'Level 3']" :selected="$training->level" placeholder="Pilih level" /></div></div>
                    <div><label class="mb-1 block text-sm font-medium" for="edit-{{ $training->id }}-produk">Produk S2S terkait</label><x-select-menu id="edit-{{ $training->id }}-produk" name="s2s_product_ids" :options="$productOptions" :selected="$training->s2sProducts->pluck('id')->all()" placeholder="Pilih produk yang dikuasai melalui training ini" searchPlaceholder="Cari kode atau nama produk..." emptyMessage="Produk tidak ditemukan." multiple /><p class="mt-1.5 text-xs text-gray-500">Boleh dikosongkan untuk training umum. Kode part akan dipakai saat capaian kompetensi dikirim ke S2S.</p></div>
                    <div><label class="mb-1 block text-sm font-medium" for="edit-{{ $training->id }}-deskripsi">Keterangan training</label><x-textarea id="edit-{{ $training->id }}-deskripsi" name="deskripsi" rows="3">{{ $training->deskripsi }}</x-textarea></div>
                    <div class="grid gap-4 sm:grid-cols-2"><div><label class="mb-1 block text-sm font-medium" for="edit-{{ $training->id }}-nilai">Nilai lulus post-test</label><x-input id="edit-{{ $training->id }}-nilai" type="number" name="passing_grade" :value="$training->passing_grade" min="50" max="100" required /></div><div><label class="mb-1 block text-sm font-medium" for="edit-{{ $training->id }}-percobaan">Batas percobaan</label><x-input id="edit-{{ $training->id }}-percobaan" type="number" name="max_retries" :value="$training->max_retries" min="1" required /></div></div>
                    <div class="flex justify-end gap-2 pt-2"><x-button type="button" variant="outline" @click="$dispatch('close-modal', 'edit-training-{{ $training->id }}')"><x-icon name="close" class="h-4 w-4" />Batal</x-button><x-button type="submit"><x-icon name="save" class="h-4 w-4" />Simpan perubahan</x-button></div>
                </form>
            </x-modal>
        @empty
            <div class="md:col-span-2 xl:col-span-3"><x-empty-state title="Belum ada training" description="Tambahkan training pertama untuk memulai penyusunan materi." /></div>
        @endforelse
    </div>

    <x-modal name="add-product-modal" :show="$errors->any() && old('_modal') === 'add-product-modal'" title="Tambah training" maxWidth="xl">
        <form action="{{ route('admin.trainings.store') }}" method="POST" class="space-y-4">
            @csrf
            <input type="hidden" name="_modal" value="add-product-modal">
            <div class="grid gap-4 sm:grid-cols-2"><div><label class="mb-1 block text-sm font-medium" for="kode_training">Kode training</label><x-input id="kode_training" name="kode_training" placeholder="Contoh: TRN-BOX-01" required class="uppercase" /></div><div><label class="mb-1 block text-sm font-medium" for="departemen">Departemen</label><x-select-menu id="departemen" name="departemens" :options="['__all__' => 'Semua departemen (training umum)'] + $departments->mapWithKeys(fn ($department) => [$department => $department])->all()" placeholder="Pilih departemen" multiple /><p class="mt-1.5 text-xs text-gray-500">Bisa memilih beberapa departemen atau semua departemen untuk training umum.</p></div></div>
            <div class="grid gap-4 sm:grid-cols-2"><div><label class="mb-1 block text-sm font-medium" for="judul">Nama training</label><x-input id="judul" name="judul" placeholder="Contoh: BOX BATTERY" required /></div><div><label class="mb-1 block text-sm font-medium" for="level">Level training</label><x-select-menu id="level" name="level" :options="[1 => 'Level 1', 2 => 'Level 2', 3 => 'Level 3']" selected="1" placeholder="Pilih level" /></div></div>
            <div><label class="mb-1 block text-sm font-medium" for="produk-s2s">Produk S2S terkait</label><x-select-menu id="produk-s2s" name="s2s_product_ids" :options="$productOptions" placeholder="Pilih produk yang dikuasai melalui training ini" searchPlaceholder="Cari kode atau nama produk..." emptyMessage="Produk tidak ditemukan." multiple /><p class="mt-1.5 text-xs text-gray-500">Boleh dikosongkan untuk training umum seperti 5R. Sinkronkan produk S2S lebih dahulu jika pilihan belum tersedia.</p></div>
            <div><label class="mb-1 block text-sm font-medium" for="deskripsi">Keterangan training</label><x-textarea id="deskripsi" name="deskripsi" rows="3" placeholder="Contoh: Training BOX BATTERY untuk lini produksi A."></x-textarea></div>
            <div class="grid gap-4 sm:grid-cols-2"><div><label class="mb-1 block text-sm font-medium" for="passing_grade">Nilai lulus post-test</label><x-input id="passing_grade" type="number" name="passing_grade" value="80" min="50" max="100" required /></div><div><label class="mb-1 block text-sm font-medium" for="max_retries">Batas percobaan</label><x-input id="max_retries" type="number" name="max_retries" value="3" min="1" required /></div></div>
            <div class="rounded-md border border-green-200 bg-green-50 p-3 text-xs leading-5 text-gray-700 dark:border-green-900 dark:bg-green-950/30 dark:text-gray-300">Alur yang dipakai: pre-test, materi, post-test (Balok 1), lalu OJT (Balok 2). Assessment dan Balok 3–4 belum digunakan.</div>
            <div class="flex justify-end gap-2 pt-2"><x-button type="button" variant="outline" @click="$dispatch('close-modal', 'add-product-modal')"><x-icon name="close" class="h-4 w-4" />Batal</x-button><x-button type="submit"><x-icon name="save" class="h-4 w-4" />Simpan training</x-button></div>
        </form>
    </x-modal>
</div>
@endsection
