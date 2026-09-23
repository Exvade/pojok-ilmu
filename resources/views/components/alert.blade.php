@props(['type' => 'info', 'title' => null])

@php
    $typeClasses = [
        'info' => 'bg-blue-50 border-blue-200 text-blue-800 dark:bg-blue-900/30 dark:border-blue-800 dark:text-blue-300',
        'success' => 'bg-green-50 border-brand-light/30 text-brand-dark dark:bg-green-900/30 dark:border-green-800 dark:text-green-300',
        'warning' => 'bg-yellow-50 border-yellow-200 text-yellow-800 dark:bg-yellow-900/30 dark:border-yellow-800 dark:text-yellow-300',
        'danger' => 'bg-red-50 border-red-200 text-red-800 dark:bg-red-900/30 dark:border-red-800 dark:text-red-300',
    ][$type];

    $iconPaths = [
        'info' => 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
        'success' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
        'warning' => 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z',
        'danger' => 'M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
    ][$type];
@endphp

<div {{ $attributes->merge(['class' => "border-l-4 p-4 rounded-r-xl shadow-sm mb-4 $typeClasses"]) }} role="alert">
    <div class="flex items-start">
        <div class="flex-shrink-0">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $iconPaths }}"></path>
            </svg>
        </div>
        <div class="ml-3">
            @if($title)
                <h3 class="text-sm font-bold mb-1">{{ $title }}</h3>
            @endif
            <div class="text-sm">
                {{ $slot }}
            </div>
        </div>
    </div>
</div>
