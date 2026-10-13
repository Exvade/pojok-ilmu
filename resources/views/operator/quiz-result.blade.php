@extends('layouts.app')

@section('title', 'Hasil Evaluasi - Pojok Ilmu MWT')

@section('content')


<div class="page-shell !max-w-3xl">
    <x-card class="shadow-none border border-gray-100 dark:border-gray-700 rounded-lg text-center" body-class="space-y-6 p-6 sm:p-8">
        
        <!-- Status Icon Banner -->
        <div class="pt-4">
            @if($quiz->tipe === 'pre_test')
                <div class="w-24 h-24 mx-auto rounded-full bg-blue-100 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 flex items-center justify-center shadow-none ">
                    <svg class="w-14 h-14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8l2 2 4-4m6 2a9 9 0 11-18 0 0 1 0 18 0z"></path></svg>
                </div>
                <h1 class="text-3xl font-semibold font-heading text-blue-600 dark:text-blue-400 mt-4">Pre-test selesai</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400">Hasil ini menjadi pemetaan awal dan tidak memengaruhi level kompetensi Anda.</p>
            @elseif($quiz->tipe === 'post_test')
                <div class="w-24 h-24 mx-auto rounded-full bg-blue-100 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 flex items-center justify-center shadow-none ">
                    <svg class="w-14 h-14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 0 1 0 18 0z"></path></svg>
                </div>
                <h1 class="text-3xl font-semibold font-heading {{ $isPassed ? 'text-blue-600 dark:text-blue-400' : 'text-red-600 dark:text-red-400' }} mt-4">{{ $isPassed ? 'Post-test selesai' : 'Post-test belum lulus' }}</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ $isPassed ? 'Balok 1 telah tercapai. Menunggu validasi OJT untuk Balok 2.' : 'Pelajari kembali materi lalu ulangi post-test.' }}</p>
            @else
                <div class="w-24 h-24 mx-auto rounded-full bg-red-100 dark:bg-red-950/60 text-red-600 dark:text-red-400 flex items-center justify-center shadow-none">
                    <svg class="w-14 h-14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <h1 class="text-3xl font-semibold font-heading text-red-600 dark:text-red-400 mt-4">Belum mencapai nilai kelulusan</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400">Nilai Anda belum memenuhi nilai kelulusan standar kelulusan.</p>
            @endif
        </div>

        <!-- Score Box -->
        <div class="p-6 rounded-lg bg-gray-50 dark:bg-gray-800/60 border border-gray-200 dark:border-gray-700 max-w-sm mx-auto space-y-2">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">{{ $quiz->tipe === 'pre_test' ? 'SKOR PRE-TEST' : 'SKOR POST-TEST' }}</p>
            <div class="text-5xl font-semibold font-heading {{ $isPassed ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400' }}">
                {{ number_format($score, 0) }}<span class="text-2xl">%</span>
            </div>
            <p class="text-xs text-gray-500">Standar Minimum Kelulusan: <strong>{{ $passingGrade }}%</strong></p>
        </div>

        <!-- Escalation Message if retry limit reached -->
        @if($quiz->tipe === 'post_test' && !$isPassed && $enrollment->status === 'failed_escalated')
            <div class="p-4 rounded-lg bg-amber-100 dark:bg-amber-950/50 border border-amber-300 dark:border-amber-800 text-amber-900 dark:text-amber-200 text-xs text-left space-y-1">
                <strong>Status Eskalasi:</strong>
                <p>Anda telah mencapai batas maksimal {{ $quiz->max_retries }} kali pengulangan. Hubungi HR atau atasan untuk pembinaan dan pembukaan kembali akses ujian.</p>
            </div>
        @endif

        <!-- Action Buttons -->
        <div class="pt-4 flex flex-col sm:flex-row items-center justify-center gap-3">
            <x-button :href="route('operator.dashboard')" variant="outline">Kembali ke pelatihan</x-button>

            @if($quiz->tipe === 'pre_test')
                <x-button :href="route('operator.materials.show', $quiz->training->id)">Lanjut ke materi <x-icon name="arrow" /></x-button>
            @elseif($quiz->tipe === 'post_test' && !$isPassed)
                <x-button :href="route('operator.materials.show', $quiz->training->id)">Pelajari kembali materi <x-icon name="arrow" /></x-button>
            @elseif(!$isPassed && $enrollment->status !== 'failed_escalated')
                <x-button :href="route('operator.materials.show', $quiz->training->id)">Pelajari kembali materi <x-icon name="arrow" /></x-button>
            @endif
        </div>
    </x-card>
</div>
@endsection
