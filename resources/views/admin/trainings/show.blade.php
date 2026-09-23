@extends('layouts.app')

@section('title', $training->judul . ' - Pojok Ilmu MWT')

@section('content')
@include('layouts.navigation')

<div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8 space-y-8" x-data="{ showMaterialModal: false, showQuizModal: false, showQuestionModal: false }">
    
    <!-- Back & Header -->
    <div>
        <a href="{{ route('admin.trainings.index') }}" class="text-xs font-bold text-gray-500 hover:text-brand-dark flex items-center gap-1 mb-2">
            &larr; Kembali ke Daftar Training
        </a>
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2">
                    <span class="px-2.5 py-1 text-xs font-mono font-bold rounded-lg bg-green-100 dark:bg-green-950/60 text-brand-dark dark:text-green-400">
                        {{ $training->kode_training }}
                    </span>
                    <span class="px-2.5 py-1 text-xs font-bold rounded-lg bg-blue-100 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300">
                        Level {{ $training->level }}
                    </span>
                </div>
                <h1 class="text-3xl font-extrabold font-heading text-brand-dark dark:text-green-400 mt-2">{{ $training->judul }}</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400">Skill: {{ $training->skill->nama_skill }} &bull; Target Passing Grade: {{ $training->passing_grade }}% &bull; Max Retries: {{ $training->max_retries }}x</p>
            </div>
            <div class="flex items-center gap-2">
                <x-button @click="showMaterialModal = true" variant="primary" size="md">
                    + Tambah Materi Pelatihan
                </x-button>
                @if(!$training->quiz)
                    <x-button @click="showQuizModal = true" variant="secondary" size="md">
                        + Buat Kuis Assessment
                    </x-button>
                @endif
            </div>
        </div>
    </div>

    <!-- Section 1: Daftar Materi Pelatihan -->
    <x-card>
        <x-slot name="header">
            <h3 class="font-bold font-heading text-lg">Modul Materi Pembelajaran ({{ $training->materials->count() }})</h3>
        </x-slot>

        <div class="space-y-4">
            @forelse($training->materials as $mat)
                <div class="p-4 rounded-2xl bg-gray-50 dark:bg-gray-800/50 border border-gray-200 dark:border-gray-700 space-y-3">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
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

                        <form action="{{ route('admin.materials.destroy', $mat->id) }}" method="POST" onsubmit="return confirm('Hapus materi ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-xs text-red-600 hover:underline">Hapus</button>
                        </form>
                    </div>

                    @if($mat->konten_teks)
                        <p class="text-xs text-gray-600 dark:text-gray-300 leading-relaxed pl-9">{{ $mat->konten_teks }}</p>
                    @endif

                    <!-- Video Preview (Google Drive / YouTube) -->
                    @if($mat->tipe_media === 'video_gdrive' || $mat->tipe_media === 'video_youtube')
                        <div class="pl-9 pt-2">
                            <div class="relative w-full max-w-xl aspect-video rounded-xl bg-black overflow-hidden shadow-md">
                                <iframe src="{{ $mat->embed_url }}" class="w-full h-full border-0" allow="autoplay" allowfullscreen></iframe>
                            </div>
                        </div>
                    @elseif($mat->tipe_media === 'image' && $mat->media_url)
                        <div class="pl-9 pt-2">
                            <img src="{{ $mat->media_url }}" alt="Media Gambar" class="max-h-48 rounded-xl object-cover shadow-sm border border-gray-200">
                        </div>
                    @endif
                </div>
            @empty
                <x-empty-state title="Belum Ada Materi" description="Tambahkan modul materi teks, foto, atau video Google Drive untuk pelatihan ini." />
            @endforelse
        </div>
    </x-card>

    <!-- Section 2: Kuis Assessment & Bank Soal -->
    <x-card>
        <x-slot name="header">
            <div class="flex items-center justify-between w-full">
                <h3 class="font-bold font-heading text-lg">Modul Assessment & Bank Soal</h3>
                @if($training->quiz)
                    <x-button @click="showQuestionModal = true" variant="primary" size="sm">
                        + Tambah Soal Kuis
                    </x-button>
                @endif
            </div>
        </x-slot>

        @if($training->quiz)
            <div class="space-y-6">
                <div class="p-4 rounded-2xl bg-green-50 dark:bg-green-950/30 border border-green-200 dark:border-green-800 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <h4 class="font-bold text-brand-dark dark:text-green-400 text-lg">{{ $training->quiz->judul }}</h4>
                        <p class="text-xs text-gray-600 dark:text-gray-300">Durasi: {{ $training->quiz->durasi_menit }} Menit &bull; Passing Grade: {{ $training->quiz->passing_grade }}% &bull; Total Soal: {{ $training->quiz->questions->count() }} Soal</p>
                    </div>
                </div>

                <!-- List Question Bank -->
                <div class="space-y-4">
                    @forelse($training->quiz->questions as $idx => $q)
                        <div class="p-4 rounded-2xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 shadow-sm space-y-3">
                            <div class="flex items-start justify-between">
                                <div class="font-semibold text-sm text-gray-900 dark:text-gray-100 flex gap-2">
                                    <span>{{ $idx + 1 }}.</span>
                                    <div>{!! nl2br(e($q->pertanyaan)) !!}</div>
                                </div>
                                <form action="{{ route('admin.questions.destroy', $q->id) }}" method="POST" onsubmit="return confirm('Hapus soal ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-xs text-red-600 hover:underline">Hapus</button>
                                </form>
                            </div>

                            <!-- Media preview if any -->
                            @if($q->tipe_media === 'video_gdrive' || $q->tipe_media === 'video_youtube')
                                <div class="w-full max-w-sm aspect-video rounded-xl overflow-hidden shadow-sm my-2">
                                    <iframe src="{{ $q->embed_url }}" class="w-full h-full border-0" allowfullscreen></iframe>
                                </div>
                            @elseif($q->tipe_media === 'image' && $q->media_url)
                                <img src="{{ $q->media_url }}" class="max-h-36 rounded-xl border border-gray-200 my-2" alt="Gambar Soal">
                            @endif

                            <!-- Options A, B, C, D -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs pt-2">
                                <div class="p-2.5 rounded-xl border {{ $q->kunci_jawaban === 'a' ? 'bg-green-100 dark:bg-green-950/60 border-green-500 font-bold text-brand-dark dark:text-green-300' : 'bg-gray-50 dark:bg-gray-700/50 border-gray-200 dark:border-gray-700' }}">
                                    <strong>A.</strong> {{ $q->opsi_a }}
                                </div>
                                <div class="p-2.5 rounded-xl border {{ $q->kunci_jawaban === 'b' ? 'bg-green-100 dark:bg-green-950/60 border-green-500 font-bold text-brand-dark dark:text-green-300' : 'bg-gray-50 dark:bg-gray-700/50 border-gray-200 dark:border-gray-700' }}">
                                    <strong>B.</strong> {{ $q->opsi_b }}
                                </div>
                                <div class="p-2.5 rounded-xl border {{ $q->kunci_jawaban === 'c' ? 'bg-green-100 dark:bg-green-950/60 border-green-500 font-bold text-brand-dark dark:text-green-300' : 'bg-gray-50 dark:bg-gray-700/50 border-gray-200 dark:border-gray-700' }}">
                                    <strong>C.</strong> {{ $q->opsi_c }}
                                </div>
                                <div class="p-2.5 rounded-xl border {{ $q->kunci_jawaban === 'd' ? 'bg-green-100 dark:bg-green-950/60 border-green-500 font-bold text-brand-dark dark:text-green-300' : 'bg-gray-50 dark:bg-gray-700/50 border-gray-200 dark:border-gray-700' }}">
                                    <strong>D.</strong> {{ $q->opsi_d }}
                                </div>
                            </div>
                        </div>
                    @empty
                        <x-empty-state title="Belum Ada Soal" description="Klik + Tambah Soal Kuis untuk membuat soal pilihan ganda." />
                    @endforelse
                </div>
            </div>
        @else
            <x-empty-state title="Kuis Belum Dibuat" description="Klik + Buat Kuis Assessment untuk membuat ujian evaluasi untuk level ini." />
        @endif
    </x-card>

    <!-- Modal Tambah Materi -->
    <x-modal name="add-material-modal" show="showMaterialModal" @close="showMaterialModal = false" title="Tambah Materi Pembelajaran">
        <form action="{{ route('admin.trainings.materials.store', $training->id) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Judul Materi</label>
                <x-input type="text" name="judul" placeholder="MISAL: Video SOP Pengoperasian Mesin" required />
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tipe Media</label>
                <select name="tipe_media" required class="w-full px-3 py-2.5 rounded-xl border border-gray-300 dark:border-gray-700 dark:bg-gray-800 text-sm font-semibold">
                    <option value="text">Teks / Rich Text</option>
                    <option value="video_gdrive">Video Google Drive Embed (Rekomendasi)</option>
                    <option value="video_youtube">Video YouTube Link</option>
                    <option value="image">Upload Foto / Gambar</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Penjelasan Teks / Instruksi</label>
                <textarea name="konten_teks" rows="3" class="w-full px-3 py-2 rounded-xl text-sm border border-gray-300 dark:border-gray-700 dark:bg-gray-800" placeholder="Instruksi kerja / penjelas materi..."></textarea>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Link Media Video (Google Drive / YouTube URL)</label>
                <x-input type="text" name="media_url" placeholder="https://drive.google.com/file/d/1A2B3C.../view" />
                <p class="text-[11px] text-gray-400 mt-1">Gunakan link Google Drive dengan izin "Anyone with the link can view".</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Upload File (Jika Foto/Gambar)</label>
                <x-input type="file" name="file_upload" accept="image/*,.pdf" />
            </div>
            <div class="flex justify-end gap-2 pt-4">
                <x-button type="button" @click="showMaterialModal = false" variant="outline">Batal</x-button>
                <x-button type="submit" variant="primary">Simpan Materi</x-button>
            </div>
        </form>
    </x-modal>

    <!-- Modal Buat Kuis -->
    @if(!$training->quiz)
        <x-modal name="add-quiz-modal" show="showQuizModal" @close="showQuizModal = false" title="Buat Modul Kuis Assessment">
            <form action="{{ route('admin.quizzes.store') }}" method="POST" class="space-y-4">
                @csrf
                <input type="hidden" name="training_id" value="{{ $training->id }}">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Judul Kuis</label>
                    <x-input type="text" name="judul" value="Assessment {{ $training->judul }}" required />
                </div>
                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Durasi (Menit)</label>
                        <x-input type="number" name="durasi_menit" value="15" min="1" required />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Passing Grade (%)</label>
                        <x-input type="number" name="passing_grade" value="{{ $training->passing_grade }}" min="50" max="100" required />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Max Retries</label>
                        <x-input type="number" name="max_retries" value="{{ $training->max_retries }}" min="1" required />
                    </div>
                </div>
                <div class="flex justify-end gap-2 pt-4">
                    <x-button type="button" @click="showQuizModal = false" variant="outline">Batal</x-button>
                    <x-button type="submit" variant="primary">Buat Kuis</x-button>
                </div>
            </form>
        </x-modal>
    @endif

    <!-- Modal Tambah Soal Kuis -->
    @if($training->quiz)
        <x-modal name="add-question-modal" show="showQuestionModal" @close="showQuestionModal = false" title="Tambah Soal Kuis">
            <form action="{{ route('admin.quizzes.questions.store', $training->quiz->id) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Pertanyaan Soal</label>
                    <textarea name="pertanyaan" rows="3" required class="w-full px-3 py-2 rounded-xl text-sm border border-gray-300 dark:border-gray-700 dark:bg-gray-800" placeholder="Tuliskan pertanyaan..."></textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Media Pendukung Soal (Opsional)</label>
                    <select name="tipe_media" class="w-full px-3 py-2 rounded-xl border border-gray-300 dark:border-gray-700 dark:bg-gray-800 text-sm mb-2">
                        <option value="none">Tanpa Media</option>
                        <option value="video_gdrive">Video Google Drive Embed</option>
                        <option value="video_youtube">Video YouTube</option>
                        <option value="image">Upload Foto Gambar</option>
                    </select>
                    <x-input type="text" name="media_url" placeholder="URL Google Drive Video / YouTube (jika video)" class="mb-2" />
                    <x-input type="file" name="file_upload" accept="image/*" />
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-gray-600 mb-1">Pilihan Opsi A</label>
                        <x-input type="text" name="opsi_a" required />
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-600 mb-1">Pilihan Opsi B</label>
                        <x-input type="text" name="opsi_b" required />
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-600 mb-1">Pilihan Opsi C</label>
                        <x-input type="text" name="opsi_c" required />
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-600 mb-1">Pilihan Opsi D</label>
                        <x-input type="text" name="opsi_d" required />
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-bold text-brand-dark dark:text-green-400 mb-1">Kunci Jawaban Benar</label>
                    <select name="kunci_jawaban" required class="w-full px-3 py-2.5 rounded-xl border-2 border-brand-light dark:bg-gray-800 text-sm font-bold">
                        <option value="a">Jawaban A</option>
                        <option value="b">Jawaban B</option>
                        <option value="c">Jawaban C</option>
                        <option value="d">Jawaban D</option>
                    </select>
                </div>
                <div class="flex justify-end gap-2 pt-4">
                    <x-button type="button" @click="showQuestionModal = false" variant="outline">Batal</x-button>
                    <x-button type="submit" variant="primary">Simpan Soal</x-button>
                </div>
            </form>
        </x-modal>
    @endif
</div>
@endsection
