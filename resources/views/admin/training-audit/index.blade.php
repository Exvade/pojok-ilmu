@extends('layouts.app')

@section('title', 'Audit master training · Pojok Ilmu')

@section('content')
    <div class="page-shell">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-brand-dark dark:text-green-400">Kesiapan kurikulum</p>
                <h1 class="page-title mt-1">Audit master training</h1>
                <p class="page-description">Tinjau kelengkapan materi, post-test, penugasan, dan urutan Level 1 hingga 3 sebelum training digunakan lebih luas.</p>
            </div>
            <x-button :href="route('admin.trainings.index')" variant="outline"><x-icon name="book" class="h-4 w-4" />Kelola training</x-button>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
            @foreach([
                ['Total training', $summary['total'], 'Seluruh training aktif.', null],
                ['Siap dan ditugaskan', $summary['ready'], 'Materi, post-test, soal, dan penugasan tersedia.', null],
                ['Kode duplikat', $summary['duplicateCodes'], 'Kode training yang perlu ditinjau.', 'amber'],
                ['Isi perlu dilengkapi', $summary['contentIssues'], 'Materi atau evaluasi belum siap.', 'amber'],
                ['Belum ditugaskan', $summary['unassigned'], 'Belum memiliki manpower.', 'sky'],
                ['Urutan level', $summary['levelIssues'], 'Departemen dengan level yang loncat.', 'amber'],
                ['Karyawan tanpa tugas', $summary['withoutAssignments'], 'Karyawan aktif belum memiliki training.', 'sky'],
                ['Target belum terpenuhi', $summary['belowTarget'], 'Belum mencapai minimum 6, 5, dan 3 training.', 'amber'],
            ] as [$label, $value, $description, $tone])
                <x-card body-class="p-5">
                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">{{ $label }}</p>
                    <p @class(['mt-2 text-3xl font-semibold', 'text-amber-600 dark:text-amber-400' => ($tone ?? null) === 'amber', 'text-sky-600 dark:text-sky-400' => ($tone ?? null) === 'sky', 'text-brand-dark dark:text-green-400' => ! isset($tone)])>{{ $value }}</p>
                    <p class="mt-2 text-xs leading-5 text-gray-500">{{ $description }}</p>
                </x-card>
            @endforeach
        </div>

        <x-card body-class="p-0">
            <x-slot name="header"><div><h2 class="font-heading text-lg font-bold">Cakupan level per departemen</h2><p class="mt-1 text-xs text-gray-500">Pemeriksaan ini mendeteksi Level 2 tanpa Level 1, serta Level 3 tanpa Level 2 dalam departemen yang sama.</p></div></x-slot>
            @if($levelCoverage->isEmpty())
                <x-empty-state title="Belum ada training" description="Tambahkan training terlebih dahulu untuk memeriksa susunan level." />
            @else
                <x-table>
                    <x-slot name="head"><tr><th>Departemen</th><th>Level 1</th><th>Level 2</th><th>Level 3</th><th>Hasil pemeriksaan</th></tr></x-slot>
                    @foreach($levelCoverage as $coverage)
                        <tr>
                            <td class="font-semibold">{{ $coverage['department'] }}</td>
                            @foreach([1, 2, 3] as $level)<td>{{ $coverage['levels'][$level] }} training</td>@endforeach
                            <td><div class="flex flex-wrap gap-1.5">@forelse($coverage['issues'] as $issue)<span class="rounded-full bg-amber-100 px-2.5 py-1 text-xs font-semibold text-amber-800 dark:bg-amber-950/70 dark:text-amber-200">{{ $issue }}</span>@empty<span class="rounded-full bg-green-100 px-2.5 py-1 text-xs font-semibold text-brand-dark dark:bg-green-950/70 dark:text-green-200">Urutan tersedia</span>@endforelse</div></td>
                        </tr>
                    @endforeach
                </x-table>
            @endif
        </x-card>

        <x-card body-class="p-0">
            <x-slot name="header"><div><h2 class="font-heading text-lg font-bold">Karyawan yang perlu penugasan</h2><p class="mt-1 text-xs text-gray-500">Target minimum per karyawan: 6 training Level 1, 5 training Level 2, dan 3 training Level 3. Jumlah dihitung dari training unik yang sedang ditugaskan.</p></div></x-slot>
            @if($manpowerTargets->isEmpty())
                <x-empty-state title="Target penugasan sudah terpenuhi" description="Seluruh karyawan aktif telah memiliki penugasan dan memenuhi target minimum." />
            @else
                <x-table>
                    <x-slot name="head"><tr><th>Karyawan</th><th>Level 1</th><th>Level 2</th><th>Level 3</th><th>Perlu ditindaklanjuti</th></tr></x-slot>
                    @foreach($manpowerTargets as $target)
                        <tr>
                            <td><p class="font-semibold">{{ $target['manpower']->nama }}</p><p class="mt-1 font-mono text-xs text-gray-500">{{ $target['manpower']->nik }} · {{ $target['manpower']->departemen }}</p></td>
                            @foreach([1 => 6, 2 => 5, 3 => 3] as $level => $minimum)
                                <td>{{ $target['summary']['levels'][$level] }} dari {{ $minimum }}</td>
                            @endforeach
                            <td>
                                <div class="flex flex-wrap gap-1.5">
                                    @if($target['summary']['total'] === 0)
                                        <span class="rounded-full bg-sky-100 px-2.5 py-1 text-xs font-semibold text-sky-800 dark:bg-sky-950/70 dark:text-sky-200">Belum ada penugasan</span>
                                    @endif

                                    @foreach($target['summary']['gaps'] as $level => $gap)
                                        @if($gap > 0)
                                            <span class="rounded-full bg-amber-100 px-2.5 py-1 text-xs font-semibold text-amber-800 dark:bg-amber-950/70 dark:text-amber-200">Level {{ $level }} kurang {{ $gap }}</span>
                                        @endif
                                    @endforeach
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </x-table>
            @endif
        </x-card>

        <x-card body-class="p-0">
            <x-slot name="header"><div><h2 class="font-heading text-lg font-bold">Kelengkapan setiap training</h2><p class="mt-1 text-xs text-gray-500">Post-test dan materinya perlu lengkap agar training dapat diselesaikan oleh karyawan. Pre-test tetap bersifat opsional.</p></div></x-slot>
            @if($trainings->isEmpty())
                <x-empty-state title="Belum ada training" description="Tambahkan training terlebih dahulu untuk memulai audit." />
            @else
                <x-table>
                    <x-slot name="head"><tr><th>Training</th><th>Level</th><th>Materi dan evaluasi</th><th>Penugasan</th><th>Status audit</th><th class="text-right">Aksi</th></tr></x-slot>
                    @foreach($trainings as $training)
                        <tr>
                            <td><p class="font-semibold">{{ $training->judul }}</p><p class="mt-1 font-mono text-xs text-gray-500">{{ $training->kode_training }} · {{ $training->departmentLabel() }}</p></td>
                            <td>Level {{ $training->level }}</td>
                            <td><p>{{ $training->materials_count }} materi · {{ $training->postTest ? $training->audit_post_test_questions.' soal post-test' : 'Post-test belum dibuat' }}</p><p class="mt-1 text-xs text-gray-500">{{ $training->preTest ? $training->audit_pre_test_questions.' soal pre-test (opsional)' : 'Tanpa pre-test' }}</p></td>
                            <td>{{ $training->enrollments_count }} manpower</td>
                            <td><div class="flex flex-wrap gap-1.5">@foreach($training->audit_content_issues as $issue)<span class="rounded-full bg-amber-100 px-2.5 py-1 text-xs font-semibold text-amber-800 dark:bg-amber-950/70 dark:text-amber-200">{{ $issue }}</span>@endforeach @if($training->enrollments_count === 0)<span class="rounded-full bg-sky-100 px-2.5 py-1 text-xs font-semibold text-sky-800 dark:bg-sky-950/70 dark:text-sky-200">Belum ditugaskan</span>@endif @if($training->audit_ready)<span class="rounded-full bg-green-100 px-2.5 py-1 text-xs font-semibold text-brand-dark dark:bg-green-950/70 dark:text-green-200">Siap digunakan</span>@endif</div></td>
                            <td class="text-right"><x-button :href="route('admin.trainings.show', $training->id)" variant="ghost" size="sm" aria-label="Atur training {{ $training->judul }}" title="Atur training"><x-icon name="pencil" class="h-4 w-4" /></x-button></td>
                        </tr>
                    @endforeach
                </x-table>
            @endif
        </x-card>
    </div>
@endsection
