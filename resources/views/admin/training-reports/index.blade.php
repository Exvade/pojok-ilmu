@extends('layouts.app')

@section('title', 'Laporan training · Pojok Ilmu MWT')

@section('content')
    @php
        $exportQuery = array_filter($filters, fn (string $value): bool => $value !== '');
        $statusClasses = [
            'belum_dimulai' => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-200',
            'dalam_proses' => 'bg-sky-50 text-sky-700 dark:bg-sky-950 dark:text-sky-300',
            'remedi' => 'bg-red-100 text-red-700 dark:bg-red-950 dark:text-red-300',
            'menunggu_ojt' => 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-200',
            'ojt_ulang' => 'bg-red-100 text-red-700 dark:bg-red-950 dark:text-red-300',
            'balok_2' => 'bg-green-100 text-brand-dark dark:bg-green-950 dark:text-green-300',
        ];
    @endphp

    <div class="page-shell">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-brand-dark dark:text-green-400">Monitoring kompetensi</p>
                <h1 class="page-title">Laporan training</h1>
                <p class="page-description">Pantau penugasan dan tindak lanjut training setiap departemen.</p>
            </div>
            <x-button :href="route('admin.training-reports.export', $exportQuery)" variant="outline" data-no-loading><x-icon name="download" class="h-4 w-4" />Ekspor Excel</x-button>
        </div>

        <x-card>
            <form action="{{ route('admin.training-reports.index') }}" method="GET" data-auto-search class="grid gap-3 md:grid-cols-2 xl:grid-cols-[minmax(0,1fr)_minmax(0,1fr)_minmax(0,1fr)_auto_auto]">
                <x-select name="departemen" aria-label="Filter departemen">
                    <option value="">Semua departemen</option>
                    @foreach($departments as $department)
                        <option value="{{ $department }}" @selected($filters['departemen'] === $department)>{{ $department }}</option>
                    @endforeach
                </x-select>
                <x-select name="status" aria-label="Filter status training">
                    <option value="">Semua status</option>
                    @foreach($statuses as $value => $label)
                        <option value="{{ $value }}" @selected($filters['status'] === $value)>{{ $label }}</option>
                    @endforeach
                </x-select>
                <x-input name="search" value="{{ $filters['search'] }}" placeholder="Cari nama, NPK, atau training..." aria-label="Cari laporan training" />
                <x-button type="submit" size="sm"><x-icon name="filter" class="h-4 w-4" />Terapkan</x-button>
                <x-button :href="route('admin.training-reports.index')" variant="outline" size="sm"><x-icon name="reset" class="h-4 w-4" />Reset</x-button>
            </form>
        </x-card>

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <x-card><p class="text-sm font-medium text-gray-500">Total penugasan</p><p class="mt-2 text-3xl font-semibold">{{ $summary['total'] }}</p><p class="mt-1 text-xs text-gray-500">Sesuai filter departemen dan pencarian.</p></x-card>
            <x-card><p class="text-sm font-medium text-gray-500">Belum mulai / proses</p><p class="mt-2 text-3xl font-semibold text-sky-700 dark:text-sky-300">{{ $summary['not_started'] + $summary['in_progress'] }}</p><p class="mt-1 text-xs text-gray-500">{{ $summary['not_started'] }} belum mulai · {{ $summary['in_progress'] }} sedang training.</p></x-card>
            <x-card><p class="text-sm font-medium text-gray-500">Menunggu tindak lanjut</p><p class="mt-2 text-3xl font-semibold text-amber-600">{{ $summary['pending_ojt'] + $summary['retry_ojt'] }}</p><p class="mt-1 text-xs text-gray-500">{{ $summary['pending_ojt'] }} menunggu OJT · {{ $summary['retry_ojt'] }} perlu OJT ulang.</p></x-card>
            <x-card><p class="text-sm font-medium text-gray-500">Sudah Balok 2</p><p class="mt-2 text-3xl font-semibold text-brand-dark dark:text-green-300">{{ $summary['block_two'] }}</p><p class="mt-1 text-xs text-gray-500">Mampu mengerjakan dengan bimbingan.</p></x-card>
        </div>

        <x-card bodyClass="p-0">
            <x-slot name="header"><div><h2 class="font-heading text-lg font-bold">Ringkasan per departemen</h2><p class="mt-1 text-xs text-gray-500">Jumlah penugasan pada setiap tahap training.</p></div></x-slot>
            <x-table>
                <x-slot name="head"><tr><th>Departemen</th><th>Total</th><th>Belum mulai</th><th>Proses</th><th>Menunggu OJT</th><th>OJT ulang</th><th>Balok 2</th></tr></x-slot>
                @forelse($departmentSummary as $department)
                    <tr>
                        <td class="font-semibold">{{ $department->department_group }}</td><td>{{ $department->total }}</td><td>{{ $department->belum_dimulai }}</td><td>{{ $department->dalam_proses }}</td><td class="text-amber-700 dark:text-amber-300">{{ $department->menunggu_ojt }}</td><td class="text-red-600">{{ $department->ojt_ulang }}</td><td class="font-semibold text-brand-dark dark:text-green-300">{{ $department->balok_dua }}</td>
                    </tr>
                @empty
                    <tr><td colspan="7"><x-empty-state title="Belum ada penugasan training" description="Data akan muncul setelah training ditugaskan kepada karyawan aktif." /></td></tr>
                @endforelse
            </x-table>
        </x-card>

        <x-card bodyClass="p-0">
            <x-slot name="header"><div><h2 class="font-heading text-lg font-bold">Detail penugasan</h2><p class="mt-1 text-xs text-gray-500">Gunakan filter untuk menentukan data yang perlu ditindaklanjuti.</p></div></x-slot>
            <x-table>
                <x-slot name="head"><tr><th>Karyawan</th><th>Departemen / bagian</th><th>Training</th><th>Status</th><th>Diperbarui</th><th class="text-right">Profil</th></tr></x-slot>
                @forelse($enrollments as $enrollment)
                    <tr>
                        <td><p class="font-semibold text-gray-900 dark:text-white">{{ $enrollment->manpower?->nama ?? 'Karyawan diarsipkan' }}</p><p class="mt-1 font-mono text-xs text-gray-500">{{ $enrollment->manpower?->mp_id ?? '-' }}</p></td>
                        <td><p class="text-sm">{{ $enrollment->manpower?->trainingDepartment() ?? '-' }}</p><p class="mt-1 text-xs text-gray-500">HaRis: {{ $enrollment->manpower?->departemen ?? '-' }} · {{ $enrollment->manpower?->bagian ?? '-' }}</p></td>
                        <td><p class="font-semibold">{{ $enrollment->training?->judul ?? 'Training diarsipkan' }}</p><p class="mt-1 text-xs text-gray-500">{{ $enrollment->training?->kode_training ?? '-' }} · Level {{ $enrollment->training?->level ?? '-' }}</p></td>
                        <td><span @class(['inline-flex rounded-full px-2.5 py-1 text-xs font-semibold', $statusClasses[$enrollment->report_status] ?? $statusClasses['dalam_proses']])>{{ $enrollment->report_status_label }}</span></td>
                        <td class="text-sm text-gray-600 dark:text-gray-300">{{ $enrollment->updated_at?->translatedFormat('d M Y, H:i') }}</td>
                        <td class="text-right">@if($enrollment->manpower)<x-button :href="route('admin.manpower.training-profile', $enrollment->manpower->id)" variant="ghost" size="sm" aria-label="Lihat profil {{ $enrollment->manpower->nama }}" title="Lihat profil"><x-icon name="eye" class="h-4 w-4" /></x-button>@endif</td>
                    </tr>
                @empty
                    <tr><td colspan="6"><x-empty-state title="Data tidak ditemukan" description="Ubah filter atau buat penugasan training terlebih dahulu." /></td></tr>
                @endforelse
            </x-table>
            <x-slot name="footer">{{ $enrollments->links() }}</x-slot>
        </x-card>
    </div>
@endsection
