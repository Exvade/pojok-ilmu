@props(['value' => 0, 'showLabel' => true])

@php
    $achieved = max(0, min(4, (int) $value));
    $label = match ($achieved) {
        1 => 'Lulus post-test',
        2 => 'Bisa mengerjakan dengan bimbingan',
        3 => 'Balok 3 belum dikembangkan',
        4 => 'Balok 4 belum dikembangkan',
        default => 'Belum mulai',
    };
@endphp

<span {{ $attributes->class(['inline-flex items-center gap-1.5']) }} aria-label="{{ $achieved }} dari 4 balok tercapai">
    <span class="grid grid-cols-2 overflow-hidden border border-gray-300 dark:border-gray-600" aria-hidden="true">
        @foreach (range(1, 4) as $block)
            <span @class([
                'h-4 w-6 border-[0.5px] border-gray-300 transition-colors sm:h-5 sm:w-7 dark:border-gray-600',
                'border-brand-dark bg-brand-dark dark:border-green-400 dark:bg-green-400' => $block <= $achieved,
                'bg-white dark:bg-gray-900' => $block > $achieved,
            ])></span>
        @endforeach
    </span>
    @if ($showLabel)
        <span class="text-xs font-semibold text-gray-600 dark:text-gray-300">{{ $label }}</span>
    @endif
</span>
