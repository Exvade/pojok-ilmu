@props(['value' => 0, 'showLabel' => true])

@php
    $achieved = max(0, min(\App\Models\Training::MAX_COMPETENCY_LEVEL, (int) $value));
    $label = match ($achieved) {
        1 => 'Lulus post-test',
        2 => 'Mampu mengerjakan dengan bimbingan',
        3, 4 => 'Belum dikembangkan',
        default => 'Belum mulai',
    };
@endphp

<span {{ $attributes->class(['inline-flex items-center gap-2']) }} aria-label="Level kompetensi {{ $achieved }} dari {{ \App\Models\Training::MAX_COMPETENCY_LEVEL }}">
    <span @class([
        'inline-flex h-7 min-w-7 items-center justify-center rounded-md px-2 text-sm font-bold',
        'bg-brand-dark text-white dark:bg-green-500 dark:text-gray-950' => $achieved >= 2,
        'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-200' => $achieved === 1,
        'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-300' => $achieved === 0,
    ]) aria-hidden="true">{{ $achieved }}</span>
    @if ($showLabel)
        <span class="text-xs font-semibold text-gray-600 dark:text-gray-300">{{ $label }}</span>
    @endif
</span>
