@extends('layouts.app')

@section('title', 'Hasil Assessment - Pojok Ilmu MWT')

@section('content')
@include('layouts.navigation')

<div class="max-w-2xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
    <x-card class="shadow-2xl border border-gray-100 dark:border-gray-700 rounded-3xl text-center space-y-6">
        
        <!-- Status Icon Banner -->
        <div class="pt-4">
            @if($isPassed)
                <div class="w-24 h-24 mx-auto rounded-full bg-emerald-100 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shadow-lg animate-bounce">
                    <svg class="w-14 h-14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <h1 class="text-3xl font-extrabold font-heading text-emerald-600 dark:text-emerald-400 mt-4">SELAMAT! ANDA LULUS</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400">Kompetensi Anda pada Skill Matrix telah resmi diperbarui oleh sistem.</p>
            @else
                <div class="w-24 h-24 mx-auto rounded-full bg-red-100 dark:bg-red-950/60 text-red-600 dark:text-red-400 flex items-center justify-center shadow-lg">
                    <svg class="w-14 h-14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <h1 class="text-3xl font-extrabold font-heading text-red-600 dark:text-red-400 mt-4">BELUM MENCAPAI TARGET</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400">Nilai Anda belum memenuhi passing grade standar kelulusan.</p>
            @endif
        </div>

        <!-- Score Box -->
        <div class="p-6 rounded-2xl bg-gray-50 dark:bg-gray-800/60 border border-gray-200 dark:border-gray-700 max-w-sm mx-auto space-y-2">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">SKOR AKHIR ASSESSMENT</p>
            <div class="text-5xl font-extrabold font-heading {{ $isPassed ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400' }}">
                {{ number_format($score, 0) }}<span class="text-2xl">%</span>
            </div>
            <p class="text-xs text-gray-500">Standar Minimum Kelulusan: <strong>{{ $passingGrade }}%</strong></p>
        </div>

        <!-- 4-Block Level Status Summary -->
        <div class="p-4 rounded-2xl bg-blue-50 dark:bg-blue-950/30 border border-blue-200 dark:border-blue-900 text-left space-y-2 text-xs text-blue-900 dark:text-blue-200">
            <div class="flex items-center justify-between font-bold">
                <span>Status Skill Matrix Operator:</span>
                <span>{{ $quiz->training->skill->nama_skill }}</span>
            </div>
            <p>Tingkat Kelulusan Pelatihan: <strong>Level {{ $quiz->training->level }}</strong></p>
            @if($isPassed)
                <p class="text-emerald-700 dark:text-emerald-400 font-semibold">&check; Skill Matrix Anda telah naik ke Level {{ $quiz->training->level }} dan tercatat di database.</p>
            @else
                <p class="text-amber-700 dark:text-amber-400">Percobaan ke-{{ $enrollment->current_retry_count }} dari batas maksimal {{ $quiz->max_retries }} kali.</p>
            @endif
        </div>

        <!-- Escalation Message if retry limit reached -->
        @if(!$isPassed && $enrollment->status === 'failed_escalated')
            <div class="p-4 rounded-2xl bg-amber-100 dark:bg-amber-950/50 border border-amber-300 dark:border-amber-800 text-amber-900 dark:text-amber-200 text-xs text-left space-y-1">
                <strong>Status Eskalasi:</strong>
                <p>Anda telah mencapai batas maksimal {{ $quiz->max_retries }} kali pengulangan. Notifikasi telah dikirimkan ke HR & Atasan untuk penanganan/review lebih lanjut.</p>
            </div>
        @endif

        <!-- Action Buttons -->
        <div class="pt-4 flex flex-col sm:flex-row items-center justify-center gap-3">
            <a href="{{ route('operator.dashboard') }}" class="w-full sm:w-auto px-6 py-3 rounded-xl font-bold text-sm bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 hover:bg-gray-300 transition-all">
                Kembali ke Dashboard Saya
            </a>

            @if(!$isPassed && $enrollment->status !== 'failed_escalated')
                <a href="{{ route('operator.materials.show', $quiz->training->id) }}" class="w-full sm:w-auto px-6 py-3 rounded-xl font-bold text-sm bg-brand-dark text-white hover:bg-green-900 shadow-lg shadow-green-900/20 transition-all">
                    Ulangi Materi & Kuis Remedi &rarr;
                </a>
            @endif
        </div>
    </x-card>
</div>
@endsection
