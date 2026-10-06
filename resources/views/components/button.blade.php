@props(['variant' => 'primary', 'type' => 'button', 'size' => 'md', 'href' => null])
@php
    $sizes = ['sm' => 'min-h-9 px-3 py-2 text-xs', 'md' => 'min-h-10 px-4 py-2 text-sm', 'lg' => 'min-h-11 px-5 py-3 text-sm'];
    $variants = [
        'primary' => 'border-transparent bg-brand-dark text-white hover:bg-green-900 dark:bg-green-700 dark:hover:bg-green-600',
        'secondary' => 'border-green-200 bg-green-50 text-brand-dark hover:bg-green-100 dark:border-green-800 dark:bg-green-950 dark:text-green-200',
        'danger' => 'border-transparent bg-red-600 text-white hover:bg-red-700',
        'outline' => 'border-gray-300 bg-white text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700',
        'ghost' => 'border-transparent text-gray-500 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800',
    ];
    $classes = 'inline-flex shrink-0 items-center justify-center gap-2 rounded-md border font-semibold transition-colors focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-green-600 disabled:opacity-50 disabled:cursor-not-allowed '.($sizes[$size] ?? $sizes['md']).' '.($variants[$variant] ?? $variants['primary']);
@endphp
@if($href)
    <a href="{{ $href }}" {{ $attributes->class([$classes]) }}>{{ $slot }}</a>
@else
    <button type="{{ $type }}" {{ $attributes->class([$classes]) }}>{{ $slot }}</button>
@endif
