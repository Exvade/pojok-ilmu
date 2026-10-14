@extends('layouts.app')

@section('title', 'Profil Training ' . $manpower->nama . ' · Pojok Ilmu MWT')

@section('content')
    <div class="page-shell">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center gap-1 text-xs font-semibold text-gray-500 hover:text-brand-dark dark:hover:text-green-300"><x-icon name="back" class="h-3.5 w-3.5" />Kembali ke matriks</a>
                <p class="mt-4 text-xs font-semibold uppercase tracking-wider text-brand-dark dark:text-green-400">Profil kompetensi</p>
                <h1 class="page-title mt-1">{{ $manpower->nama }}</h1>
                <p class="page-description">NPK: {{ $manpower->mp_id }} · {{ $manpower->departemen }} · {{ $manpower->jabatan ?? $manpower->bagian }}</p>
            </div>
            <span class="inline-flex w-fit items-center rounded-full border border-green-200 bg-green-50 px-3 py-1.5 text-xs font-semibold text-brand-dark dark:border-green-900 dark:bg-green-950/40 dark:text-green-300">Level saat ini: {{ $currentLevel ? 'Level '.$currentLevel : 'Belum tercapai' }}</span>
        </div>

        <div class="grid gap-4 sm:grid-cols-3">
            <x-card><p class="text-xs font-semibold uppercase tracking-wider text-gray-500">Training ditugaskan</p><p class="mt-2 text-3xl font-semibold">{{ $enrollments->count() }}</p><p class="mt-1 text-xs text-gray-500">Seluruh riwayat training manpower.</p></x-card>
            <x-card><p class="text-xs font-semibold uppercase tracking-wider text-gray-500">Balok 1</p><p class="mt-2 text-3xl font-semibold text-amber-600">{{ $totalBalokSatu }}</p><p class="mt-1 text-xs text-gray-500">Lulus post-test, menunggu OJT.</p></x-card>
            <x-card><p class="text-xs font-semibold uppercase tracking-wider text-gray-500">Balok 2</p><p class="mt-2 text-3xl font-semibold text-brand-dark">{{ $totalBalokDua }}</p><p class="mt-1 text-xs text-gray-500">OJT telah disetujui Leader.</p></x-card>
        </div>

        <x-card body-class="p-0">
            <x-slot name="header"><div><h2 class="font-heading text-lg font-bold">Riwayat training</h2><p class="mt-1 text-xs text-gray-500">Capaian setiap training yang ditugaskan kepada manpower ini.</p></div></x-slot>
            @if($enrollments->isEmpty())
                <x-empty-state title="Belum ada training" description="Manpower ini belum menerima penugasan training." />
            @else
                <div class="overflow-x-auto">
                    <x-table>
                        <x-slot name="head"><tr><th>Training</th><th>Post-test</th><th>OJT</th><th>Balok</th></tr></x-slot>
                        @foreach($enrollments as $enrollment)
                            @php($postTestAttempt = $enrollment->attempts->first(fn ($attempt) => $attempt->quiz?->tipe === 'post_test'))
                            @php($ojt = $enrollment->ojtAssessment)
                            <tr>
                                <td><p class="font-semibold text-gray-900 dark:text-white">{{ $enrollment->training?->judul ?? 'Training diarsipkan' }}</p><p class="mt-1 text-xs text-gray-500">{{ $enrollment->training?->kode_training }} · Level {{ $enrollment->training?->level }}</p></td>
                                <td class="text-sm">@if($enrollment->post_test_completed_at)<p class="font-semibold text-brand-dark dark:text-green-300">Lulus · {{ $postTestAttempt?->score ?? '-' }}%</p><p class="mt-1 text-xs text-gray-500">{{ $enrollment->post_test_completed_at->translatedFormat('d M Y, H:i') }}</p>@else<span class="text-gray-400">Belum lulus</span>@endif</td>
                                <td class="text-sm">@if($enrollment->ojt_status === 'approved')<p class="font-semibold text-brand-dark dark:text-green-300">Disetujui</p><p class="mt-1 text-xs text-gray-500">{{ $ojt?->theory_score ?? '-' }}% teori · {{ $ojt?->practice_score ?? '-' }}% praktik</p>@elseif($enrollment->ojt_status === 'pending')<span class="inline-flex rounded-full bg-amber-100 px-2.5 py-1 text-xs font-semibold text-amber-800 dark:bg-amber-950 dark:text-amber-200">Menunggu OJT</span>@elseif($enrollment->ojt_status === 'rejected')<span class="inline-flex rounded-full bg-red-100 px-2.5 py-1 text-xs font-semibold text-red-700 dark:bg-red-950 dark:text-red-300">Perlu OJT ulang</span>@else<span class="text-gray-400">Belum masuk OJT</span>@endif</td>
                                <td><x-competency-blocks :value="$enrollment->block" /></td>
                            </tr>
                        @endforeach
                    </x-table>
                </div>
            @endif
        </x-card>
    </div>
@endsection
