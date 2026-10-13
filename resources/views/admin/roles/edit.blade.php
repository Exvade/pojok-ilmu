@extends('layouts.app')

@section('title', 'Atur Akses · Pojok Ilmu')

@section('content')
<div class="page-shell !max-w-3xl">
    <div><a href="{{ route('admin.roles.index') }}" class="text-sm font-medium text-brand-dark hover:underline">&larr; Kembali ke Kelola Role</a><h1 class="page-title mt-3">Atur akses pengelola</h1><p class="page-description">{{ $manpower->nama }} · {{ $manpower->nik }}</p></div>
    <x-card>
        <div class="grid gap-3 border-b border-gray-200 pb-5 text-sm dark:border-gray-700 sm:grid-cols-2"><div><p class="text-xs text-gray-500">Departemen</p><p class="mt-1 font-medium">{{ $manpower->departemen }}</p></div><div><p class="text-xs text-gray-500">Jabatan</p><p class="mt-1 font-medium">{{ $manpower->jabatan ?? $manpower->bagian }}</p></div></div>
        <form action="{{ route('admin.roles.update', $manpower) }}" method="POST" class="mt-6 space-y-5">@csrf @method('PUT')
            <fieldset class="space-y-3"><legend class="text-sm font-semibold">Hak akses</legend>
                <label class="flex items-start gap-3 rounded-lg border border-gray-200 p-4 dark:border-gray-700"><input type="checkbox" name="can_manage_competency" value="1" @checked(old('can_manage_competency', $manpower->user?->can_manage_competency))><span><span class="block font-semibold">Kompetensi & leveling</span><span class="mt-1 block text-sm text-gray-500">Mengelola master kompetensi, struktur level, dan capaian operator.</span></span></label>
                <label class="flex items-start gap-3 rounded-lg border border-gray-200 p-4 dark:border-gray-700"><input type="checkbox" name="can_manage_curriculum" value="1" @checked(old('can_manage_curriculum', $manpower->user?->can_manage_curriculum))><span><span class="block font-semibold">Kurikulum & Modul</span><span class="mt-1 block text-sm text-gray-500">Mengelola training, materi pembelajaran, dan soal evaluasi.</span></span></label>
                <label class="flex items-start gap-3 rounded-lg border border-gray-200 p-4 dark:border-gray-700"><input type="checkbox" name="can_assess_ojt" value="1" @checked(old('can_assess_ojt', $manpower->user?->can_assess_ojt))><span><span class="block font-semibold">Penilai OJT produksi</span><span class="mt-1 block text-sm text-gray-500">Melihat antrean dan mengisi hasil OJT manpower produksi.</span></span></label>
            </fieldset>
            <div><label for="password" class="mb-2 block text-sm font-medium">Kata sandi pengelola</label><x-password id="password" name="password" placeholder="Wajib saat akses pertama kali diberikan" autocomplete="new-password" /><p class="mt-2 text-xs text-gray-500">Kosongkan untuk mempertahankan kata sandi yang sudah ada.</p></div>
            <div class="flex justify-end gap-3"><x-button :href="route('admin.roles.index')" variant="outline"><x-icon name="close" class="h-4 w-4" />Batal</x-button><x-button type="submit"><x-icon name="save" class="h-4 w-4" />Simpan akses</x-button></div>
        </form>
    </x-card>
</div>
@endsection
