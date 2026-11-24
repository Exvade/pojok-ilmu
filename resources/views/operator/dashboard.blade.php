@extends('layouts.app')
@section('title', 'Pelatihan saya · Pojok Ilmu')
@section('content')
<div class="page-shell">
    @php
        $availableWelcomeTrainings = $enrollments
            ->filter(fn ($enrollment): bool => $enrollment->is_unlocked && $enrollment->is_training_ready && $enrollment->block < 2);
        $welcomeTrainings = $availableWelcomeTrainings
            ->pluck('training.judul')
            ->take(3)
            ->values();
        $remainingWelcomeTrainings = $availableWelcomeTrainings->count() - $welcomeTrainings->count();
        $welcomeName = $manpower?->nama ?? auth()->user()->name;
        $welcomeSpeech = $welcomeTrainings->isEmpty()
            ? "Selamat datang, {$welcomeName}. Saat ini tidak ada training yang tersedia dan belum mencapai level kompetensi 2."
            : "Selamat datang, {$welcomeName}. Berdasarkan sistem, Anda memiliki " . $availableWelcomeTrainings->count() . ' training yang belum mencapai level kompetensi 2, yaitu ' . $welcomeTrainings->implode(', ') . ($remainingWelcomeTrainings > 0 ? ", dan {$remainingWelcomeTrainings} training lainnya." : '.');
    @endphp

    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <p class="text-xs font-semibold uppercase tracking-wider text-brand-dark dark:text-green-400">Training saya</p>
            <h1 class="page-title">Halo, {{ $manpower?->nama ?? auth()->user()->name }}</h1>
            <p class="page-description">Berikut adalah training yang ditugaskan kepada Anda. Selesaikan training sampai level kompetensi 2 agar tercatat kompeten pada training tersebut.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2"><x-button :href="route('operator.profile')" variant="outline" size="sm"><x-icon name="user" class="h-4 w-4" />Profil saya</x-button><x-button :href="route('operator.competency-summary.pdf')" data-no-loading variant="outline" size="sm"><x-icon name="download" class="h-4 w-4" />Unduh kompetensi (PDF)</x-button><div class="rounded-md border border-gray-200 bg-white px-3 py-2 text-xs dark:border-gray-700 dark:bg-gray-800"><p class="font-semibold">{{ $manpower?->departemen ?? 'Departemen belum tersedia' }}</p><p class="mt-1 text-gray-500">{{ $manpower?->bagian ?? 'Jabatan belum tersedia' }}</p></div></div>
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
        <x-card><p class="text-xs font-medium text-gray-500">Level Manpower</p><p class="mt-3 text-3xl font-semibold">{{ $currentLevel ? 'Level '.$currentLevel : 'Belum ada' }}</p><p class="mt-1 text-xs text-gray-500">Level lengkap bila semua training pada level tersebut level kompetensi 2.</p></x-card>
        <x-card><p class="text-xs font-medium text-gray-500">Training level kompetensi 2</p><p class="mt-3 text-3xl font-semibold">{{ $enrollments->where('block', 2)->count() }}</p><p class="mt-1 text-xs text-gray-500">Mampu mengerjakan dengan bimbingan.</p></x-card>
        <x-card><p class="text-xs font-medium text-gray-500">Menunggu OJT</p><p class="mt-3 text-3xl font-semibold">{{ $enrollments->where('block', 1)->count() }}</p><p class="mt-1 text-xs text-gray-500">Post-test lulus dan menunggu penilaian praktik.</p></x-card>
    </div>

    <x-card bodyClass="p-5">
        <div class="flex flex-col gap-4 md:flex-row md:items-center">
            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-md bg-brand-dark text-sm font-bold text-white">1</div><div><p class="text-sm font-semibold">Level kompetensi 1: lulus post-test</p><p class="mt-1 text-xs text-gray-500">Selesaikan pre-test, materi, lalu post-test.</p></div>
            <div class="hidden h-8 w-px bg-gray-200 md:block dark:bg-gray-700"></div>
            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-md bg-brand-dark text-sm font-bold text-white">2</div><div><p class="text-sm font-semibold">Level kompetensi 2: mampu mengerjakan dengan bimbingan</p><p class="mt-1 text-xs text-gray-500">Capaian ini dibuktikan setelah OJT dinyatakan lulus oleh Leader.</p></div>
        </div>
    </x-card>

    <section aria-labelledby="assigned-training-heading">
        <div class="mb-4"><h2 id="assigned-training-heading" class="text-lg font-semibold">Training yang ditugaskan</h2><p class="mt-1 text-xs text-gray-500">Training Level 2 dan seterusnya terbuka setelah seluruh training tugas pada level sebelumnya mencapai level kompetensi 2.</p></div>
        <div class="space-y-6">
            @forelse($levelRows as $level => $rows)
                <div data-training-level="{{ $level }}">
                    <h3 class="mb-2 text-sm font-semibold uppercase tracking-wider text-brand-dark dark:text-green-400">Level {{ $level }}</h3>
                    <div class="hidden md:block">
                        <x-table class="[&_table]:min-w-[56rem] [&_table]:table-fixed">
                            <x-slot name="thead">
                                <th scope="col" class="w-14">No</th>
                                <th scope="col">Nama part</th>
                                <th scope="col" class="w-36">No part</th>
                                <th scope="col" class="w-36 text-center">Level kompetensi</th>
                                <th scope="col" class="w-36 text-center">Acceptance level</th>
                                <th scope="col" class="w-64">Aksi</th>
                            </x-slot>
                            @foreach($rows as $row)
                                @php($enrollment = $row['enrollment'])
                                <tr class="{{ $enrollment->is_unlocked ? '' : 'opacity-60' }}">
                                    <td class="text-gray-500">{{ $loop->iteration }}</td>
                                    <td>
                                        <p class="font-semibold">{{ $row['part_name'] }}</p>
                                        <p class="mt-0.5 text-xs text-gray-500"><span class="font-mono">{{ $enrollment->training->kode_training }}</span> · {{ $enrollment->training->judul }}</p>
                                    </td>
                                    <td class="font-mono text-sm">{{ $row['part_number'] ?? ($enrollment->training->is_general ? 'Umum' : '–') }}</td>
                                    <td class="text-center text-base font-semibold">{{ $row['competency_level'] }}</td>
                                    <td class="text-center text-base font-semibold">{{ $enrollment->training->acceptance_level }}</td>
                                    <td>@include('operator.partials.training-action', ['enrollment' => $enrollment])</td>
                                </tr>
                            @endforeach
                        </x-table>
                    </div>
                    <div class="space-y-3 md:hidden" data-training-cards>
                        @foreach($rows as $row)
                            @php($enrollment = $row['enrollment'])
                            <article class="rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800 {{ $enrollment->is_unlocked ? '' : 'opacity-60' }}">
                                <div class="flex items-start gap-3">
                                    <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-md bg-gray-100 text-xs font-semibold text-gray-600 dark:bg-gray-700 dark:text-gray-300">{{ $loop->iteration }}</span>
                                    <div class="min-w-0">
                                        <p class="font-semibold leading-snug">{{ $row['part_name'] }}</p>
                                        <p class="mt-0.5 break-words text-xs text-gray-500"><span class="font-mono">{{ $enrollment->training->kode_training }}</span> · {{ $enrollment->training->judul }}</p>
                                    </div>
                                </div>
                                <dl class="mt-3 grid grid-cols-3 gap-2 rounded-lg bg-gray-50 p-2.5 text-center dark:bg-gray-900/40">
                                    <div class="min-w-0"><dt class="text-[11px] text-gray-500">No part</dt><dd class="mt-0.5 break-all font-mono text-xs font-semibold">{{ $row['part_number'] ?? ($enrollment->training->is_general ? 'Umum' : '–') }}</dd></div>
                                    <div><dt class="text-[11px] text-gray-500">Level kompetensi</dt><dd class="mt-0.5 text-base font-semibold">{{ $row['competency_level'] }}</dd></div>
                                    <div><dt class="text-[11px] text-gray-500">Acceptance level</dt><dd class="mt-0.5 text-base font-semibold">{{ $enrollment->training->acceptance_level }}</dd></div>
                                </dl>
                                <div class="mt-3">@include('operator.partials.training-action', ['enrollment' => $enrollment])</div>
                            </article>
                        @endforeach
                    </div>
                </div>
            @empty
                <x-empty-state title="Belum ada training yang ditugaskan" description="Pengelola belum menugaskan training untuk Anda. Hubungi atasan atau pengelola training bila diperlukan." />
            @endforelse
        </div>
    </section>
</div>
@endsection
