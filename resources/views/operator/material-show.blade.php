@extends('layouts.app')

@section('title', $training->judul . ' - Modul Pembelajaran Pojok Ilmu')

@section('content')


<div class="page-shell !max-w-5xl">
    
    @php
        $totalMaterials = $training->materials->count();
        $completedMaterials = $completedMaterialIds->count();
        $materialProgress = $totalMaterials === 0 ? 0 : (int) round(($completedMaterials / $totalMaterials) * 100);
    @endphp

    <div>
        <a href="{{ route('operator.dashboard') }}" class="text-xs font-bold text-gray-500 hover:text-brand-dark flex items-center gap-1 mb-2">
            &larr; Kembali ke Dashboard Saya
        </a>
        <div class="flex items-center gap-2 mb-1">
            <span class="px-2.5 py-1 text-xs font-mono font-bold rounded-lg bg-green-100 dark:bg-green-950/60 text-brand-dark dark:text-green-400">
                {{ $training->kode_training }}
            </span>
            <span class="px-2.5 py-1 text-xs font-bold rounded-lg bg-blue-100 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300">
                Syarat Level {{ $training->level }}
            </span>
        </div>
        <h1 class="page-title">{{ $training->judul }}</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400">Produk Level {{ $training->level }} &middot; Selesaikan setiap materi sebelum mengambil post-test.</p>
    </div>

    <x-card bodyClass="p-5" data-learning-progress data-current-completed="{{ $completedMaterials }}" data-total-materials="{{ $totalMaterials }}">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div><p class="text-sm font-semibold">Progress sub-course</p><p class="mt-1 text-xs text-gray-500"><span data-completed-materials>{{ $completedMaterials }}</span> dari <span data-total-materials>{{ $totalMaterials }}</span> materi selesai</p></div>
            <span data-progress-percent class="text-lg font-semibold text-brand-dark dark:text-green-400">{{ $materialProgress }}%</span>
        </div>
        <div class="mt-3 h-2 overflow-hidden rounded-sm bg-gray-100 dark:bg-gray-700"><div data-progress-bar class="h-full rounded-sm bg-brand-dark dark:bg-green-500" style="width: {{ $materialProgress }}%"></div></div>
    </x-card>

    <!-- Materials Content List -->
    <div class="space-y-6">
        @forelse($training->materials as $mat)
            <x-card class="shadow-none border border-gray-100 dark:border-gray-700 rounded-lg overflow-hidden" data-material-progress data-progress-recorded="{{ $completedMaterialIds->contains($mat->id) ? 'true' : 'false' }}" data-progress-url="{{ route('operator.materials.item.complete', [$training->id, $mat->id]) }}">
                <x-slot name="header">
                    <div class="flex items-center justify-between gap-3 flex-wrap">
                        <div class="flex items-center gap-3">
                        <span class="w-8 h-8 rounded-lg bg-brand-dark text-white font-semibold text-sm flex items-center justify-center shadow-none">
                            {{ $mat->urutan }}
                        </span>
                        <h3 class="font-bold font-heading text-lg text-gray-900 dark:text-gray-100">{{ $mat->judul }}</h3>
                        </div>
                        @if($completedMaterialIds->contains($mat->id))
                            <span class="inline-flex items-center gap-1 rounded-md bg-green-100 px-2 py-1 text-xs font-semibold text-brand-dark dark:bg-green-950 dark:text-green-300"><x-icon name="check" class="h-3.5 w-3.5" /> Selesai</span>
                        @else
                            <span data-material-status class="rounded-md bg-gray-100 px-2 py-1 text-xs font-medium text-gray-600 dark:bg-gray-700 dark:text-gray-300">Sedang dipelajari</span>
                        @endif
                    </div>
                </x-slot>

                <div class="space-y-4">
                    @if($mat->konten_teks)
                        <div class="prose dark:prose-invert max-w-none text-sm text-gray-700 dark:text-gray-300 leading-relaxed">
                            {!! nl2br(e($mat->konten_teks)) !!}
                        </div>
                    @endif

                    <!-- Embedded Video Player (Google Drive / YouTube) -->
                    @if($mat->tipe_media === 'video_gdrive' || $mat->tipe_media === 'video_youtube')
                        <div class="pt-2">
                            <div class="relative w-full aspect-video rounded-lg bg-black overflow-hidden shadow-none border border-gray-800">
                                <iframe title="Video pembelajaran" loading="lazy" src="{{ $mat->embed_url }}" class="w-full h-full border-0" allow="autoplay" allowfullscreen></iframe>
                            </div>
                            <p class="text-[11px] text-gray-400 text-center mt-2">Tekan tombol putar untuk menonton video pembelajaran.</p>
                        </div>
                    @elseif($mat->tipe_media === 'image' && $mat->media_url)
                        <div class="pt-2 flex justify-center">
                            <img src="{{ $mat->media_url }}" alt="Materi Gambar" class="max-h-96 rounded-lg object-contain shadow-none border border-gray-200 dark:border-gray-700">
                        </div>
                    @elseif($mat->tipe_media === 'pdf' && $mat->media_url)
                        <x-button :href="$mat->media_url" target="_blank" rel="noopener" variant="outline">Buka dokumen PDF <x-icon name="arrow" /></x-button>
                    @endif
                </div>
            </x-card>
        @empty
            <x-card>
                <x-empty-state title="Materi Belum Tersedia" description="Modul materi pelatihan sedang disiapkan oleh Admin." />
            </x-card>
        @endforelse
    </div>

    @if($training->postTest && $training->postTest->questions->isNotEmpty())
        <div class="p-6 rounded-lg bg-gradient-to-r from-green-100 to-brand-surface dark:from-gray-800 dark:to-gray-900 border-2 border-brand-light/40 flex flex-col sm:flex-row items-center justify-between gap-4 shadow-none">
            <div>
                <h4 class="font-bold font-heading text-lg text-brand-dark dark:text-green-400">Lanjut ke post-test</h4>
                <p class="text-xs text-gray-600 dark:text-gray-400">{{ $completedMaterials }} dari {{ $totalMaterials }} materi selesai · Nilai lulus {{ $training->postTest->passing_grade }}% · Durasi {{ $training->postTest->durasi_menit }} menit</p>
            </div>
            @if($enrollment->next_attempt_at?->isFuture())
                <p class="text-xs font-medium text-amber-700 dark:text-amber-300">Post-test tersedia kembali: {{ $enrollment->next_attempt_at->translatedFormat('d M Y, H:i') }}</p>
            @else
                <form action="{{ route('operator.materials.complete', $training->id) }}" method="POST" data-post-test-form @class(['hidden' => $totalMaterials !== 0 && $completedMaterials !== $totalMaterials])>
                    @csrf
                    <x-button type="submit">Mulai post-test <x-icon name="arrow" /></x-button>
                </form>
                <p data-post-test-locked @class(['hidden' => $totalMaterials === 0 || $completedMaterials === $totalMaterials, 'text-xs font-medium text-amber-700 dark:text-amber-300'])>Selesaikan semua materi terlebih dahulu.</p>
            @endif
        </div>
    @endif
</div>
@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
        const watchedMaterials = document.querySelectorAll('[data-material-progress]');
        const progress = document.querySelector('[data-learning-progress]');
        const timers = new WeakMap();
        let completedMaterials = Number(progress?.dataset.currentCompleted || 0);
        const totalMaterials = Number(progress?.dataset.totalMaterials || 0);

        const updateProgress = () => {
            const percentage = totalMaterials === 0 ? 0 : Math.round((completedMaterials / totalMaterials) * 100);
            document.querySelectorAll('[data-completed-materials]').forEach((element) => element.textContent = completedMaterials);
            document.querySelectorAll('[data-progress-percent]').forEach((element) => element.textContent = `${percentage}%`);
            document.querySelectorAll('[data-progress-bar]').forEach((element) => element.style.width = `${percentage}%`);

            if (totalMaterials === 0 || completedMaterials >= totalMaterials) {
                document.querySelector('[data-post-test-form]')?.classList.remove('hidden');
                document.querySelector('[data-post-test-locked]')?.classList.add('hidden');
            }
        };

        const recordProgress = async (element) => {
            if (element.dataset.progressRecorded === 'true') return;

            const response = await fetch(element.dataset.progressUrl, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
            });

            if (!response.ok) return;

            element.dataset.progressRecorded = 'true';
            completedMaterials++;
            updateProgress();
            const status = element.querySelector('[data-material-status]');
            if (status) {
                status.textContent = 'Aktivitas tercatat';
                status.className = 'rounded-md bg-green-100 px-2 py-1 text-xs font-semibold text-brand-dark dark:bg-green-950 dark:text-green-300';
            }
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                const element = entry.target;
                if (element.dataset.progressRecorded === 'true') return;

                if (entry.isIntersecting) {
                    timers.set(element, window.setTimeout(() => recordProgress(element), 12000));
                } else if (timers.has(element)) {
                    window.clearTimeout(timers.get(element));
                    timers.delete(element);
                }
            });
        }, { threshold: 0.6 });

        watchedMaterials.forEach((material) => {
            if (material.dataset.progressRecorded !== 'true') observer.observe(material);
        });
    });
</script>
@endpush
@endsection
