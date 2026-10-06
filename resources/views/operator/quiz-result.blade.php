@extends('layouts.app')

@section('title', 'Hasil Assessment - Pojok Ilmu MWT')

@section('content')


<div class="page-shell !max-w-3xl">
    <x-card class="shadow-none border border-gray-100 dark:border-gray-700 rounded-lg text-center" body-class="space-y-6 p-6 sm:p-8">
        
        <!-- Status Icon Banner -->
        <div class="pt-4">
            @if($isPassed)
                <div class="w-24 h-24 mx-auto rounded-full bg-emerald-100 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shadow-none ">
                    <svg class="w-14 h-14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <h1 class="text-3xl font-semibold font-heading text-emerald-600 dark:text-emerald-400 mt-4">Anda lulus evaluasi</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400">Kompetensi Anda pada Skill Matrix telah resmi diperbarui oleh sistem.</p>
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
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">SKOR AKHIR ASSESSMENT</p>
            <div class="text-5xl font-semibold font-heading {{ $isPassed ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400' }}">
                {{ number_format($score, 0) }}<span class="text-2xl">%</span>
            </div>
            <p class="text-xs text-gray-500">Standar Minimum Kelulusan: <strong>{{ $passingGrade }}%</strong></p>
        </div>

        <!-- 4-Block Level Status Summary -->
        <div class="p-4 rounded-lg bg-blue-50 dark:bg-blue-950/30 border border-blue-200 dark:border-blue-900 text-left space-y-2 text-xs text-blue-900 dark:text-blue-200">
            <div class="flex items-center justify-between font-bold">
                <span>Status Skill Matrix Operator:</span>
                <span>{{ $quiz->training->skill?->nama_skill ?? 'Kompetensi diarsipkan' }}</span>
            </div>
            <p>Tingkat Kelulusan Pelatihan: <strong>Level {{ $quiz->training->level }}</strong></p>
            @if($isPassed)
                <p class="text-emerald-700 dark:text-emerald-400 font-semibold">Kelulusan Level {{ $quiz->training->level }} telah tercatat pada matriks kompetensi Anda.</p>
            @else
                <p class="text-amber-700 dark:text-amber-400">Percobaan ke-{{ $enrollment->current_retry_count }} dari batas maksimal {{ $quiz->max_retries }} kali.</p>
            @endif
        </div>

        <!-- Escalation Message if retry limit reached -->
        @if(!$isPassed && $enrollment->status === 'failed_escalated')
            <div class="p-4 rounded-lg bg-amber-100 dark:bg-amber-950/50 border border-amber-300 dark:border-amber-800 text-amber-900 dark:text-amber-200 text-xs text-left space-y-1">
                <strong>Status Eskalasi:</strong>
                <p>Anda telah mencapai batas maksimal {{ $quiz->max_retries }} kali pengulangan. Hubungi HR atau atasan untuk pembinaan dan pembukaan kembali akses ujian.</p>
            </div>
        @endif

        <!-- Action Buttons -->
        <div class="pt-4 flex flex-col sm:flex-row items-center justify-center gap-3">
            <x-button :href="route('operator.dashboard')" variant="outline">Kembali ke pelatihan</x-button>

            @if(!$isPassed && $enrollment->status !== 'failed_escalated')
                <x-button :href="route('operator.materials.show', $quiz->training->id)">Pelajari kembali materi <x-icon name="arrow" /></x-button>
            @endif
        </div>
    </x-card>
</div>
@endsection
