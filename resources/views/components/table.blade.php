@props(['headers' => []])
<div {{ $attributes->class(['min-w-0 overflow-x-auto rounded-md border border-gray-200 dark:border-gray-700']) }} tabindex="0" role="region" aria-label="Tabel data, geser untuk melihat semua kolom">
    <table class="data-table">
        <thead>
            @isset($head) {{ $head }}
            @else <tr>@isset($thead) {{ $thead }} @else @foreach($headers as $header)<th scope="col">{{ $header }}</th>@endforeach @endisset</tr>
            @endisset
        </thead>
        <tbody>{{ $slot }}</tbody>
    </table>
</div>
