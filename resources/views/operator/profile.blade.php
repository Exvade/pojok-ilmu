@extends('layouts.app')

@section('title', 'Profil Saya · Pojok Ilmu')

@section('content')
    <div class="page-shell">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-brand-dark dark:text-green-400">Profil kompetensi</p>
                <h1 class="page-title">{{ $manpower->nama }}</h1>
                <p class="page-description">Riwayat training, hasil post-test, dan hasil OJT Anda.</p>
            </div>
            <x-button :href="route('operator.dashboard')" variant="outline"><x-icon name="back" class="h-4 w-4" />Kembali ke training</x-button>
        </div>

        <div class="grid gap-4 sm:grid-cols-3">
            <x-card><p class="text-xs font-medium text-gray-500">Departemen</p><p class="mt-2 font-semibold">{{ $manpower->departemen }}</p><p class="mt-1 text-xs text-gray-500">{{ $manpower->bagian }}</p></x-card>
            <x-card><p class="text-xs font-medium text-gray-500">Balok 1</p><p class="mt-2 text-3xl font-semibold text-amber-600">{{ $enrollments->where('block', 1)->count() }}</p><p class="mt-1 text-xs text-gray-500">Lulus post-test.</p></x-card>
            <x-card><p class="text-xs font-medium text-gray-500">Balok 2</p><p class="mt-2 text-3xl font-semibold text-brand-dark dark:text-green-400">{{ $enrollments->where('block', 2)->count() }}</p><p class="mt-1 text-xs text-gray-500">OJT disetujui Leader.</p></x-card>
        </div>

        <x-card>
            <x-slot name="header"><h2 class="font-heading text-lg font-bold">Riwayat training</h2></x-slot>
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
