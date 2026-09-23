@props([
    'disabled' => false,
    'error' => false,
    'label' => false
])

@php
    $baseClasses = 'w-full px-4 py-3 pr-12 bg-gray-50 dark:bg-gray-800 border rounded-xl focus-visible:ring-2 focus-visible:outline-none transition-colors duration-200 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500';
    
    if ($error) {
        $classes = $baseClasses . ' border-red-300 focus-visible:border-red-500 focus-visible:ring-red-200 dark:border-red-600 dark:focus-visible:ring-red-900/50';
    } else {
        $classes = $baseClasses . ' border-gray-200 dark:border-gray-700 focus-visible:border-brand-light focus-visible:ring-brand-light/30 dark:focus-visible:ring-brand-light/20';
    }
@endphp

<div class="w-full">
    @if($label)
        <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-100">{{ $label }}</label>
    @endif
    <div x-data="{ show: false }" class="relative w-full">
        <input :type="show ? 'text' : 'password'" 
               {{ $disabled ? 'disabled' : '' }} 
               {!! $attributes->merge(['class' => $classes]) !!}>
    
    <button type="button" 
            @click="show = !show" 
            class="absolute inset-y-0 right-0 flex items-center pr-4 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-light rounded-r-xl transition-colors group"
            :aria-label="show ? 'Sembunyikan password' : 'Tampilkan password'">
        
        <!-- Eye Icon (Tampilkan) -->
        <svg x-show="!show" class="w-5 h-5 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
        </svg>
        
        <!-- Eye Slash Icon (Sembunyikan) -->
        <svg x-show="show" style="display: none;" class="w-5 h-5 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path>
        </svg>
    </button>
    </div>
</div>
