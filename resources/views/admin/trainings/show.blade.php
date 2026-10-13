@extends('layouts.app')

@section('title', $training->judul . ' - Pojok Ilmu MWT')

@section('content')


<div class="page-shell" x-data="{ activeSection: 'materials', selectedQuizId: @js(old('quiz_id', '')), selectedQuizLabel: '', openQuestion(quizId, label) { this.selectedQuizId = quizId; this.selectedQuizLabel = label; this.$dispatch('open-modal', 'add-question-modal'); } }">
    
    <!-- Back & Header -->
    <div>
        <a href="{{ route('admin.trainings.index') }}" class="text-xs font-bold text-gray-500 hover:text-brand-dark flex items-center gap-1 mb-2">
            &larr; Kembali ke Daftar Training
        </a>
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 flex-wrap">
                    <span class="px-2.5 py-1 text-xs font-mono font-bold rounded-lg bg-green-100 dark:bg-green-950/60 text-brand-dark dark:text-green-400">
                        {{ $training->kode_training }}
                    </span>
                    <span class="px-2.5 py-1 text-xs font-bold rounded-lg bg-blue-100 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300">
                        Level {{ $training->level }}
                    </span>
                </div>
                <h1 class="page-title mt-2">{{ $training->judul }}</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ $training->departemen ?: 'Departemen belum diatur' }} &bull; Produk Level {{ $training->level }} &bull; Nilai lulus post-test {{ $training->passing_grade }}%</p>
            </div>
            <div class="flex items-center gap-2 flex-wrap">
                <x-button @click="$dispatch('open-modal', 'add-material-modal')" variant="primary" size="md">
                    Tambah materi
                </x-button>
                <x-button :href="route('admin.imports.create', 'questions')" variant="outline" size="md">Impor soal</x-button>
                @if(!$training->preTest || !$training->postTest)
                    <form action="{{ route('admin.trainings.evaluation-set.prepare', $training->id) }}" method="POST">
                        @csrf
                        <x-button type="submit" variant="secondary" size="md">Siapkan pre-test & post-test</x-button>
                    </form>
                @endif
            </div>
        </div>
    </div>

    <div class="border-b border-gray-200 dark:border-gray-700">
        <nav class="flex gap-1 overflow-x-auto" aria-label="Bagian pengelolaan sub-course">
            <x-button type="button" variant="ghost" @click="activeSection = 'materials'" class="rounded-b-none border-b-2 px-4" x-bind:class="activeSection === 'materials' ? 'border-brand-dark text-brand-dark dark:border-green-400 dark:text-green-300' : 'border-transparent'">Materi ({{ $training->materials->count() }})</x-button>
            <x-button type="button" variant="ghost" @click="activeSection = 'pre-test'" class="rounded-b-none border-b-2 px-4" x-bind:class="activeSection === 'pre-test' ? 'border-brand-dark text-brand-dark dark:border-green-400 dark:text-green-300' : 'border-transparent'">Pre-test ({{ $training->preTest?->questions->count() ?? 0 }})</x-button>
            <x-button type="button" variant="ghost" @click="activeSection = 'post-test'" class="rounded-b-none border-b-2 px-4" x-bind:class="activeSection === 'post-test' ? 'border-brand-dark text-brand-dark dark:border-green-400 dark:text-green-300' : 'border-transparent'">Post-test ({{ $training->postTest?->questions->count() ?? 0 }})</x-button>
        </nav>
    </div>

    <section x-show="activeSection === 'materials'" x-cloak>
    <x-card>
        <x-slot name="header">
            <h3 class="font-bold font-heading text-lg">Modul Materi Pembelajaran ({{ $training->materials->count() }})</h3>
        </x-slot>

        <div class="space-y-4">
            @forelse($training->materials as $mat)
                <div class="p-4 rounded-lg bg-gray-50 dark:bg-gray-800/50 border border-gray-200 dark:border-gray-700 space-y-3">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="w-7 h-7 rounded-full bg-brand-dark text-white font-bold text-xs flex items-center justify-center">
                                {{ $mat->urutan }}
                            </span>
                            <h4 class="font-bold text-base text-gray-900 dark:text-gray-100">{{ $mat->judul }}</h4>
                            
                            <span class="px-2 py-0.5 text-[11px] font-semibold rounded-md 
                                {{ $mat->tipe_media === 'video_gdrive' ? 'bg-purple-100 text-purple-700' : '' }}
                                {{ $mat->tipe_media === 'text' ? 'bg-blue-100 text-blue-700' : '' }}
                                {{ $mat->tipe_media === 'image' ? 'bg-green-100 text-green-700' : '' }}
                            ">
                                {{ strtoupper(str_replace('_', ' ', $mat->tipe_media)) }}
                            </span>
                        </div>

                        <form action="{{ route('admin.materials.destroy', $mat->id) }}" method="POST" data-confirm="Hapus materi ini?">
                            @csrf
                            @method('DELETE')
                            <x-button type="submit" variant="ghost" size="sm" class="text-red-600" aria-label="Hapus" title="Hapus"><x-icon name="trash" class="h-4 w-4" /></x-button>
                        </form>
                    </div>

                    @if($mat->konten_teks)
                        <p class="text-xs text-gray-600 dark:text-gray-300 leading-relaxed pl-9">{{ $mat->konten_teks }}</p>
                    @endif

                    <!-- Video Preview (Google Drive / YouTube) -->
                    @if($mat->tipe_media === 'video_gdrive' || $mat->tipe_media === 'video_youtube')
                        <div class="pl-9 pt-2">
                            <div class="relative w-full max-w-xl aspect-video rounded-lg bg-black overflow-hidden shadow-none">
                                <iframe title="Video pembelajaran" loading="lazy" src="{{ $mat->embed_url }}" class="w-full h-full border-0" allow="autoplay" allowfullscreen></iframe>
                            </div>
                        </div>
                    @elseif($mat->tipe_media === 'image' && $mat->media_url)
                        <div class="pl-9 pt-2">
                            <img src="{{ $mat->media_url }}" alt="Media Gambar" class="max-h-48 rounded-lg object-cover shadow-sm border border-gray-200">
                        </div>
                    @elseif($mat->tipe_media === 'pdf' && $mat->media_url)
                        <x-button :href="$mat->media_url" target="_blank" rel="noopener" variant="outline" size="sm">Buka dokumen PDF <x-icon name="arrow" class="h-4 w-4" /></x-button>
                    @endif
                </div>
            @empty
            <x-empty-state title="Belum Ada Materi" description="Tambahkan materi teks, gambar, dokumen, atau video untuk produk ini." />
            @endforelse
        </div>
    </x-card>
    </section>

    <!-- Assessment setup: pre-test is diagnostic; post-test determines the level. -->
    <section x-show="activeSection === 'pre-test'" x-cloak>
    <x-card>
        <x-slot name="header">
            <div class="flex items-center justify-between w-full">
                <h3 class="font-bold font-heading text-lg">Pre-test diagnostik</h3>
                @if($training->preTest)
                    <x-button @click="openQuestion('{{ $training->preTest->id }}', 'Pre-test')" variant="primary" size="sm">Tambah soal</x-button>
                @endif
            </div>
        </x-slot>
        @if($training->preTest)
            <div class="rounded-lg border border-blue-200 bg-blue-50 p-4 dark:border-blue-900 dark:bg-blue-950/30">
                <p class="font-semibold text-blue-950 dark:text-blue-100">{{ $training->preTest->judul }}</p>
                <p class="mt-1 text-xs text-blue-800 dark:text-blue-200">Durasi {{ $training->preTest->durasi_menit }} menit &bull; {{ $training->preTest->questions->count() }} soal. Nilainya hanya untuk pemetaan awal dan tidak mengubah level.</p>
            </div>
            <div class="mt-4 space-y-2">
                @forelse($training->preTest->questions as $index => $question)
                    <div class="flex items-start justify-between gap-3 rounded-md border border-gray-200 p-3 text-sm dark:border-gray-700">
                        <span><strong>{{ $index + 1 }}.</strong> {{ $question->pertanyaan }}</span>
                        <form action="{{ route('admin.questions.destroy', $question->id) }}" method="POST" data-confirm="Hapus soal ini?">@csrf @method('DELETE')<x-button type="submit" variant="ghost" size="sm" class="text-red-600" aria-label="Hapus"><x-icon name="trash" class="h-4 w-4" /></x-button></form>
                    </div>
                @empty
                    <x-empty-state title="Belum ada soal pre-test" description="Tambahkan soal untuk pemetaan awal sebelum materi dibuka." />
                @endforelse
            </div>
        @else
            <x-empty-state title="Pre-test belum dibuat" description="Opsional, namun direkomendasikan untuk memetakan kemampuan awal karyawan." />
        @endif
    </x-card>
    </section>

    <!-- Section 2: Post-test & bank soal -->
    <section x-show="activeSection === 'post-test'" x-cloak>
    <x-card>
        <x-slot name="header">
            <div class="flex items-center justify-between w-full">
                <h3 class="font-bold font-heading text-lg">Post-test &amp; Bank Soal</h3>
                @if($training->postTest)
                    <x-button @click="openQuestion('{{ $training->postTest->id }}', 'Post-test')" variant="primary" size="sm">
                        Tambah Soal Kuis
                    </x-button>
                @endif
            </div>
        </x-slot>

        @if($training->postTest)
            <div class="space-y-6">
                <div class="p-4 rounded-lg bg-green-50 dark:bg-green-950/30 border border-green-200 dark:border-green-800 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <h4 class="font-bold text-brand-dark dark:text-green-400 text-lg">{{ $training->postTest->judul }}</h4>
                        <p class="text-xs text-gray-600 dark:text-gray-300">Durasi: {{ $training->postTest->durasi_menit }} Menit &bull; Nilai kelulusan: {{ $training->postTest->passing_grade }}% &bull; Masa tunggu: {{ $training->postTest->cooldown_hours }} jam &bull; Total Soal: {{ $training->postTest->questions->count() }} Soal</p>
                    </div>
                </div>

                <!-- List Question Bank -->
                <div class="space-y-4">
                    @forelse($training->postTest->questions as $idx => $q)
                        <div class="p-4 rounded-lg bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 shadow-sm space-y-3">
                            <div class="flex items-start justify-between">
                                <div class="font-semibold text-sm text-gray-900 dark:text-gray-100 flex gap-2">
                                    <span>{{ $idx + 1 }}.</span>
                                    <div>{!! nl2br(e($q->pertanyaan)) !!}</div>
                                </div>
                                <form action="{{ route('admin.questions.destroy', $q->id) }}" method="POST" data-confirm="Hapus soal ini?">
                                    @csrf
                                    @method('DELETE')
                                    <x-button type="submit" variant="ghost" size="sm" class="text-red-600" aria-label="Hapus" title="Hapus"><x-icon name="trash" class="h-4 w-4" /></x-button>
                                </form>
                            </div>

                            <!-- Media preview if any -->
                            @if($q->tipe_media === 'video_gdrive' || $q->tipe_media === 'video_youtube')
                                <div class="w-full max-w-sm aspect-video rounded-lg overflow-hidden shadow-sm my-2">
                                    <iframe title="Video pembelajaran" loading="lazy" src="{{ $q->embed_url }}" class="w-full h-full border-0" allowfullscreen></iframe>
                                </div>
                            @elseif($q->tipe_media === 'image' && $q->media_url)
                                <img src="{{ $q->media_url }}" class="max-h-36 rounded-lg border border-gray-200 my-2" alt="Gambar Soal">
                            @endif

                            <!-- Options A, B, C, D -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs pt-2">
                                <div class="p-2.5 rounded-lg border {{ $q->kunci_jawaban === 'a' ? 'bg-green-100 dark:bg-green-950/60 border-green-500 font-bold text-brand-dark dark:text-green-300' : 'bg-gray-50 dark:bg-gray-700/50 border-gray-200 dark:border-gray-700' }}">
                                    <strong>A.</strong> {{ $q->opsi_a }}
                                </div>
                                <div class="p-2.5 rounded-lg border {{ $q->kunci_jawaban === 'b' ? 'bg-green-100 dark:bg-green-950/60 border-green-500 font-bold text-brand-dark dark:text-green-300' : 'bg-gray-50 dark:bg-gray-700/50 border-gray-200 dark:border-gray-700' }}">
                                    <strong>B.</strong> {{ $q->opsi_b }}
                                </div>
                                <div class="p-2.5 rounded-lg border {{ $q->kunci_jawaban === 'c' ? 'bg-green-100 dark:bg-green-950/60 border-green-500 font-bold text-brand-dark dark:text-green-300' : 'bg-gray-50 dark:bg-gray-700/50 border-gray-200 dark:border-gray-700' }}">
                                    <strong>C.</strong> {{ $q->opsi_c }}
                                </div>
                                <div class="p-2.5 rounded-lg border {{ $q->kunci_jawaban === 'd' ? 'bg-green-100 dark:bg-green-950/60 border-green-500 font-bold text-brand-dark dark:text-green-300' : 'bg-gray-50 dark:bg-gray-700/50 border-gray-200 dark:border-gray-700' }}">
                                    <strong>D.</strong> {{ $q->opsi_d }}
                                </div>
                            </div>
                        </div>
                    @empty
                        <x-empty-state title="Belum Ada Soal" description="Klik Tambah Soal Kuis untuk membuat soal pilihan ganda." />
                    @endforelse
                </div>
            </div>
        @else
            <x-empty-state title="Post-test belum dibuat" description="Buat post-test untuk mencapai Balok 1." />
        @endif
    </x-card>
    </section>

    <section x-show="false" x-cloak aria-hidden="true">
    <x-card>
        <x-slot name="header"><div class="flex items-center justify-between w-full"><h3 class="font-bold font-heading text-lg">Assessment level</h3>@if($training->assessment)<x-button @click="openQuestion('{{ $training->assessment->id }}', 'Assessment')" variant="primary" size="sm">Tambah soal</x-button>@endif</div></x-slot>
        @if($training->assessment)
            <div class="rounded-lg border border-amber-200 bg-amber-50 p-4 dark:border-amber-900 dark:bg-amber-950/30">
                <p class="font-semibold text-amber-950 dark:text-amber-100">{{ $training->assessment->judul }}</p>
                <p class="mt-1 text-xs text-amber-800 dark:text-amber-200">Penentu kenaikan level &bull; Durasi {{ $training->assessment->durasi_menit }} menit &bull; {{ $training->assessment->questions->count() }} soal &bull; Maksimal {{ $training->assessment->max_retries }} percobaan.</p>
            </div>
            <div class="mt-4 space-y-2">
                @forelse($training->assessment->questions as $index => $question)
                    <div class="flex items-start justify-between gap-3 rounded-md border border-gray-200 p-3 text-sm dark:border-gray-700">
                        <span><strong>{{ $index + 1 }}.</strong> {{ $question->pertanyaan }}</span>
                        <form action="{{ route('admin.questions.destroy', $question->id) }}" method="POST" data-confirm="Hapus soal ini?">@csrf @method('DELETE')<x-button type="submit" variant="ghost" size="sm" class="text-red-600" aria-label="Hapus"><x-icon name="trash" class="h-4 w-4" /></x-button></form>
                    </div>
                @empty
                    <x-empty-state title="Belum ada soal assessment" description="Tambahkan soal penentu kelulusan sub-course." />
                @endforelse
            </div>
        @else
            <x-empty-state title="Assessment level belum dibuat" description="Assessment ini menentukan kelulusan level setelah post-test." />
        @endif
    </x-card>
    </section>

    <!-- Modal Tambah Materi -->
    <x-modal name="add-material-modal" :show="$errors->any() && old('_modal') === 'add-material-modal'" title="Tambah Materi Pembelajaran">
        <form action="{{ route('admin.trainings.materials.store', $training->id) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
            @csrf
            <input type="hidden" name="_modal" value="add-material-modal">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1" for="add-material-modal-judul">Judul Materi</label>
                <x-input type="text" name="judul" placeholder="MISAL: Video SOP Pengoperasian Mesin" required id="add-material-modal-judul" />
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1" for="add-material-modal-tipe_media">Tipe Media</label>
                <x-select name="tipe_media" required id="add-material-modal-tipe_media">
                    <option value="text">Teks</option>
                    <option value="video_gdrive">Video Google Drive Embed (Rekomendasi)</option>
                    <option value="video_youtube">Video YouTube Link</option>
                    <option value="image">Gambar</option><option value="pdf">Dokumen PDF</option>
                </x-select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1" for="add-material-modal-konten_teks">Penjelasan Teks / Instruksi</label>
                <x-textarea name="konten_teks" rows="3" placeholder="Instruksi kerja / penjelas materi..." id="add-material-modal-konten_teks"></x-textarea>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1" for="add-material-modal-media_url">Link Media Video (Google Drive / YouTube URL)</label>
                <x-input type="text" name="media_url" placeholder="https://drive.google.com/file/d/1A2B3C.../view" id="add-material-modal-media_url" />
                <p class="text-[11px] text-gray-400 mt-1">Gunakan link Google Drive dengan izin "Anyone with the link can view".</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1" for="add-material-modal-file_upload">Unggah gambar atau PDF (maksimal 5 MB)</label>
                <x-input type="file" name="file_upload" accept="image/*,.pdf" id="add-material-modal-file_upload" />
            </div>
            <div class="flex justify-end gap-2 pt-4">
                <x-button type="button" @click="$dispatch('close-modal', 'add-material-modal')" variant="outline">Batal</x-button>
                <x-button type="submit" variant="primary">Simpan Materi</x-button>
            </div>
        </form>
    </x-modal>

    <!-- Modal Tambah evaluasi -->
    @if(!$training->preTest || !$training->postTest)
        <x-modal name="add-quiz-modal" :show="$errors->any() && old('_modal') === 'add-quiz-modal'" title="Buat pre-test atau post-test">
            <form action="{{ route('admin.quizzes.store') }}" method="POST" class="space-y-4">
                @csrf
            <input type="hidden" name="_modal" value="add-quiz-modal">
                <input type="hidden" name="training_id" value="{{ $training->id }}">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1" for="add-quiz-modal-tipe">Jenis evaluasi</label>
                    <x-select name="tipe" required id="add-quiz-modal-tipe">
                        @if(!$training->preTest)<option value="pre_test" @selected(old('tipe') === 'pre_test')>Pre-test (pemetaan awal)</option>@endif
                        @if(!$training->postTest)<option value="post_test" @selected(old('tipe', !$training->preTest ? '' : 'post_test') === 'post_test')>Post-test (validasi materi)</option>@endif
                    </x-select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1" for="add-quiz-modal-judul">Judul Kuis</label>
                    <x-input type="text" name="judul" :value="old('judul', 'Evaluasi '.$training->judul)" required id="add-quiz-modal-judul" />
                </div>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1" for="add-quiz-modal-durasi_menit">Durasi (Menit)</label>
                        <x-input type="number" name="durasi_menit" value="15" min="1" required id="add-quiz-modal-durasi_menit" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1" for="add-quiz-modal-passing_grade">Nilai kelulusan (%)</label>
                        <x-input type="number" name="passing_grade" :value="old('passing_grade', $training->passing_grade)" min="50" max="100" required id="add-quiz-modal-passing_grade" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1" for="add-quiz-modal-max_retries">Batas percobaan</label>
                        <x-input type="number" name="max_retries" :value="old('max_retries', $training->max_retries)" min="1" required id="add-quiz-modal-max_retries" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1" for="add-quiz-modal-cooldown_hours">Masa tunggu (jam)</label>
                        <x-input type="number" name="cooldown_hours" :value="old('cooldown_hours', 24)" min="1" max="168" required id="add-quiz-modal-cooldown_hours" />
                    </div>
                </div>
                <div class="flex justify-end gap-2 pt-4">
                    <x-button type="button" @click="$dispatch('close-modal', 'add-quiz-modal')" variant="outline">Batal</x-button>
                    <x-button type="submit" variant="primary">Tambah evaluasi</x-button>
                </div>
            </form>
        </x-modal>
    @endif

    <!-- Modal Tambah Soal Kuis -->
    @if($training->preTest || $training->postTest)
        <x-modal name="add-question-modal" :show="$errors->any() && old('_modal') === 'add-question-modal'" title="Tambah Soal Kuis">
            <form action="{{ route('admin.questions.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
            <input type="hidden" name="_modal" value="add-question-modal">
                <div class="rounded-md border border-green-200 bg-green-50 px-3 py-2 text-sm text-brand-dark dark:border-green-900 dark:bg-green-950/30 dark:text-green-200">
                    <span class="font-semibold">Tahap evaluasi:</span> <span x-text="selectedQuizLabel || 'Evaluasi yang dipilih'"></span>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1" for="add-question-modal-quiz_id">Jenis evaluasi</label>
                    <x-select name="quiz_id" required id="add-question-modal-quiz_id" x-model="selectedQuizId" class="hidden">
                        @if($training->preTest)<option value="{{ $training->preTest->id }}">Pre-test — {{ $training->preTest->judul }}</option>@endif
                        @if($training->postTest)<option value="{{ $training->postTest->id }}">Post-test — {{ $training->postTest->judul }}</option>@endif
                    </x-select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1" for="add-question-modal-pertanyaan">Pertanyaan Soal</label>
                    <x-textarea name="pertanyaan" rows="3" required placeholder="Tuliskan pertanyaan..." id="add-question-modal-pertanyaan"></x-textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1" for="add-question-modal-tipe_media">Media Pendukung Soal (Opsional)</label>
                    <x-select name="tipe_media" id="add-question-modal-tipe_media">
                        <option value="none">Tanpa Media</option>
                        <option value="video_gdrive">Video Google Drive Embed</option>
                        <option value="video_youtube">Video YouTube</option>
                        <option value="image">Upload Foto Gambar</option>
                    </x-select>
                    <label for="add-question-modal-media_url" class="mt-3 mb-1 block text-sm text-gray-600 dark:text-gray-300">URL video (jika menggunakan video)</label>
                    <x-input type="text" name="media_url" placeholder="URL Google Drive / YouTube" class="mb-2" id="add-question-modal-media_url" />
                    <label for="add-question-modal-file_upload" class="mb-1 block text-sm text-gray-600 dark:text-gray-300">Unggah gambar (opsional)</label>
                    <x-input type="file" name="file_upload" accept="image/*" id="add-question-modal-file_upload" />
                </div>
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                    <div>
                        <label class="block text-xs font-bold text-gray-600 mb-1" for="add-question-modal-opsi_a">Pilihan Opsi A</label>
                        <x-input type="text" name="opsi_a" required id="add-question-modal-opsi_a" />
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-600 mb-1" for="add-question-modal-opsi_b">Pilihan Opsi B</label>
                        <x-input type="text" name="opsi_b" required id="add-question-modal-opsi_b" />
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-600 mb-1" for="add-question-modal-opsi_c">Pilihan Opsi C</label>
                        <x-input type="text" name="opsi_c" required id="add-question-modal-opsi_c" />
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-600 mb-1" for="add-question-modal-opsi_d">Pilihan Opsi D</label>
                        <x-input type="text" name="opsi_d" required id="add-question-modal-opsi_d" />
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-bold text-brand-dark dark:text-green-400 mb-1" for="add-question-modal-kunci_jawaban">Kunci Jawaban Benar</label>
                    <x-select name="kunci_jawaban" required id="add-question-modal-kunci_jawaban">
                        <option value="a">Jawaban A</option>
                        <option value="b">Jawaban B</option>
                        <option value="c">Jawaban C</option>
                        <option value="d">Jawaban D</option>
                    </x-select>
                </div>
                <div class="flex justify-end gap-2 pt-4">
                    <x-button type="button" @click="$dispatch('close-modal', 'add-question-modal')" variant="outline">Batal</x-button>
                    <x-button type="submit" variant="primary">Simpan Soal</x-button>
                </div>
            </form>
        </x-modal>
    @endif
</div>
@endsection
