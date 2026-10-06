@extends('layouts.app')

@section('title', $quiz->judul . ' - Assessment Pojok Ilmu')

@section('content')


<div class="page-shell !max-w-4xl" x-data="quizEngine({{ $quiz->durasi_menit }}, {{ $quiz->questions->count() }})">
    
    <!-- Assessment Sticky Header with Timer -->
    <div class="sticky top-3 z-20 bg-white/90 dark:bg-gray-800/90 backdrop-blur-md p-4 rounded-lg shadow-none border border-gray-200 dark:border-gray-700 flex flex-wrap items-center justify-between gap-3">
        <div>
            <h2 class="font-semibold font-heading text-lg text-brand-dark dark:text-green-400">{{ $quiz->judul }}</h2>
            <p class="text-xs text-gray-500">Nilai kelulusan: {{ $quiz->passing_grade }}% &bull; Total: {{ $quiz->questions->count() }} Soal</p>
        </div>

        <div class="flex items-center gap-2 bg-red-100 dark:bg-red-950/60 px-4 py-2 rounded-lg border border-red-200 dark:border-red-800 text-red-700 dark:text-red-300 font-mono font-bold text-sm">
            <svg class="w-5 h-5 " fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <span aria-label="Sisa waktu" x-text="formatTime(timeLeft)"></span>
        </div>
    </div>

    <!-- Quiz Form -->
    <form id="quizForm" x-ref="quizForm" @submit.prevent="confirmSubmit()" @change="countAnswers()" action="{{ route('operator.quizzes.submit', $quiz->id) }}" method="POST" class="space-y-6">
        @csrf

        @foreach($quiz->questions as $idx => $q)
            <x-card class="shadow-none border border-gray-100 dark:border-gray-700 rounded-lg">
                <div class="space-y-4">
                    <div class="flex items-start gap-3">
                        <span class="w-8 h-8 rounded-lg bg-brand-dark text-white font-semibold text-sm flex items-center justify-center shrink-0">
                            {{ $idx + 1 }}
                        </span>
                        <div class="font-bold text-base text-gray-900 dark:text-gray-100 leading-snug pt-1">
                            {!! nl2br(e($q->pertanyaan)) !!}
                        </div>
                    </div>

                    <!-- Question Media Preview (GDrive Video / Image) -->
                    @if($q->tipe_media === 'video_gdrive' || $q->tipe_media === 'video_youtube')
                        <div class="sm:pl-11 pt-1">
                            <div class="relative w-full max-w-lg aspect-video rounded-lg bg-black overflow-hidden shadow-none">
                                <iframe title="Video pembelajaran" loading="lazy" src="{{ $q->embed_url }}" class="w-full h-full border-0" allowfullscreen></iframe>
                            </div>
                        </div>
                    @elseif($q->tipe_media === 'image' && $q->media_url)
                        <div class="pl-11 pt-1">
                            <img src="{{ $q->media_url }}" alt="Media Soal" class="max-h-48 rounded-lg object-contain shadow-sm border border-gray-200">
                        </div>
                    @endif

                    <!-- Radio Options A, B, C, D -->
                    <div class="pl-11 space-y-2.5 pt-2">
                        @foreach(['a' => $q->opsi_a, 'b' => $q->opsi_b, 'c' => $q->opsi_c, 'd' => $q->opsi_d] as $key => $val)
                            <label class="has-checked:border-green-600 has-checked:bg-green-50 dark:has-checked:bg-green-950/40 flex items-center p-3.5 rounded-lg border border-gray-200 dark:border-gray-700 hover:bg-green-50 dark:hover:bg-gray-700/50 cursor-pointer transition-colors group">
                                <input type="radio" name="answers[{{ $q->id }}]" value="{{ $key }}" @checked(old('answers.'.$q->id) === $key) required class="w-4 h-4 text-brand-dark focus:ring-brand-light">
                                <span class="ml-3 text-sm text-gray-800 dark:text-gray-200 group-hover:text-brand-dark dark:group-hover:text-green-300 font-medium">
                                    <strong class="uppercase mr-1">{{ $key }}.</strong> {{ $val }}
                                </span>
                            </label>
                        @endforeach
                    </div>
                </div>
            </x-card>
        @endforeach

        <div class="flex flex-wrap items-center justify-between gap-4 border-t border-gray-200 pt-4 dark:border-gray-700"><p class="text-sm text-gray-500" aria-live="polite"><span x-text="answered"></span> / {{ $quiz->questions->count() }} soal dijawab</p>
            <x-button type="submit" variant="primary" size="lg" ::disabled="submitting || total === 0" class="w-full sm:w-auto shadow-none shadow-green-900/30">
                <span x-text="submitting ? 'Mengirim jawaban…' : 'Kirim jawaban'"></span>
            </x-button>
        </div>
    </form>
</div>


@endsection
