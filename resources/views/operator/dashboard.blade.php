@extends('layouts.app')
@section('title', 'Pelatihan saya · Pojok Ilmu')
@section('content')
<div class="page-shell">
    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-start">
        <div><p class="mb-2 text-xs font-semibold uppercase tracking-wider text-brand-dark dark:text-green-400">Ruang belajar karyawan</p><h1 class="page-title">Selamat datang, {{ $manpower?->nama ?? auth()->user()->name }}</h1><p class="page-description">Lanjutkan pembelajaran dan pantau perkembangan kompetensi Anda.</p></div>
        <span class="self-start rounded-md border border-gray-200 bg-white px-3 py-2 font-mono text-xs text-gray-500 dark:border-gray-700 dark:bg-gray-800">{{ $manpower?->mp_id ?? auth()->user()->user_id }}</span>
    </div>
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
        @foreach([['Pelatihan ditugaskan', $enrollments->count()], ['Pelatihan selesai', $enrollments->where('status', 'passed')->count()], ['Kompetensi ditargetkan', $matrices->count()]] as [$label, $value])
            <x-card><p class="text-xs font-medium text-gray-500">{{ $label }}</p><p class="mt-3 text-3xl font-semibold tracking-tight">{{ $value }}</p></x-card>
        @endforeach
    </div>
    <div class="rounded-lg border border-green-200 bg-green-50 px-5 py-4 dark:border-green-900 dark:bg-green-950/30">
        <p class="text-sm font-semibold text-brand-dark dark:text-green-300">{{ $manpower?->departemen ?? 'Departemen belum tersedia' }}</p>
        <p class="mt-1 text-xs text-gray-600 dark:text-gray-400">{{ $manpower?->bagian ?? 'Jabatan belum tersedia' }} · Materi ditugaskan berdasarkan kebutuhan kompetensi.</p>
    </div>
    <section aria-labelledby="training-heading">
        <div class="mb-4 flex items-center justify-between gap-3"><h2 id="training-heading" class="text-base font-semibold">Pelatihan saya</h2><span class="text-xs text-gray-500">{{ $enrollments->count() }} pelatihan</span></div>
        <div class="grid grid-cols-1 gap-4 xl:grid-cols-2">
            @forelse($enrollments as $en)
                <x-card>
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <span class="text-xs font-medium text-gray-500">{{ $en->training->kode_training }} · Level {{ $en->training->level }}</span>
                        <span class="rounded-md px-2 py-1 text-xs font-semibold {{ $en->status === 'passed' ? 'bg-green-50 text-green-700 dark:bg-green-950 dark:text-green-300' : ($en->status === 'failed_escalated' ? 'bg-red-50 text-red-700 dark:bg-red-950 dark:text-red-300' : 'bg-blue-50 text-blue-700 dark:bg-blue-950 dark:text-blue-300') }}">{{ ['passed' => 'Selesai', 'failed_escalated' => 'Perlu tindak lanjut', 'in_progress' => 'Sedang dipelajari', 'enrolled' => 'Belum dimulai'][$en->status] ?? 'Ditugaskan' }}</span>
                    </div>
                    <h3 class="mt-4 text-lg font-semibold">{{ $en->training->judul }}</h3>
                    <p class="mt-2 text-sm text-gray-500">{{ $en->training->skill?->nama_skill ?? 'Kompetensi tidak tersedia' }}</p>
                    <x-slot name="footer">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div class="text-xs leading-5 text-gray-500">Nilai kelulusan {{ $en->training->passing_grade }}%<br>Percobaan gagal {{ $en->current_retry_count }} / {{ $en->training->quiz?->max_retries ?? $en->training->max_retries }}</div>
                            @if($en->status === 'failed_escalated')
                                <span class="text-xs text-red-600 dark:text-red-400">Hubungi HR / atasan</span>
                            @else
                                <x-button :href="route('operator.materials.show', $en->training->id)" size="sm">{{ $en->status === 'passed' ? 'Tinjau materi' : 'Buka materi' }}<x-icon name="arrow" class="h-4 w-4" /></x-button>
                            @endif
                        </div>
                    </x-slot>
                </x-card>
            @empty
                <x-card class="xl:col-span-2"><x-empty-state title="Belum ada pelatihan yang ditugaskan" description="Pelatihan akan muncul setelah HR menetapkan target kompetensi dan materi yang sesuai dengan jabatan Anda." /></x-card>
            @endforelse
        </div>
    </section>
    <x-card>
        <x-slot name="header"><h2 class="text-base font-semibold">Perkembangan kompetensi</h2></x-slot>
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
            @forelse($matrices as $mx)
                <div class="rounded-md border border-gray-200 p-4 dark:border-gray-700">
                    <p class="text-xs text-gray-500">{{ $mx->skill?->kode_skill }}</p><h3 class="mt-2 text-sm font-semibold">{{ $mx->skill?->nama_skill ?? 'Kompetensi tidak tersedia' }}</h3>
                    <div class="mb-2 mt-5 flex justify-between text-xs text-gray-500"><span>Level {{ $mx->current_level }}</span><span>Target {{ $mx->target_level }}</span></div>
                    <div class="flex gap-1" aria-label="Level {{ $mx->current_level }} dari target {{ $mx->target_level }}">@for($level = 1; $level <= 4; $level++)<div class="h-2 flex-1 rounded-sm {{ $level <= $mx->current_level ? 'bg-brand-dark dark:bg-green-500' : 'bg-gray-100 dark:bg-gray-700' }}"></div>@endfor</div>
                    <p class="mt-3 text-xs {{ $mx->current_level >= $mx->target_level ? 'text-green-700 dark:text-green-400' : 'text-amber-700 dark:text-amber-400' }}">{{ $mx->current_level >= $mx->target_level ? 'Target tercapai' : 'Masih perlu peningkatan '.($mx->target_level - $mx->current_level).' level' }}</p>
                </div>
            @empty
                <div class="col-span-full"><x-empty-state title="Target kompetensi belum ditetapkan" description="Hubungi HR atau atasan untuk penetapan target sesuai jabatan Anda." /></div>
            @endforelse
        </div>
    </x-card>
</div>
@endsection
