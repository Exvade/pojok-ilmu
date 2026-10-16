@extends('layouts.app')
@section('title', 'Pelatihan saya · Pojok Ilmu')
@section('content')
<template hidden>@verbatim
<div class="page-shell">
    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-start">
        <div><p class="mb-2 text-xs font-semibold uppercase tracking-wider text-brand-dark dark:text-green-400">Ruang belajar karyawan</p><h1 class="page-title">Selamat datang, {{ $manpower?->nama ?? auth()->user()->name }}</h1><p class="page-description">Pelajari materi yang tersedia dan pantau level kompetensi yang sudah dicapai.</p></div>
        <span class="self-start rounded-md border border-gray-200 bg-white px-3 py-2 font-mono text-xs text-gray-500 dark:border-gray-700 dark:bg-gray-800">{{ $manpower?->mp_id ?? auth()->user()->user_id }}</span>
    </div>
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
        @foreach([['Jalur dipelajari', $learningPaths->where('started', true)->count()], ['Sub-course selesai', $enrollments->where('status', 'passed')->count()], ['Sub-course aktif', $enrollments->where('status', '!=', 'passed')->count()]] as [$label, $value])
            <x-card><p class="text-xs font-medium text-gray-500">{{ $label }}</p><p class="mt-3 text-3xl font-semibold tracking-tight">{{ $value }}</p></x-card>
        @endforeach
    </div>
    <div class="rounded-lg border border-green-200 bg-green-50 px-5 py-4 dark:border-green-900 dark:bg-green-950/30">
        <p class="text-sm font-semibold text-brand-dark dark:text-green-300">{{ $manpower?->departemen ?? 'Departemen belum tersedia' }}</p>
        <p class="mt-1 text-xs text-gray-600 dark:text-gray-400">{{ $manpower?->bagian ?? 'Jabatan belum tersedia' }} · Pembelajaran bersifat mandiri sesuai program yang tersedia.</p>
    </div>
    <section aria-labelledby="learning-path-heading">
        <div class="mb-4 flex items-center justify-between gap-3"><div><h2 id="learning-path-heading" class="text-base font-semibold">Leveling {{ $manpower?->departemen ?? 'departemen saya' }}</h2><p class="mt-1 text-xs text-gray-500">Pilih salah satu jalur di departemen Anda, selesaikan seluruh sub-course pada level aktif, lalu level berikutnya terbuka.</p></div><span class="text-xs text-gray-500">{{ $learningPaths->count() }} jalur</span></div>
        <div class="grid grid-cols-1 gap-4 xl:grid-cols-2">
            @forelse($learningPaths as $path)
                @php($framework = $path['framework'])
                <x-card>
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div><p class="font-mono text-xs font-semibold text-brand-dark dark:text-green-400">{{ $framework->kode }}</p><h3 class="mt-1 text-lg font-semibold">{{ $framework->nama }}</h3><p class="mt-2 text-sm text-gray-500">{{ $framework->deskripsi ?: 'Jalur pembelajaran kompetensi operator.' }}</p></div>
                        <span class="rounded-md px-2 py-1 text-xs font-semibold {{ $path['started'] ? 'bg-green-100 text-brand-dark dark:bg-green-950 dark:text-green-300' : 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300' }}">{{ $path['started'] ? 'Level ' . $path['achieved_level'] . ' tercapai' : 'Belum dimulai' }}</span>
                    </div>
                    <div class="mt-5 grid gap-2 sm:grid-cols-2">
                        @foreach($path['levels'] as $level)
                            <div class="rounded-md border p-3 {{ $level['is_complete'] ? 'border-green-200 bg-green-50 dark:border-green-900 dark:bg-green-950/30' : ($level['is_unlocked'] ? 'border-brand-light/50 bg-white dark:bg-gray-800' : 'border-gray-200 bg-gray-50 opacity-70 dark:border-gray-700 dark:bg-gray-900') }}">
                                <div class="flex items-center justify-between gap-2"><span class="text-xs font-semibold {{ $level['is_complete'] ? 'text-brand-dark dark:text-green-300' : 'text-gray-700 dark:text-gray-200' }}">Level {{ $level['number'] }} · {{ $level['category'] }}</span><span class="text-xs font-semibold">{{ $level['is_complete'] ? 'Tercapai' : ($level['is_unlocked'] ? $level['progress_percent'] . '%' : 'Terkunci') }}</span></div>
                                <p class="mt-2 text-xs text-gray-500">@if(! $level['is_configured']) Belum ada sub-course. @elseif($level['is_unlocked']) {{ $level['passed_count'] }} dari {{ $level['total'] }} sub-course selesai @else Selesaikan level sebelumnya. @endif</p>
                                <div class="mt-3 h-1.5 overflow-hidden rounded-sm bg-gray-200 dark:bg-gray-700"><div class="h-full rounded-sm {{ $level['is_complete'] ? 'bg-brand-dark dark:bg-green-500' : 'bg-brand-light' }}" style="width: {{ $level['is_unlocked'] ? $level['progress_percent'] : 0 }}%"></div></div>
                            </div>
                        @endforeach
                    </div>
                    <x-slot name="footer">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            @if(! $path['started'])
                                @if($path['levels']->contains('is_configured', true))
                                    <span class="text-xs text-gray-500">Mulai dari seluruh sub-course Level 1.</span>
                                    <form action="{{ route('operator.learning-paths.start', $framework->id) }}" method="POST">@csrf<x-button type="submit" size="sm">Mulai jalur belajar <x-icon name="arrow" class="h-4 w-4" /></x-button></form>
                                @else
                                    <span class="text-xs text-amber-700 dark:text-amber-300">Kurikulum belum disusun oleh pengelola.</span>
                                @endif
                            @elseif($path['next_enrollment'])
                                <span class="text-xs text-gray-500">Lanjutkan Level {{ $path['current_level']['number'] }}.</span>
                                <x-button :href="route('operator.materials.show', $path['next_enrollment']->training_id)" size="sm">Lanjutkan belajar <x-icon name="arrow" class="h-4 w-4" /></x-button>
                            @else
                                <span class="text-xs font-semibold text-brand-dark dark:text-green-300">Seluruh level yang tersedia telah tercapai.</span>
                            @endif
                        </div>
                    </x-slot>
                </x-card>
            @empty
                <div class="col-span-full"><x-empty-state title="Belum ada jalur leveling" description="Pengelola belum menyiapkan jalur untuk departemen Anda." /></div>
            @endforelse
        </div>
    </section>
    <section aria-labelledby="general-module-heading">
        <div class="mb-4 flex items-center justify-between gap-3"><div><h2 id="general-module-heading" class="text-base font-semibold">Modul umum</h2><p class="mt-1 text-xs text-gray-500">Pembelajaran lintas departemen. Penyelesaian modul umum tidak mengubah level pada jalur kompetensi.</p></div><span class="text-xs text-gray-500">{{ $generalModules->count() }} modul</span></div>
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
            @forelse($generalModules as $module)
                @php($moduleEnrollment = $generalEnrollments->get($module->id))
                <x-card>
                    <div class="flex items-start justify-between gap-3"><div><p class="font-mono text-xs font-semibold text-brand-dark dark:text-green-400">{{ $module->kode_training }}</p><h3 class="mt-2 text-base font-semibold">{{ $module->judul }}</h3></div><span class="rounded-md bg-gray-100 px-2 py-1 text-xs font-semibold text-gray-600 dark:bg-gray-700 dark:text-gray-300">Modul umum</span></div>
                    <p class="mt-3 text-sm text-gray-500">{{ $module->deskripsi ?: ($module->skill?->deskripsi ?? 'Materi pembelajaran lintas departemen.') }}</p>
                    <x-slot name="footer"><div class="flex items-center justify-between gap-3"><span class="text-xs {{ $moduleEnrollment?->status === 'passed' ? 'text-brand-dark dark:text-green-300' : 'text-gray-500' }}">{{ $moduleEnrollment?->status === 'passed' ? 'Selesai' : ($moduleEnrollment ? 'Sedang dipelajari' : 'Belum dimulai') }}</span>@if($moduleEnrollment)<x-button :href="route('operator.materials.show', $module->id)" size="sm">{{ $moduleEnrollment->status === 'passed' ? 'Tinjau' : 'Lanjutkan' }} <x-icon name="arrow" class="h-4 w-4" /></x-button>@else<form action="{{ route('operator.general-modules.start', $module->id) }}" method="POST">@csrf<x-button type="submit" size="sm">Mulai modul <x-icon name="arrow" class="h-4 w-4" /></x-button></form>@endif</div></x-slot>
                </x-card>
            @empty
                <div class="col-span-full"><x-empty-state title="Belum ada modul umum" description="Pengelola belum menyiapkan modul lintas departemen." /></div>
            @endforelse
        </div>
    </section>
    <section aria-labelledby="training-heading">
        <div class="mb-4 flex items-center justify-between gap-3"><h2 id="training-heading" class="text-base font-semibold">Sub-course saya</h2><span class="text-xs text-gray-500">{{ $enrollments->count() }} sub-course</span></div>
        <div class="grid grid-cols-1 gap-4 xl:grid-cols-2">
            @forelse($enrollments as $en)
                <x-card>
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <span class="text-xs font-medium text-gray-500">{{ $en->training->kode_training }} · Level {{ $en->training->level }}</span>
                        <span class="rounded-md px-2 py-1 text-xs font-semibold {{ $en->status === 'passed' ? 'bg-green-50 text-green-700 dark:bg-green-950 dark:text-green-300' : ($en->status === 'failed_escalated' ? 'bg-red-50 text-red-700 dark:bg-red-950 dark:text-red-300' : 'bg-blue-50 text-blue-700 dark:bg-blue-950 dark:text-blue-300') }}">{{ ['passed' => 'Selesai', 'failed_escalated' => 'Perlu tindak lanjut', 'in_progress' => 'Sedang dipelajari', 'enrolled' => 'Belum dimulai'][$en->status] ?? 'Tersedia' }}</span>
                    </div>
                    <h3 class="mt-4 text-lg font-semibold">{{ $en->training->judul }}</h3>
                    <p class="mt-2 text-sm text-gray-500">{{ $en->training->skill?->nama_skill ?? 'Kompetensi tidak tersedia' }}</p>
                    <x-slot name="footer">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div class="text-xs leading-5 text-gray-500">Nilai kelulusan {{ $en->training->passing_grade }}%<br>Percobaan gagal {{ $en->current_retry_count }} / {{ $en->training->postTest?->max_retries ?? $en->training->max_retries }}</div>
                            @if($en->status === 'failed_escalated')
                                <span class="text-xs text-red-600 dark:text-red-400">Hubungi HR / atasan</span>
                            @elseif($en->training->preTest && !$en->pre_test_completed_at)
                                <x-button :href="route('operator.quizzes.show', $en->training->preTest->id)" size="sm">Mulai pre-test<x-icon name="arrow" class="h-4 w-4" /></x-button>
                            @elseif($en->next_attempt_at?->isFuture())
                                <span class="text-xs text-amber-700 dark:text-amber-300">Tersedia kembali {{ $en->next_attempt_at->translatedFormat('d M, H:i') }}</span>
                            @else
                                <x-button :href="route('operator.materials.show', $en->training->id)" size="sm">{{ $en->status === 'passed' ? 'Tinjau materi' : 'Buka materi' }}<x-icon name="arrow" class="h-4 w-4" /></x-button>
                            @endif
                        </div>
                    </x-slot>
                </x-card>
            @empty
                <x-card class="xl:col-span-2"><x-empty-state title="Belum ada sub-course aktif" description="Mulai salah satu jalur pembelajaran untuk membuka sub-course Level 1." /></x-card>
            @endforelse
        </div>
    </section>
</div>
@endverbatim</template>

<div class="page-shell">
    @php
        $welcomeTrainings = $enrollments
            ->filter(fn ($enrollment): bool => $enrollment->is_unlocked && $enrollment->block < 2)
            ->pluck('training.judul')
            ->take(3)
            ->values();
        $remainingWelcomeTrainings = max(0, $enrollments
            ->filter(fn ($enrollment): bool => $enrollment->is_unlocked && $enrollment->block < 2)
            ->count() - $welcomeTrainings->count());
        $welcomeName = $manpower?->nama ?? auth()->user()->name;
        $welcomeSpeech = $welcomeTrainings->isEmpty()
            ? "Selamat datang, {$welcomeName}. Saat ini tidak ada training aktif yang perlu Anda mulai."
            : "Selamat datang, {$welcomeName}. Berdasarkan sistem, Anda memiliki " . $welcomeTrainings->count() . ' training yang perlu dilanjutkan: ' . $welcomeTrainings->implode(', ') . ($remainingWelcomeTrainings > 0 ? ", dan {$remainingWelcomeTrainings} training lainnya." : '.');
    @endphp

    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <p class="text-xs font-semibold uppercase tracking-wider text-brand-dark dark:text-green-400">Training saya</p>
            <h1 class="page-title">Halo, {{ $manpower?->nama ?? auth()->user()->name }}</h1>
            <p class="page-description">Berikut adalah training yang ditugaskan kepada Anda. Selesaikan training sampai Balok 2 agar tercatat kompeten pada training tersebut.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2"><x-button :href="route('operator.profile')" variant="outline" size="sm"><x-icon name="user" class="h-4 w-4" />Profil saya</x-button><div class="rounded-md border border-gray-200 bg-white px-3 py-2 text-xs dark:border-gray-700 dark:bg-gray-800"><p class="font-semibold">{{ $manpower?->departemen ?? 'Departemen belum tersedia' }}</p><p class="mt-1 text-gray-500">{{ $manpower?->bagian ?? 'Jabatan belum tersedia' }}</p></div></div>
    </div>

        <div x-data="welcomeSpeech($el.dataset.message, $el.dataset.shouldPlay === 'true')" data-message="{{ $welcomeSpeech }}" data-should-play="{{ session('play_welcome_speech') ? 'true' : 'false' }}" aria-live="polite">
            <x-card bodyClass="p-4 sm:p-5">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex min-w-0 items-start gap-3">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-md bg-green-50 text-brand-dark dark:bg-green-950 dark:text-green-300"><x-icon name="speaker" class="h-5 w-5" /></span>
                <div>
                    <p class="text-sm font-semibold">Suara sambutan</p>
                    <p class="mt-1 text-xs leading-5 text-gray-500" x-text="message"></p>
                    <p x-show="! supported" x-cloak class="mt-1 text-xs text-amber-700 dark:text-amber-300">Browser ini belum mendukung suara sambutan.</p>
                    <p x-show="autoplayBlocked" x-cloak class="mt-1 text-xs text-amber-700 dark:text-amber-300">Tekan “Dengarkan sambutan” untuk mengaktifkan suara pada browser ini.</p>
                </div>
            </div>
            <div class="flex shrink-0 flex-wrap gap-2" x-show="supported" x-cloak>
                <x-button type="button" variant="secondary" size="sm" @click="replay()" x-bind:disabled="speaking"><x-icon name="speaker" class="h-4 w-4" /><span x-text="speaking ? 'Sedang diputar' : 'Dengarkan sambutan'"></span></x-button>
                <x-button type="button" variant="outline" size="sm" x-show="enabled" @click="disable()"><x-icon name="speaker-off" class="h-4 w-4" />Matikan suara</x-button>
                <x-button type="button" variant="outline" size="sm" x-show="! enabled" x-cloak @click="enable()"><x-icon name="speaker" class="h-4 w-4" />Aktifkan suara</x-button>
            </div>
                </div>
            </x-card>
        </div>

    <div class="grid gap-4 sm:grid-cols-3">
        <x-card><p class="text-xs font-medium text-gray-500">Level saat ini</p><p class="mt-3 text-3xl font-semibold">{{ $currentLevel ? 'Level '.$currentLevel : 'Belum ada' }}</p><p class="mt-1 text-xs text-gray-500">Level lengkap bila semua training pada level tersebut Balok 2.</p></x-card>
        <x-card><p class="text-xs font-medium text-gray-500">Training Balok 2</p><p class="mt-3 text-3xl font-semibold">{{ $enrollments->where('block', 2)->count() }}</p><p class="mt-1 text-xs text-gray-500">Mampu mengerjakan dengan bimbingan.</p></x-card>
        <x-card><p class="text-xs font-medium text-gray-500">Menunggu OJT</p><p class="mt-3 text-3xl font-semibold">{{ $enrollments->where('block', 1)->count() }}</p><p class="mt-1 text-xs text-gray-500">Post-test lulus dan menunggu penilaian praktik.</p></x-card>
    </div>

    <x-card bodyClass="p-5">
        <div class="flex flex-col gap-4 md:flex-row md:items-center">
            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-md bg-brand-dark text-sm font-bold text-white">1</div><div><p class="text-sm font-semibold">Balok 1: lulus post-test</p><p class="mt-1 text-xs text-gray-500">Selesaikan pre-test, materi, lalu post-test.</p></div>
            <div class="hidden h-8 w-px bg-gray-200 md:block dark:bg-gray-700"></div>
            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-md bg-brand-dark text-sm font-bold text-white">2</div><div><p class="text-sm font-semibold">Balok 2: mampu mengerjakan dengan bimbingan</p><p class="mt-1 text-xs text-gray-500">Capaian ini dibuktikan setelah OJT dinyatakan lulus oleh Leader.</p></div>
        </div>
    </x-card>

    <section>
        <div class="mb-4"><h2 class="text-lg font-semibold">Training yang ditugaskan</h2><p class="mt-1 text-xs text-gray-500">Training Level 2 dan seterusnya terbuka setelah seluruh training tugas pada level sebelumnya mencapai Balok 2.</p></div>
        <div class="grid gap-4 xl:grid-cols-2">
            @forelse($enrollments as $enrollment)
                @php($blockingTrainingTitles = $enrollment->blocking_training_titles ?? [])
                <x-card class="{{ $enrollment->is_unlocked ? '' : 'opacity-70' }}">
                    <div class="flex items-start justify-between gap-3">
                        <div><p class="font-mono text-xs font-semibold text-brand-dark dark:text-green-400">{{ $enrollment->training->kode_training }}</p><h3 class="mt-1 text-lg font-semibold">{{ $enrollment->training->judul }}</h3><p class="mt-2 text-xs text-gray-500">{{ $enrollment->training->departemen }} · Training Level {{ $enrollment->training->level }}</p></div>
                        <x-competency-blocks :value="$enrollment->block" :show-label="false" />
                    </div>
                    <p class="mt-4 text-sm text-gray-600 dark:text-gray-300">{{ $enrollment->block_label }}</p>
                    <x-competency-blocks :value="$enrollment->block" class="mt-4" />
                    <x-slot name="footer">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            @if(! $enrollment->is_unlocked)
                                <span class="text-xs leading-5 text-gray-500">Terkunci. Selesaikan Balok 2 untuk: {{ implode(', ', $blockingTrainingTitles) }}.</span>
                            @elseif($enrollment->block === 2)
                                <span class="text-xs font-semibold text-brand-dark dark:text-green-300">Anda telah mencapai Balok 2 pada training ini.</span>
                                <x-button :href="route('operator.materials.show', $enrollment->training_id)" variant="outline" size="sm"><x-icon name="eye" class="h-4 w-4" />Lihat training</x-button>
                            @elseif($enrollment->block === 1)
                                @if($enrollment->ojtAssessment)
                                    <span class="text-xs text-amber-800 dark:text-amber-200">Hasil OJT sudah tersedia.</span>
                                    <x-button :href="route('operator.ojt-result', $enrollment->id)" variant="outline" size="sm"><x-icon name="eye" class="h-4 w-4" />Lihat hasil OJT</x-button>
                                @else
                                    <span class="text-xs text-amber-800 dark:text-amber-200">Post-test lulus. Menunggu OJT dari penilai.</span>
                                @endif
                            @elseif($enrollment->training->preTest && ! $enrollment->pre_test_completed_at)
                                <x-button :href="route('operator.quizzes.show', $enrollment->training->preTest->id)" size="sm">Mulai pre-test <x-icon name="arrow" class="h-4 w-4" /></x-button>
                            @elseif($enrollment->next_attempt_at?->isFuture())
                                <span class="text-xs text-amber-800 dark:text-amber-200">Bisa mencoba lagi {{ $enrollment->next_attempt_at->translatedFormat('d M, H:i') }}</span>
                            @else
                                <x-button :href="route('operator.materials.show', $enrollment->training_id)" size="sm">Buka training <x-icon name="arrow" class="h-4 w-4" /></x-button>
                            @endif
                        </div>
                    </x-slot>
                </x-card>
            @empty
                <div class="xl:col-span-2"><x-empty-state title="Belum ada training yang ditugaskan" description="Pengelola belum menugaskan training untuk Anda. Hubungi atasan atau pengelola training bila diperlukan." /></div>
            @endforelse
        </div>
    </section>
</div>
@endsection
