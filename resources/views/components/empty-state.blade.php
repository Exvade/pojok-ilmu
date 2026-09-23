@props([
    'title' => 'Belum Ada Data',
    'description' => 'Data yang Anda cari tidak ditemukan atau belum ditambahkan ke dalam sistem.',
])

<div {{ $attributes->merge(['class' => 'flex flex-col items-center justify-center p-8 lg:p-12 text-center rounded-2xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 shadow-sm']) }}>
    
    @if(isset($icon))
        <div class="p-4 mb-4 text-brand-dark dark:text-brand-light bg-green-50 dark:bg-green-900/20 rounded-full">
            {{ $icon }}
        </div>
    @else
        <!-- Default Icon (Inbox/Empty Box) -->
        <div class="p-4 mb-4 text-gray-400 dark:text-gray-500 bg-gray-50 dark:bg-gray-800 rounded-full">
            <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
            </svg>
        </div>
    @endif
    
    <h3 class="mb-2 text-lg font-bold text-gray-900 dark:text-white">{{ $title }}</h3>
    <p class="mb-6 text-sm text-gray-500 dark:text-gray-400 max-w-sm">{{ $description }}</p>
    
    @if(isset($action))
        <div>
            {{ $action }}
        </div>
    @endif
</div>
