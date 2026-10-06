@props(['bodyClass' => 'p-5 sm:p-6'])
<section {{ $attributes->class(['min-w-0 rounded-lg border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800']) }}>
    @isset($header)
        <div class="border-b border-gray-200 px-5 py-4 dark:border-gray-700 sm:px-6">{{ $header }}</div>
    @endisset
    <div class="{{ $bodyClass }}">{{ $slot }}</div>
    @isset($footer)
        <div class="border-t border-gray-200 px-5 py-4 dark:border-gray-700 sm:px-6">{{ $footer }}</div>
    @endisset
</section>
