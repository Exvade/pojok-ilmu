@props([
    'variant' => 'primary',
    'type' => 'button',
    'size' => 'md',
])

@php
    $baseClasses = 'inline-flex items-center justify-center gap-2 font-semibold rounded-xl transition-all duration-300 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 transform hover:-translate-y-0.5 shadow-md hover:shadow-lg';

    $sizeClasses = [
        'sm' => 'px-4 py-2 text-sm',
        'md' => 'px-6 py-2.5 text-base',
        'lg' => 'px-8 py-3.5 text-lg',
    ][$size];

    $variantClasses = [
        'primary' => 'text-white bg-brand-dark hover:bg-green-900 focus-visible:ring-brand-light shadow-green-900/20',
        'secondary' => 'text-brand-dark bg-green-100 hover:bg-green-200 focus-visible:ring-green-500 shadow-green-900/10',
        'danger' => 'text-white bg-red-600 hover:bg-red-700 focus-visible:ring-red-500 shadow-red-900/20',
        'outline' => 'text-gray-700 bg-white border border-gray-300 hover:bg-gray-50 focus-visible:ring-gray-500 shadow-gray-900/5',
    ][$variant];

    $classes = $baseClasses . ' ' . $sizeClasses . ' ' . $variantClasses;
@endphp

<button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</button>
