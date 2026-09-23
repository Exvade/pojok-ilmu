@extends('layouts.app')

@section('title', 'Skill Matrix Dashboard - Pojok Ilmu MWT')

@section('content')
@include('layouts.navigation')

<div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8 space-y-8">
    
    <!-- Page Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-extrabold font-heading text-brand-dark dark:text-green-400">Skill Matrix & Competency Board</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">Visualisasi 4-Block Kemampuan Aktual Operator vs Target Level Perusahaan</p>
        </div>
        <div class="flex items-center gap-2">
            <span class="inline-flex items-center px-3 py-1.5 rounded-xl text-xs font-semibold bg-green-100 dark:bg-green-950/40 text-brand-dark dark:text-green-400 border border-green-200 dark:border-green-800">
                <span class="w-2 h-2 rounded-full bg-brand-light animate-ping mr-2"></span>
                Real-Time Matrix Updated
            </span>
        </div>
    </div>

    <!-- Stat Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <x-card class="border-l-4 border-l-brand-dark dark:border-l-green-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Total Operator Active</p>
                    <p class="text-3xl font-extrabold text-brand-text dark:text-gray-100 mt-1">{{ $totalOperator }}</p>
                </div>
                <div class="p-3 rounded-2xl bg-green-100 dark:bg-green-950/50 text-brand-dark dark:text-green-400">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                </div>
            </div>
        </x-card>

        <x-card class="border-l-4 border-l-brand-light">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Total Skill Registered</p>
                    <p class="text-3xl font-extrabold text-brand-text dark:text-gray-100 mt-1">{{ $totalSkill }}</p>
                </div>
                <div class="p-3 rounded-2xl bg-blue-100 dark:bg-blue-950/50 text-blue-600 dark:text-blue-400">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path></svg>
                </div>
            </div>
        </x-card>

        <x-card class="border-l-4 border-l-emerald-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Operator Terakreditasi</p>
                    <p class="text-3xl font-extrabold text-emerald-600 dark:text-emerald-400 mt-1">{{ $totalTerakreditasi }}</p>
                </div>
                <div class="p-3 rounded-2xl bg-emerald-100 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
            </div>
        </x-card>

        <x-card class="border-l-4 border-l-amber-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Eskalasi Remedi (Pending)</p>
                    <p class="text-3xl font-extrabold text-amber-600 dark:text-amber-400 mt-1">{{ $totalEscalated }}</p>
                </div>
                <div class="p-3 rounded-2xl bg-amber-100 dark:bg-amber-950/50 text-amber-600 dark:text-amber-400">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                </div>
            </div>
        </x-card>
    </div>

    <!-- Escalation Alert List (If any) -->
    @if($escalatedEnrollments->count() > 0)
        <div class="bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800 rounded-3xl p-6 shadow-sm">
            <div class="flex items-center gap-3 mb-4">
                <svg class="w-6 h-6 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                <div>
                    <h3 class="font-heading font-bold text-amber-900 dark:text-amber-200 text-lg">Perhatian: Membutuhkan Eskalasi HR / Atasan</h3>
                    <p class="text-xs text-amber-700 dark:text-amber-300">Operator di bawah ini gagal ujian melebihi batas kuota remedi yang ditentukan.</p>
                </div>
            </div>

            <div class="space-y-3">
                @foreach($escalatedEnrollments as $esc)
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between p-4 bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-amber-100 dark:border-amber-900/50 gap-4">
                        <div>
                            <p class="font-bold text-gray-900 dark:text-gray-100">{{ $esc->manpower->nama }} <span class="text-xs font-mono text-gray-500">({{ $esc->manpower->mp_id }})</span></p>
                            <p class="text-xs text-gray-500">Modul: {{ $esc->training->judul }} &bull; Skill: {{ $esc->training->skill->nama_skill }}</p>
                        </div>
                        <form action="{{ route('admin.reset-remedi', $esc->id) }}" method="POST">
                            @csrf
                            <x-button type="submit" variant="secondary" size="sm">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                                Reset Remedi & Izinkan Ujian
                            </x-button>
                        </form>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Skill Matrix Main Table -->
    <x-card>
        <x-slot name="header">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 w-full">
                <h3 class="font-bold font-heading text-lg">Matriks Kompetensi Operator (4-Block Skill Matrix)</h3>
                
                <!-- Filter Form -->
                <form action="{{ route('admin.dashboard') }}" method="GET" class="flex flex-wrap items-center gap-3">
                    <select name="departemen" class="px-3 py-2 rounded-xl text-sm border border-gray-300 dark:border-gray-700 dark:bg-gray-800 focus:ring-brand-light">
                        <option value="">-- Semua Departemen --</option>
                        @foreach($departemens as $dept)
                            <option value="{{ $dept }}" {{ $departemenFilter === $dept ? 'selected' : '' }}>{{ $dept }}</option>
                        @endforeach
                    </select>

                    <input type="text" name="search" value="{{ $search }}" placeholder="Cari nama / NPK..." class="px-3 py-2 rounded-xl text-sm border border-gray-300 dark:border-gray-700 dark:bg-gray-800 focus:ring-brand-light">

                    <x-button type="submit" variant="primary" size="sm">Filter</x-button>
                </form>
            </div>
        </x-slot>

        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left border-collapse">
                <thead>
                    <tr class="bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 text-xs font-bold uppercase tracking-wider">
                        <th class="py-3.5 px-4 rounded-tl-xl">Operator / NPK</th>
                        <th class="py-3.5 px-4">Dept / Bagian</th>
                        @foreach($skills as $sk)
                            <th class="py-3.5 px-4 text-center border-l border-gray-200 dark:border-gray-700">
                                <span>{{ $sk->nama_skill }}</span>
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($manpowers as $mp)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                            <td class="py-4 px-4 font-semibold text-gray-900 dark:text-gray-100 whitespace-nowrap">
                                <div>{{ $mp->nama }}</div>
                                <span class="text-xs text-gray-400 font-mono">NPK: {{ $mp->mp_id }}</span>
                            </td>
                            <td class="py-4 px-4 text-xs text-gray-600 dark:text-gray-400 whitespace-nowrap">
                                <div>{{ $mp->departemen }}</div>
                                <span class="text-gray-400">{{ $mp->bagian }}</span>
                            </td>

                            @foreach($skills as $sk)
                                @php
                                    $matrix = $mp->skillMatrices->firstWhere('skill_id', $sk->id);
                                    $current = $matrix ? $matrix->current_level : 0;
                                    $target = $matrix ? $matrix->target_level : 1;
                                    $gap = $target - $current;
                                @endphp
                                <td class="py-4 px-4 border-l border-gray-200 dark:border-gray-700 text-center align-middle whitespace-nowrap">
                                    <div class="inline-flex flex-col items-center">
                                        <!-- 4-Block Level Indicator -->
                                        <div class="flex items-center gap-1 mb-1">
                                            @for($l = 1; $l <= 4; $l++)
                                                <div class="w-3.5 h-6 rounded-sm border {{ $l <= $current ? 'bg-brand-light border-green-600 shadow-sm' : 'bg-gray-200 dark:bg-gray-700 border-gray-300 dark:border-gray-600' }}" title="Level {{ $l }}"></div>
                                            @endfor
                                        </div>
                                        
                                        <!-- Level Label & Status Badge -->
                                        <div class="text-[11px] font-bold">
                                            <span class="text-gray-700 dark:text-gray-300">Lvl {{ $current }}</span>
                                            <span class="text-gray-400 font-normal">/ Target {{ $target }}</span>
                                        </div>

                                        @if($gap <= 0)
                                            <span class="mt-1 px-2 py-0.5 text-[10px] font-bold rounded-md bg-emerald-100 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-400">Lulus</span>
                                        @else
                                            <span class="mt-1 px-2 py-0.5 text-[10px] font-bold rounded-md bg-amber-100 dark:bg-amber-950/60 text-amber-700 dark:text-amber-400">Gap -{{ $gap }} Lvl</span>
                                        @endif
                                    </div>
                                </td>
                            @endforeach
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ 2 + $skills->count() }}" class="text-center py-8">
                                <x-empty-state title="Belum Ada Data Skill Matrix" description="Data operator atau skill belum tersedia di sistem." />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <x-slot name="footer">
            <div class="mt-4">
                {{ $manpowers->links() }}
            </div>
        </x-slot>
    </x-card>
</div>
@endsection
