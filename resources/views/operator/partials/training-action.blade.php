@if(! $enrollment->is_unlocked)
    <span class="text-xs leading-5 text-gray-500">Terkunci. Selesaikan level kompetensi 2 untuk: {{ implode(', ', $enrollment->blocking_training_titles ?? []) }}.</span>
@elseif($enrollment->block === 2)
    <x-button :href="route('operator.materials.show', $enrollment->training_id)" variant="outline" size="sm"><x-icon name="eye" class="h-4 w-4" />Lihat training</x-button>
@elseif($enrollment->block === 1)
    @if($enrollment->ojtAssessment)
        <x-button :href="route('operator.ojt-result', $enrollment->id)" variant="outline" size="sm"><x-icon name="eye" class="h-4 w-4" />Lihat hasil OJT</x-button>
    @else
        <span class="text-xs text-amber-800 dark:text-amber-200">Menunggu OJT dari penilai.</span>
    @endif
@elseif(! $enrollment->is_training_ready)
    <span class="text-xs leading-5 text-gray-500" data-training-preparing>Materi sedang disiapkan. Training bisa dimulai setelah pengelola melengkapi isinya.</span>
@elseif($enrollment->training->preTest && ! $enrollment->pre_test_completed_at)
    <x-button :href="route('operator.quizzes.show', $enrollment->training->preTest->id)" size="sm">Training sekarang <x-icon name="arrow" class="h-4 w-4" /></x-button>
@elseif($enrollment->next_attempt_at?->isFuture())
    <span class="text-xs text-amber-800 dark:text-amber-200">Bisa mencoba lagi {{ $enrollment->next_attempt_at->translatedFormat('d M, H:i') }}</span>
@else
    <x-button :href="route('operator.materials.show', $enrollment->training_id)" size="sm">Training sekarang <x-icon name="arrow" class="h-4 w-4" /></x-button>
@endif
