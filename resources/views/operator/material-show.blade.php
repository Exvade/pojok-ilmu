@extends('layouts.app')

@section('title', $training->judul . ' - Modul Pembelajaran Pojok Ilmu')

@section('content')


<div class="page-shell !max-w-5xl">
    
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
        <h1 class="page-title">{{ $training->judul }}</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400">Pelajari seluruh modul sebelum menandainya selesai dan mengambil post-test.</p>
    </div>

    <!-- Materials Content List -->
    <div class="space-y-6">
        @forelse($training->materials as $mat)
            <x-card class="shadow-none border border-gray-100 dark:border-gray-700 rounded-lg overflow-hidden">
                <x-slot name="header">
                    <div class="flex items-center gap-3 flex-wrap">
                        <span class="w-8 h-8 rounded-lg bg-brand-dark text-white font-semibold text-sm flex items-center justify-center shadow-none">
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
                            <div class="relative w-full aspect-video rounded-lg bg-black overflow-hidden shadow-none border border-gray-800">
                                <iframe title="Video pembelajaran" loading="lazy" src="{{ $mat->embed_url }}" class="w-full h-full border-0" allow="autoplay" allowfullscreen></iframe>
                            </div>
                            <p class="text-[11px] text-gray-400 text-center mt-2">Tekan tombol putar untuk menonton video pembelajaran.</p>
                        </div>
                    @elseif($mat->tipe_media === 'image' && $mat->media_url)
                        <div class="pt-2 flex justify-center">
                            <img src="{{ $mat->media_url }}" alt="Materi Gambar" class="max-h-96 rounded-lg object-contain shadow-none border border-gray-200 dark:border-gray-700">
                        </div>
                    @elseif($mat->tipe_media === 'pdf' && $mat->media_url)
                        <x-button :href="$mat->media_url" target="_blank" rel="noopener" variant="outline">Buka dokumen PDF <x-icon name="arrow" /></x-button>
                    @endif
                </div>
            </x-card>
        @empty
            <x-card>
                <x-empty-state title="Materi Belum Tersedia" description="Modul materi pelatihan sedang disiapkan oleh Admin." />
            </x-card>
        @endforelse
    </div>

    <!-- Completion gate: post-test is only opened after the employee confirms reviewing the material. -->
    @if($training->postTest && $training->postTest->questions->isNotEmpty())
        <div class="p-6 rounded-lg bg-gradient-to-r from-green-100 to-brand-surface dark:from-gray-800 dark:to-gray-900 border-2 border-brand-light/40 flex flex-col sm:flex-row items-center justify-between gap-4 shadow-none">
            <div>
                <h4 class="font-bold font-heading text-lg text-brand-dark dark:text-green-400">Sudah Selesai Mempelajari Seluruh Materi?</h4>
                <p class="text-xs text-gray-600 dark:text-gray-400">Post-test: {{ $training->postTest->judul }} &bull; Durasi: {{ $training->postTest->durasi_menit }} Menit &bull; Target: {{ $training->postTest->passing_grade }}%</p>
            </div>
            @if($enrollment->next_attempt_at?->isFuture())
                <p class="text-xs font-medium text-amber-700 dark:text-amber-300">Post-test tersedia kembali: {{ $enrollment->next_attempt_at->translatedFormat('d M Y, H:i') }}</p>
            @else
                <form action="{{ route('operator.materials.complete', $training->id) }}" method="POST">
                    @csrf
                    <x-button type="submit">Tandai selesai &amp; mulai post-test <x-icon name="arrow" /></x-button>
                </form>
            @endif
        </div>
    @endif
</div>
@endsection
