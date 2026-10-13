@props(['name' => 'book'])
@php
    $paths = [
        'book' => 'M12 6v14m0-14C8 3 4 4 2 5v14c4-2 7-1 10 1 3-2 6-3 10-1V5c-4-2-7-1-10 1Z',
        'clipboard' => 'M9 5h6m-5-2h4a2 2 0 0 1 2 2v1h2a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h2V5a2 2 0 0 1 2-2Zm-1 9 2 2 4-4',
        'grid' => 'M3 3h7v7H3zM14 3h7v7h-7zM3 14h7v7H3zM14 14h7v7h-7z',
        'chart' => 'M4 20V10h4v10m4 0V4h4v16m4 0V7h1v13M2 20h20',
        'check' => 'm5 12 4 4L19 6',
        'plus' => 'M12 5v14M5 12h14',
        'trash' => 'M3 6h18M9 6V3h6v3M5 6l1 15h12l1-15M10 10v7m4-7v7',
        'arrow' => 'M4 12h16m-6-6 6 6-6 6',
        'chevron-down' => 'm6 9 6 6 6-6',
        'filter' => 'M4 5h16M7 12h10m-7 7h4',
        'reset' => 'M3 12a9 9 0 1 0 3-6.7M3 4.5v5h5',
        'download' => 'M12 3v12m0 0 4-4m-4 4-4-4M5 21h14',
        'upload' => 'M12 21V9m0 0 4 4m-4-4-4 4M5 3h14',
        'save' => 'M5 3h12l2 2v16H5V3Zm3 0v6h7V3m-7 18v-7h8v7',
        'pencil' => 'm16.86 3.49 3.65 3.65M4 20l4.29-.86L19.8 7.63a2.58 2.58 0 0 0-3.65-3.65L4.64 15.49 4 20Z',
        'search' => 'm21 21-4.35-4.35M19 11a8 8 0 1 1-16 0 8 8 0 0 1 16 0Z',
        'back' => 'M19 12H5m6 6-6-6 6-6',
        'document' => 'M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Zm0 0v6h6M8 13h8m-8 4h5',
        'menu' => 'M4 6h16M4 12h16M4 18h16',
        'close' => 'm6 6 12 12M6 18 18 6',
        'user' => 'M16 7a4 4 0 1 1-8 0 4 4 0 0 1 8 0ZM4 21v-2a8 8 0 0 1 16 0v2',
        'logout' => 'M9 4H4v16h5m6-13 5 5-5 5M8 12h12',
        'moon' => 'M20 15A9 9 0 0 1 9 4a9 9 0 1 0 11 11Z',
        'scan' => 'M3 8V3h5m8 0h5v5M3 16v5h5m8 0h5v-5M7 8v8m4-8v8m3-8v8m3-8v8',
        'eye' => 'M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Zm13 0a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z',
    ];
@endphp
<svg {{ $attributes->class(['h-5 w-5 shrink-0']) }} aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="{{ $paths[$name] ?? $paths['book'] }}" /></svg>
