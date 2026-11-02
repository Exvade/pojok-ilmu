@extends('layouts.app')

@section('title', 'Kelola Peserta Training - Pojok Ilmu')

@section('content')
<div class="page-shell max-w-5xl" x-data="{ query: '', normalize(value) { return String(value).toLocaleLowerCase('id-ID') }, matches(value) { return this.normalize(value).includes(this.normalize(this.query)) }, selectAll() { this.$refs.people.querySelectorAll('input[type=checkbox]:not(:disabled)').forEach((input) => input.checked = true) }, clearAll() { this.$refs.people.querySelectorAll('input[type=checkbox]:not(:disabled)').forEach((input) => input.checked = false) } }">
    <a href="{{ route('admin.trainings.index') }}" class="mb-4 inline-flex items-center gap-1 text-sm font-semibold text-brand-dark hover:underline">&larr; Kembali ke training</a>
    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div><p class="text-xs font-semibold uppercase tracking-wider text-brand-dark dark:text-green-400">Peserta training</p><h1 class="page-title">{{ $training->judul }}</h1><p class="page-description">{{ $training->departmentLabel() }} · Level {{ $training->level }}</p></div>
        <div class="flex gap-2"><x-button :href="route('admin.trainings.participants.history', $training)" variant="outline">Riwayat perubahan</x-button><x-button :href="route('admin.trainings.participants.import')" variant="secondary">Impor Excel</x-button></div>
    </div>
    <x-card bodyClass="p-5" class="mt-5">
        <p class="text-sm font-semibold">Atur peserta</p><p class="mt-1 text-sm text-gray-500">Cari dan tambahkan manpower aktif dari departemen mana pun. Departemen peserta yang baru akan otomatis ditambahkan ke cakupan training. Peserta yang sudah memulai training tidak dibatalkan agar riwayat kompetensinya tetap aman.</p>
        <form action="{{ route('admin.trainings.participants.store', $training) }}" method="POST" class="mt-5 space-y-4">
            @csrf
            <div class="flex flex-wrap items-center justify-between gap-3 rounded-lg border border-gray-200 bg-gray-50 p-3 dark:border-gray-700 dark:bg-gray-800"><span class="text-sm text-gray-600 dark:text-gray-300">{{ $manpowers->count() }} manpower aktif tersedia</span><div class="flex gap-2"><x-button type="button" size="sm" variant="secondary" @click="selectAll()">Pilih semua</x-button><x-button type="button" size="sm" variant="outline" @click="clearAll()">Kosongkan</x-button></div></div>
            <div><label for="participant-search" class="mb-1.5 block text-sm font-medium">Cari peserta</label><x-input id="participant-search" x-model="query" placeholder="Cari nama, NIK, atau bagian..." autocomplete="off" /></div>
            <div x-ref="people" class="max-h-[32rem] space-y-2 overflow-y-auto rounded-lg border border-gray-200 p-3 dark:border-gray-700">
                @if($manpowers->isEmpty())
                    <p class="p-4 text-center text-sm text-gray-500">Tidak ada manpower aktif yang tersedia.</p>
                @else
                @foreach($manpowers as $manpower)
                    @php($enrollment = $training->enrollments->firstWhere('manpower_id', $manpower->id))
                    @php($canCancel = ! $enrollment || $enrollment->status === 'enrolled')
                    @php($searchValue = implode(' ', [$manpower->nama, $manpower->nik, $manpower->bagian, $manpower->trainingDepartment()]))
                    <label x-show="matches(@js($searchValue))" class="flex items-center gap-3 rounded-md p-3 {{ $canCancel ? 'hover:bg-gray-50 dark:hover:bg-gray-800' : 'bg-gray-50 dark:bg-gray-800/60' }}">
                        <input type="checkbox" name="manpower_ids[]" value="{{ $manpower->id }}" {{ $enrollment ? 'checked' : '' }} {{ ! $canCancel ? 'disabled' : '' }} class="rounded border-gray-300 text-brand-dark focus:ring-brand-light">
                        <span><span class="block text-sm font-semibold">{{ $manpower->nama }}</span><span class="text-xs text-gray-500">{{ $manpower->nik }} - {{ $manpower->bagian }}{{ ! $canCancel ? ' - Riwayat dipertahankan' : '' }}</span></span>
                    </label>
                @endforeach
                @endif
            </div>
            <div><label for="reason" class="mb-1 block text-sm font-medium">Catatan perubahan <span class="font-normal text-gray-500">(opsional)</span></label><x-textarea id="reason" name="reason" rows="2" placeholder="Contoh: penugasan disesuaikan dengan kebutuhan lini produksi." /></div>
            <div class="flex justify-end"><x-button type="submit"><x-icon name="save" class="h-4 w-4" />Simpan peserta</x-button></div>
        </form>
    </x-card>
</div>
@endsection
