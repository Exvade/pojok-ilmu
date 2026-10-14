@extends('layouts.app')

@section('title', 'Isi OJT · Pojok Ilmu MWT')

@section('content')
    <div class="page-shell !max-w-6xl">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <a href="{{ route('admin.ojt.index') }}" class="inline-flex items-center gap-1 text-sm font-medium text-brand-dark hover:underline">
                    <x-icon name="back" class="h-4 w-4" />Kembali ke antrean OJT
                </a>
                <p class="mt-4 text-xs font-semibold uppercase tracking-wider text-brand-dark dark:text-green-400">Form OJT standar produksi</p>
                <h1 class="page-title">Penilaian {{ $enrollment->training->judul }}</h1>
                <p class="page-description">Isi berdasarkan tanya jawab dan observasi praktik manpower.</p>
            </div>
            <x-competency-blocks :value="1" />
        </div>

        <x-card>
            <div class="grid gap-4 text-sm sm:grid-cols-2 lg:grid-cols-4">
                <div><p class="text-xs text-gray-500">Nama</p><p class="mt-1 font-semibold">{{ $enrollment->manpower->nama }}</p></div>
                <div><p class="text-xs text-gray-500">Jabatan</p><p class="mt-1 font-semibold">{{ $enrollment->manpower->jabatan ?? $enrollment->manpower->bagian }}</p></div>
                <div><p class="text-xs text-gray-500">Bagian</p><p class="mt-1 font-semibold">{{ $enrollment->manpower->departemen }}</p></div>
                <div><p class="text-xs text-gray-500">Training</p><p class="mt-1 font-semibold">{{ $enrollment->training->judul }} · Level {{ $enrollment->training->level }}</p></div>
            </div>
        </x-card>

        <form action="{{ route('admin.ojt.store', $enrollment) }}" method="POST" class="space-y-5">
            @csrf

            <x-card>
                <x-slot name="header"><h2 class="font-heading text-lg font-bold">Pelaksanaan</h2></x-slot>
                <div class="grid gap-4 md:grid-cols-3">
                    <div><label class="mb-2 block text-sm font-medium" for="training_date">Tanggal</label><x-input id="training_date" name="training_date" type="date" :value="old('training_date', $assessment?->training_date?->format('Y-m-d') ?? now()->format('Y-m-d'))" /></div>
                    <div><label class="mb-2 block text-sm font-medium" for="training_time">Jam</label><x-input id="training_time" name="training_time" type="time" :value="old('training_time', $assessment?->training_time)" /></div>
                    <div><label class="mb-2 block text-sm font-medium" for="location">Tempat</label><x-input id="location" name="location" :value="old('location', $assessment?->location)" placeholder="Contoh: Line Assembly 1" /></div>
                </div>

                <fieldset class="mt-5">
                    <legend class="mb-2 text-sm font-medium">Kategori pelatihan</legend>
                    <div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-5">
                        @foreach (['pengetahuan_produk' => 'Pengetahuan training', 'pengetahuan_proses' => 'Pengetahuan proses', 'pengoperasian_mesin' => 'Pengoperasian mesin', 'proses_kerja' => 'Proses kerja', 'lainnya' => 'Lain-lain'] as $value => $label)
                            <label class="flex items-center gap-2 rounded-md border border-gray-200 px-3 py-2 text-sm dark:border-gray-700">
                                <input type="checkbox" name="categories[]" value="{{ $value }}" @checked(in_array($value, old('categories', $assessment?->categories ?? []))) class="rounded border-gray-300 text-brand-dark focus:ring-brand-light">
                                {{ $label }}
                            </label>
                        @endforeach
                    </div>
                    @error('categories')<p class="mt-2 text-xs text-red-600">{{ $message }}</p>@enderror
                </fieldset>

                <div class="mt-5 grid gap-4 lg:grid-cols-2">
                    <div><label class="mb-2 block text-sm font-medium" for="tools">Alat peraga</label><x-textarea id="tools" name="tools" placeholder="Mesin, alat ukur, dokumen, dan lainnya">{{ old('tools', $assessment?->tools) }}</x-textarea></div>
                    <div>
                        <p class="mb-2 text-sm font-medium">Materi training</p>
                        <div class="space-y-2">
                            @for ($index = 0; $index < 3; $index++)
                                <x-input name="materials[]" :value="old('materials.'.$index, $assessment?->materials[$index] ?? null)" placeholder="Materi {{ $index + 1 }}" />
                            @endfor
                        </div>
                    </div>
                </div>
            </x-card>

            <x-card>
                <x-slot name="header"><div><h2 class="font-heading text-lg font-bold">Evaluasi teori</h2><p class="mt-1 text-xs text-gray-500">Nilai maksimal total 100. Standar lulus minimal 75.</p></div></x-slot>
                <x-table>
                    <x-slot name="head"><tr><th class="w-12">No.</th><th>Tes teori / soal</th><th class="w-20 text-center">Maks.</th><th>Jawaban manpower</th><th class="w-28">Nilai</th></tr></x-slot>
                    @foreach ($theoryQuestions as $key => $question)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td class="min-w-72">{{ $question['label'] }}</td>
                            <td class="text-center">{{ $question['maximum'] }}</td>
                            <td class="min-w-80"><x-textarea name="theory[{{ $key }}][answer]" class="min-h-20">{{ old('theory.'.$key.'.answer', data_get($assessment?->theory_answers, $key.'.answer')) }}</x-textarea></td>
                            <td><x-input name="theory[{{ $key }}][score]" type="number" min="0" max="{{ $question['maximum'] }}" step="0.01" :value="old('theory.'.$key.'.score', data_get($assessment?->theory_answers, $key.'.score'))" /></td>
                        </tr>
                    @endforeach
                </x-table>
            </x-card>

            <x-card>
                <x-slot name="header"><div><h2 class="font-heading text-lg font-bold">Tes praktik</h2><p class="mt-1 text-xs text-gray-500">Nilai maksimal total 100. Standar lulus minimal 75.</p></div></x-slot>
                <x-table>
                    <x-slot name="head"><tr><th>Standar</th><th class="w-24 text-center">Maks.</th><th>Pencapaian aktual</th><th class="w-28">Nilai</th></tr></x-slot>
                    @foreach ($practiceItems as $key => $item)
                        <tr>
                            <td class="min-w-56">{{ $item['label'] }}</td>
                            <td class="text-center">{{ $item['maximum'] }}</td>
                            <td class="min-w-96"><x-textarea name="practice[{{ $key }}][actual]" class="min-h-20">{{ old('practice.'.$key.'.actual', data_get($assessment?->practice_results, $key.'.actual')) }}</x-textarea></td>
                            <td><x-input name="practice[{{ $key }}][score]" type="number" min="0" max="{{ $item['maximum'] }}" step="0.01" :value="old('practice.'.$key.'.score', data_get($assessment?->practice_results, $key.'.score'))" /></td>
                        </tr>
                    @endforeach
                </x-table>
            </x-card>

            <x-card>
                <x-slot name="header"><h2 class="font-heading text-lg font-bold">Kesimpulan dan saran</h2></x-slot>
                <div class="grid gap-4 lg:grid-cols-2">
                    <div><label class="mb-2 block text-sm font-medium" for="conclusion">Kesimpulan Leader</label><x-textarea id="conclusion" name="conclusion" placeholder="Ringkasan hasil OJT">{{ old('conclusion', $assessment?->conclusion) }}</x-textarea></div>
                    <div><label class="mb-2 block text-sm font-medium" for="recommendation">Saran untuk manpower</label><x-textarea id="recommendation" name="recommendation" placeholder="Saran pembinaan atau perbaikan">{{ old('recommendation', $assessment?->recommendation) }}</x-textarea></div>
                </div>
                <p class="mt-4 text-xs text-gray-500">Sistem menyatakan lulus bila nilai teori dan tes praktik masing-masing minimal 75. Hasil lulus otomatis menetapkan Balok 2 pada training ini.</p>
            </x-card>

            <div class="flex justify-end gap-3">
                <x-button :href="route('admin.ojt.index')" variant="outline"><x-icon name="close" class="h-4 w-4" />Batal</x-button>
                <x-button type="submit"><x-icon name="save" class="h-4 w-4" />Simpan hasil OJT</x-button>
            </div>
        </form>
    </div>
@endsection
