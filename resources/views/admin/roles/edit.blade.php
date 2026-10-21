@extends('layouts.app')

@section('title', 'Atur Akses · Pojok Ilmu')

@section('content')
<div class="page-shell !max-w-5xl">
    <div><a href="{{ route('admin.roles.index') }}" class="text-sm font-medium text-brand-dark hover:underline">&larr; Kembali ke Kelola Role</a><h1 class="page-title mt-3">Atur akses pengelola</h1><p class="page-description">{{ $manpower->nama }} · {{ $manpower->nik }}</p></div>
    <x-card body-class="p-5 sm:p-6">
        <div class="grid gap-3 border-b border-gray-200 pb-5 text-sm dark:border-gray-700 sm:grid-cols-2"><div><p class="text-xs text-gray-500">Departemen</p><p class="mt-1 font-medium">{{ $manpower->departemen }}</p></div><div><p class="text-xs text-gray-500">Jabatan</p><p class="mt-1 font-medium">{{ $manpower->jabatan ?? $manpower->bagian }}</p></div></div>
        <form action="{{ route('admin.roles.update', $manpower) }}" method="POST" class="mt-5 space-y-6">@csrf @method('PUT')
            <fieldset class="space-y-3"><legend class="text-base font-semibold">Akses yang diberikan</legend>
                <p class="text-sm text-gray-500">Pilih sesuai tanggung jawab kerja karyawan. Akses penuh hanya dapat diatur melalui akun superadmin.</p>
                <div class="grid gap-3 md:grid-cols-3">
                <label class="flex min-h-36 items-start gap-3 rounded-lg border border-gray-200 p-4 transition-colors hover:border-brand-dark hover:bg-green-50/50 dark:border-gray-700 dark:hover:bg-gray-700/50"><input type="checkbox" name="can_manage_competency" value="1" @checked(old('can_manage_competency', $manpower->user?->can_manage_competency))><span><span class="block font-semibold">Kelola kompetensi & penugasan</span><span class="mt-1 block text-sm text-gray-500">Menugaskan training, melihat laporan, dan memantau capaian kompetensi.</span></span></label>
                <label class="flex min-h-36 items-start gap-3 rounded-lg border border-gray-200 p-4 transition-colors hover:border-brand-dark hover:bg-green-50/50 dark:border-gray-700 dark:hover:bg-gray-700/50"><input type="checkbox" name="can_manage_curriculum" value="1" @checked(old('can_manage_curriculum', $manpower->user?->can_manage_curriculum))><span><span class="block font-semibold">Kelola materi & evaluasi</span><span class="mt-1 block text-sm text-gray-500">Mengelola training, materi pembelajaran, soal evaluasi, impor, dan alias departemen.</span></span></label>
                <label class="flex min-h-36 items-start gap-3 rounded-lg border border-gray-200 p-4 transition-colors hover:border-brand-dark hover:bg-green-50/50 dark:border-gray-700 dark:hover:bg-gray-700/50"><input type="checkbox" name="can_assess_ojt" value="1" @checked(old('can_assess_ojt', $manpower->user?->can_assess_ojt))><span><span class="block font-semibold">Nilai OJT</span><span class="mt-1 block text-sm text-gray-500">Membuka antrean OJT dan mengisi hasil penilaian manpower.</span></span></label>
                </div>
            </fieldset>
            <div><label for="password" class="mb-2 block text-sm font-medium">Kata sandi pengelola</label><x-password id="password" name="password" placeholder="Wajib saat akses pertama kali diberikan" autocomplete="new-password" /><p class="mt-2 text-xs text-gray-500">Kosongkan untuk mempertahankan kata sandi yang sudah ada.</p></div>
            <div class="flex justify-end gap-3"><x-button :href="route('admin.roles.index')" variant="outline"><x-icon name="close" class="h-4 w-4" />Batal</x-button><x-button type="submit"><x-icon name="save" class="h-4 w-4" />Simpan akses</x-button></div>
        </form>
    </x-card>
</div>
@endsection
