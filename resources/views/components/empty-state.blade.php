@props(['title' => 'Belum ada data', 'description' => 'Data akan ditampilkan di sini setelah tersedia.'])
<div {{ $attributes->class(['flex flex-col items-center px-4 py-10 text-center']) }}>
    <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-lg border border-gray-200 bg-gray-50 text-gray-400 dark:border-gray-700 dark:bg-gray-900"><x-icon name="book" class="h-6 w-6" /></div>
    <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-100">{{ $title }}</h3>
    <p class="mt-2 max-w-md text-sm leading-6 text-gray-500 dark:text-gray-400">{{ $description }}</p>
    @if(!$slot->isEmpty())<div class="mt-4">{{ $slot }}</div>@endif
</div>
