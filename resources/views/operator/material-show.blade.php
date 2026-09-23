@extends('layouts.app')

@section('title', $training->judul . ' - Modul Pembelajaran Pojok Ilmu')

@section('content')
@include('layouts.navigation')

<div class="max-w-4xl mx-auto py-8 px-4 sm:px-6 lg:px-8 space-y-8">
    
    <!-- Back & Title -->
    <div>
        <a href="{{ route('operator.dashboard') }}" class="text-xs font-bold text-gray-500 hover:text-brand-dark flex items-center gap-1 mb-2">
            &larr; Kembali ke Dashboard Saya
        </a>
        <div class="flex items-center gap-2 mb-1">
            <span class="px-2.5 py-1 text-xs font-mono font-bold rounded-lg bg-green-100 dark:bg-green-950/60 text-brand-dark dark:text-green-400">
                {{ $training->kode_training }}
            </span>
            <span class="px-2.5 py-1 text-xs font-bold rounded-lg bg-blue-100 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300">
                Level {{ $training->level }}
            </span>
        </div>
        <h1 class="text-3xl font-extrabold font-heading text-brand-dark dark:text-green-400">{{ $training->judul }}</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400">Pelajari seluruh modul secara mendalam sebelum mengambil ujian assessment.</p>
    </div>

    <!-- Materials Content List -->
    <div class="space-y-6">
        @forelse($training->materials as $mat)
            <x-card class="shadow-lg border border-gray-100 dark:border-gray-700 rounded-3xl overflow-hidden">
                <x-slot name="header">
                    <div class="flex items-center gap-3">
                        <span class="w-8 h-8 rounded-xl bg-brand-dark text-white font-extrabold text-sm flex items-center justify-center shadow-md">
                            {{ $mat->urutan }}
                        </span>
                        <h3 class="font-bold font-heading text-lg text-gray-900 dark:text-gray-100">{{ $mat->judul }}</h3>
                    </div>
                </x-slot>

                <div class="space-y-4">
                    @if($mat->konten_teks)
                        <div class="prose dark:prose-invert max-w-none text-sm text-gray-700 dark:text-gray-300 leading-relaxed">
                            {!! nl2br(e($mat->konten_teks)) !!}
                        </div>
                    @endif

                    <!-- Embedded Video Player (Google Drive / YouTube) -->
                    @if($mat->tipe_media === 'video_gdrive' || $mat->tipe_media === 'video_youtube')
                        <div class="pt-2">
                            <div class="relative w-full aspect-video rounded-2xl bg-black overflow-hidden shadow-xl border border-gray-800">
                                <iframe src="{{ $mat->embed_url }}" class="w-full h-full border-0" allow="autoplay" allowfullscreen></iframe>
                            </div>
                            <p class="text-[11px] text-gray-400 text-center mt-2">Pemain Video Google Drive &bull; Tekan tombol play untuk menonton</p>
                        </div>
                    @elseif($mat->tipe_media === 'image' && $mat->media_url)
                        <div class="pt-2 flex justify-center">
                            <img src="{{ $mat->media_url }}" alt="Materi Gambar" class="max-h-96 rounded-2xl object-contain shadow-md border border-gray-200 dark:border-gray-700">
                        </div>
                    @endif
                </div>
            </x-card>
        @empty
            <x-card>
                <x-empty-state title="Materi Belum Tersedia" description="Modul materi pelatihan sedang disiapkan oleh Admin." />
            </x-card>
        @endforelse
    </div>

    <!-- Bottom Action to Start Assessment -->
    @if($training->quiz)
        <div class="p-6 rounded-3xl bg-gradient-to-r from-green-100 to-brand-surface dark:from-gray-800 dark:to-gray-900 border-2 border-brand-light/40 flex flex-col sm:flex-row items-center justify-between gap-4 shadow-xl">
            <div>
                <h4 class="font-bold font-heading text-lg text-brand-dark dark:text-green-400">Sudah Selesai Mempelajari Seluruh Materi?</h4>
                <p class="text-xs text-gray-600 dark:text-gray-400">Ujian: {{ $training->quiz->judul }} &bull; Durasi: {{ $training->quiz->durasi_menit }} Menit &bull; Target: {{ $training->quiz->passing_grade }}%</p>
            </div>
            <a href="{{ route('operator.quizzes.show', $training->quiz->id) }}" class="px-6 py-3.5 rounded-xl font-extrabold text-base text-white bg-brand-dark hover:bg-green-900 shadow-xl shadow-green-900/30 transition-transform hover:scale-105">
                Mulai Ujian Assessment &rarr;
            </a>
        </div>
    @endif
</div>
@endsection
