@extends('layouts.app')

@section('title', 'Training Saya - Portal Operator Pojok Ilmu')

@section('content')
@include('layouts.navigation')

<div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8 space-y-8">
    
    <!-- Operator Greeting Banner -->
    <div class="p-6 sm:p-8 rounded-3xl bg-gradient-to-r from-brand-dark to-green-900 text-white shadow-xl relative overflow-hidden">
        <div class="absolute right-0 top-0 bottom-0 opacity-10 flex items-center pr-8 pointer-events-none">
            <svg class="w-64 h-64" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"></path></svg>
        </div>

        <div class="relative z-10 space-y-2">
            <div class="inline-flex items-center px-3 py-1 rounded-full bg-white/20 backdrop-blur-md text-xs font-semibold">
                Portal Operator &bull; PT Mada Wikri Tunggal
            </div>
            <h1 class="text-3xl font-extrabold font-heading">Selamat Datang, {{ $manpower?->nama ?? auth()->user()->name }}!</h1>
            <p class="text-green-100 text-sm max-w-xl">
                Departemen: <strong>{{ $manpower?->departemen ?? '-' }}</strong> &bull; 
                Bagian: <strong>{{ $manpower?->bagian ?? '-' }}</strong> &bull; 
                NPK: <strong>{{ $manpower?->mp_id ?? auth()->user()->user_id }}</strong>
            </p>
        </div>
    </div>

    <!-- Section 1: Dynamic Skill Level Matrix Operator -->
    <x-card>
        <x-slot name="header">
            <h3 class="font-bold font-heading text-lg">Matriks Kompetensi Saya (Actual Skill Level)</h3>
        </x-slot>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
            @forelse($matrices as $mx)
                @php
                    $gap = $mx->target_level - $mx->current_level;
                @endphp
                <div class="p-5 rounded-2xl bg-gray-50 dark:bg-gray-800/60 border border-gray-200 dark:border-gray-700 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-bold font-mono text-gray-400">{{ $mx->skill->kode_skill }}</span>
                            @if($gap <= 0)
                                <span class="px-2.5 py-1 text-xs font-extrabold rounded-lg bg-emerald-100 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-400">
                                    Lulus / Sesuai Target
                                </span>
                            @else
                                <span class="px-2.5 py-1 text-xs font-extrabold rounded-lg bg-amber-100 dark:bg-amber-950/60 text-amber-700 dark:text-amber-400">
                                    Gap -{{ $gap }} Level
                                </span>
                            @endif
                        </div>
                        <h4 class="font-bold text-base text-gray-900 dark:text-gray-100">{{ $mx->skill->nama_skill }}</h4>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ $mx->skill->kategori }}</p>
                    </div>

                    <!-- 4-Block Level Visualizer -->
                    <div class="mt-4 pt-3 border-t border-gray-200 dark:border-gray-700">
                        <div class="flex items-center justify-between text-xs text-gray-500 mb-1.5">
                            <span>Kemampuan Aktual</span>
                            <span class="font-bold text-brand-dark dark:text-green-400">Level {{ $mx->current_level }} / Target {{ $mx->target_level }}</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            @for($l = 1; $l <= 4; $l++)
                                <div class="flex-1 h-3 rounded-md border {{ $l <= $mx->current_level ? 'bg-brand-light border-green-600 shadow-sm' : 'bg-gray-200 dark:bg-gray-700 border-gray-300 dark:border-gray-600' }}"></div>
                            @endfor
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full">
                    <x-empty-state title="Belum Ada Target Skill" description="Silakan hubungi HR/Atasan untuk penetapan target skill jabatan." />
                </div>
            @endforelse
        </div>
    </x-card>

    <!-- Section 2: Daftar Training (Auto TNA) -->
    <x-card>
        <x-slot name="header">
            <h3 class="font-bold font-heading text-lg">Daftar Pelatihan Mandiri (TNA Tugas Saya)</h3>
        </x-slot>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            @forelse($enrollments as $en)
                <div class="p-6 rounded-3xl bg-white dark:bg-gray-800 border-2 {{ $en->status === 'failed_escalated' ? 'border-red-300 dark:border-red-800 bg-red-50/30' : ($en->status === 'passed' ? 'border-emerald-200 dark:border-emerald-900 bg-emerald-50/20' : 'border-brand-light/30') }} shadow-md flex flex-col justify-between space-y-4">
                    
                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="px-2.5 py-1 text-xs font-mono font-bold rounded-lg bg-green-100 dark:bg-green-950/60 text-brand-dark dark:text-green-400">
                                {{ $en->training->kode_training }}
                            </span>

                            @if($en->status === 'passed')
                                <span class="px-3 py-1 text-xs font-extrabold rounded-full bg-emerald-100 dark:bg-emerald-950 text-emerald-700 dark:text-emerald-400">
                                    &check; LULUS
                                </span>
                            @elseif($en->status === 'failed_escalated')
                                <span class="px-3 py-1 text-xs font-extrabold rounded-full bg-red-100 dark:bg-red-950 text-red-700 dark:text-red-400">
                                    &excl; ESKALASI HR
                                </span>
                            @else
                                <span class="px-3 py-1 text-xs font-extrabold rounded-full bg-blue-100 dark:bg-blue-950 text-blue-700 dark:text-blue-400">
                                    WAJIB DIKERJAKAN
                                </span>
                            @endif
                        </div>

                        <h3 class="font-bold font-heading text-xl text-gray-900 dark:text-gray-100 leading-snug">
                            {{ $en->training->judul }}
                        </h3>

                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            Skill: <strong>{{ $en->training->skill->nama_skill }}</strong> &bull; Level: <strong>Level {{ $en->training->level }}</strong>
                        </p>
                    </div>

                    <div class="pt-4 border-t border-gray-100 dark:border-gray-700 flex items-center justify-between">
                        <div class="text-xs text-gray-500">
                            Passing Grade: <strong>{{ $en->training->passing_grade }}%</strong> <br>
                            Percobaan: <strong>{{ $en->attempts->count() }} / {{ $en->training->max_retries }} kali</strong>
                        </div>

                        <div>
                            @if($en->status === 'failed_escalated')
                                <span class="text-xs font-bold text-red-600">Menunggu Respon HR</span>
                            @else
                                <a href="{{ route('operator.materials.show', $en->training->id) }}" class="inline-flex items-center px-4 py-2.5 rounded-xl font-bold text-xs text-white bg-brand-dark hover:bg-green-900 shadow-md shadow-green-900/20 transition-all">
                                    {{ $en->status === 'passed' ? 'Ulang Materi' : 'Buka Materi Belajar &rarr;' }}
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full">
                    <x-empty-state title="Tidak Ada Tugas Training" description="Selamat! Semua target skill Anda sudah terpenuhi atau belum ada penugasan baru." />
                </div>
            @endforelse
        </div>
    </x-card>
</div>
@endsection
