@extends('layouts.app')

@section('title', 'Profil Saya · Pojok Ilmu')

@section('content')
    <div class="page-shell">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-brand-dark dark:text-green-400">Profil kompetensi</p>
                <h1 class="page-title">{{ $manpower->nama }}</h1>
                <p class="page-description">Lihat posisi level, training yang sudah dikuasai, dan langkah yang perlu diselesaikan berikutnya.</p>
            </div>
            <x-button :href="route('operator.dashboard')" variant="outline"><x-icon name="back" class="h-4 w-4" />Kembali ke training</x-button>
        </div>

        <div class="grid gap-4 sm:grid-cols-3">
            <x-card><p class="text-xs font-medium text-gray-500">Posisi level saat ini</p><p class="mt-2 text-2xl font-semibold">{{ $currentLevel ? 'Level ' . $currentLevel : 'Belum mencapai Level 1' }}</p><p class="mt-1 text-xs text-gray-500">Level lengkap setelah seluruh training pada level tersebut mencapai Balok 2.</p></x-card>
            <x-card><p class="text-xs font-medium text-gray-500">Training Balok 2</p><p class="mt-2 text-3xl font-semibold text-brand-dark dark:text-green-400">{{ $enrollments->where('block', 2)->count() }}</p><p class="mt-1 text-xs text-gray-500">Mampu mengerjakan dengan bimbingan.</p></x-card>
            <x-card><p class="text-xs font-medium text-gray-500">Departemen</p><p class="mt-2 font-semibold">{{ $manpower->departemen }}</p><p class="mt-1 text-xs text-gray-500">{{ $manpower->bagian }}</p></x-card>
        </div>

        <x-card bodyClass="p-5">
            <x-slot name="header">
                <div>
                    <h2 class="font-heading text-lg font-bold">Perjalanan level</h2>
                    <p class="mt-1 text-xs text-gray-500">Selesaikan semua training pada satu level hingga Balok 2 untuk membuka level berikutnya.</p>
                </div>
            </x-slot>

            <div class="space-y-4">
                @forelse ($levels as $level)
                    <section @class([
                        'rounded-xl border p-4 sm:p-5',
                        'border-green-200 bg-green-50/60 dark:border-green-900 dark:bg-green-950/20' => $level['is_complete'],
                        'border-amber-200 bg-amber-50/50 dark:border-amber-900 dark:bg-amber-950/20' => ! $level['is_complete'] && ! $level['is_unlocked'],
                        'border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800' => ! $level['is_complete'] && $level['is_unlocked'],
                    ])>
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wider text-brand-dark dark:text-green-400">Level {{ $level['number'] }}</p>
                                <h3 class="mt-1 text-base font-semibold">{{ $level['completed'] }} dari {{ $level['enrollments']->count() }} training mencapai Balok 2</h3>
                            </div>
                            <span @class([
                                'inline-flex w-fit rounded-full px-3 py-1 text-xs font-semibold',
                                'bg-green-100 text-brand-dark dark:bg-green-950 dark:text-green-300' => $level['is_complete'],
                                'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-200' => ! $level['is_complete'] && ! $level['is_unlocked'],
                                'bg-blue-50 text-sky-700 dark:bg-sky-950 dark:text-sky-300' => ! $level['is_complete'] && $level['is_unlocked'],
                            ])>{{ $level['is_complete'] ? 'Level selesai' : ($level['is_unlocked'] ? 'Sedang dikerjakan' : 'Terkunci') }}</span>
                        </div>

                        @if (! $level['is_unlocked'])
                            <p class="mt-3 text-xs leading-5 text-amber-800 dark:text-amber-200">Selesaikan Balok 2 untuk: {{ implode(', ', $level['blocking_training_titles']) }}.</p>
                        @endif

                        <div class="mt-4 divide-y divide-gray-200 rounded-lg border border-gray-200 bg-white dark:divide-gray-700 dark:border-gray-700 dark:bg-gray-900">
                            @foreach ($level['enrollments'] as $enrollment)
                                <div class="flex flex-col gap-3 p-3 sm:flex-row sm:items-center sm:justify-between">
                                    <div class="min-w-0"><p class="font-semibold">{{ $enrollment->training->judul }}</p><p class="mt-1 text-xs text-gray-500">{{ $enrollment->block_label }}</p></div>
                                    <x-competency-blocks :value="$enrollment->block" />
                                </div>
                            @endforeach
                        </div>
                    </section>
                @empty
                    <x-empty-state title="Belum ada training" description="Training yang ditugaskan akan muncul sebagai perjalanan level di sini." />
                @endforelse
            </div>
        </x-card>

        <x-card>
            <x-slot name="header"><div><h2 class="font-heading text-lg font-bold">Detail riwayat training</h2><p class="mt-1 text-xs text-gray-500">Rincian hasil post-test dan OJT untuk setiap training.</p></div></x-slot>
            <x-table>
                <x-slot name="head"><tr><th>Training</th><th>Post-test</th><th>OJT</th><th>Balok</th><th class="text-right">Aksi</th></tr></x-slot>
                @forelse ($enrollments as $enrollment)
                    @php($postTest = $enrollment->attempts->first(fn ($attempt) => $attempt->quiz?->tipe === 'post_test'))
                    <tr>
                        <td><p class="font-semibold">{{ $enrollment->training->judul }}</p><p class="mt-1 text-xs text-gray-500">Level {{ $enrollment->training->level }}</p></td>
                        <td>@if ($enrollment->post_test_completed_at)<span class="font-semibold text-brand-dark dark:text-green-300">Lulus · {{ $postTest?->score ?? '-' }}%</span>@else<span class="text-gray-400">Belum lulus</span>@endif</td>
                        <td>
                            @if ($enrollment->ojt_status === 'approved')<span class="font-semibold text-brand-dark dark:text-green-300">Disetujui</span>
                            @elseif ($enrollment->ojt_status === 'rejected')<span class="font-semibold text-red-600">Perlu OJT ulang</span>
                            @elseif ($enrollment->ojt_status === 'pending')<span class="text-amber-700">Menunggu Leader</span>
                            @else<span class="text-gray-400">Belum tersedia</span>@endif
                        </td>
                        <td><x-competency-blocks :value="$enrollment->block" :show-label="false" /></td>
                        <td class="text-right">
                            @if ($enrollment->ojtAssessment)
                                <x-button :href="route('operator.ojt-result', $enrollment->id)" variant="ghost" size="sm" aria-label="Lihat hasil OJT {{ $enrollment->training->judul }}" title="Lihat hasil OJT"><x-icon name="eye" class="h-4 w-4" /></x-button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5"><x-empty-state title="Belum ada training" description="Training yang ditugaskan akan muncul di sini." /></td></tr>
                @endforelse
            </x-table>
        </x-card>
    </div>
@endsection
