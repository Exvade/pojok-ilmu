@extends('layouts.app')

@section('title', 'Hasil OJT · Pojok Ilmu')

@section('content')
    @php($ojt = $enrollment->ojtAssessment)
    <div class="page-shell !max-w-4xl">
        <x-button :href="route('operator.profile')" variant="outline"><x-icon name="back" class="h-4 w-4" />Kembali ke profil</x-button>
        <div class="mt-5 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div><p class="text-xs font-semibold uppercase tracking-wider text-brand-dark dark:text-green-400">Hasil OJT</p><h1 class="page-title">{{ $enrollment->training->judul }}</h1><p class="page-description">Hasil penilaian praktik oleh Leader.</p></div>
            <div class="flex flex-wrap items-center gap-2">
                <x-button :href="route('operator.ojt-result.pdf', $enrollment)" data-no-loading variant="outline" size="sm"><x-icon name="download" class="h-4 w-4" />Unduh PDF</x-button>
                <x-competency-blocks :value="$ojt->is_passed ? 2 : 1" />
            </div>
        </div>

        <div class="grid gap-4 sm:grid-cols-3">
            <x-card><p class="text-xs text-gray-500">Nilai teori</p><p class="mt-2 text-3xl font-semibold">{{ $ojt->theory_score }}%</p></x-card>
            <x-card><p class="text-xs text-gray-500">Nilai praktik</p><p class="mt-2 text-3xl font-semibold">{{ $ojt->practice_score }}%</p></x-card>
            <x-card><p class="text-xs text-gray-500">Penilai</p><p class="mt-2 font-semibold">{{ $ojt->assessor?->name ?? '-' }}</p><p class="mt-1 text-xs text-gray-500">{{ $ojt->training_date?->translatedFormat('d M Y') }}</p></x-card>
        </div>

        <x-card>
            <x-slot name="header"><h2 class="font-heading text-lg font-bold">Catatan Leader</h2></x-slot>
            <div class="grid gap-5 lg:grid-cols-2"><div><p class="text-sm font-semibold">Kesimpulan</p><p class="mt-2 whitespace-pre-line text-sm leading-6 text-gray-600 dark:text-gray-300">{{ $ojt->conclusion ?: 'Belum ada catatan.' }}</p></div><div><p class="text-sm font-semibold">Saran untuk Anda</p><p class="mt-2 whitespace-pre-line text-sm leading-6 text-gray-600 dark:text-gray-300">{{ $ojt->recommendation ?: 'Belum ada saran tambahan.' }}</p></div></div>
        </x-card>
    </div>
@endsection
