<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Pojok Ilmu · MWT')</title>
    <link rel="icon" type="image/webp" href="{{ asset('assets/images/logo/logo-square.webp') }}">
    <script>try { document.documentElement.classList.toggle('dark', localStorage.getItem('theme') === 'dark'); } catch {}</script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body x-data="appShell" @auth @if(auth()->user()->isOperator()) data-kiosk-idle-timeout="900000" data-kiosk-logout-url="{{ route('logout') }}" @endif @endauth class="min-h-dvh bg-brand-surface font-sans text-brand-text antialiased dark:bg-gray-950 dark:text-gray-100">
    <div id="interaction-lock" class="fixed inset-0 z-[120] hidden touch-none" aria-hidden="true"></div>
    <div id="page-skeleton" class="fixed inset-y-0 left-0 right-0 z-[130] hidden overflow-y-auto bg-brand-surface dark:bg-gray-950 @auth lg:left-64 @endauth" aria-live="polite" aria-label="Halaman sedang dimuat">
        <div class="min-h-16 border-b border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900"></div>
        <div class="mx-auto max-w-7xl animate-pulse space-y-6 px-4 py-8 sm:px-8">
            <div data-skeleton-screen="matrix" class="space-y-6">
                <div class="space-y-3"><div class="h-4 w-36 rounded bg-gray-200 dark:bg-gray-800"></div><div class="h-9 w-80 max-w-full rounded-lg bg-gray-200 dark:bg-gray-800"></div><div class="h-4 w-4/5 max-w-xl rounded bg-gray-200 dark:bg-gray-800"></div></div>
                <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">@foreach(range(1, 4) as $skeleton)<div class="h-32 rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900"><div class="h-4 w-28 rounded bg-gray-200 dark:bg-gray-800"></div><div class="mt-4 h-8 w-16 rounded bg-gray-100 dark:bg-gray-800"></div><div class="mt-3 h-3 w-32 rounded bg-gray-100 dark:bg-gray-800"></div></div>@endforeach</div>
                <div class="overflow-hidden rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900"><div class="space-y-4 border-b border-gray-200 p-5 dark:border-gray-800"><div class="h-6 w-56 rounded bg-gray-200 dark:bg-gray-800"></div><div class="flex flex-wrap gap-3"><div class="h-10 w-44 rounded-lg bg-gray-100 dark:bg-gray-800"></div><div class="h-10 w-48 rounded-lg bg-gray-100 dark:bg-gray-800"></div><div class="h-10 w-24 rounded-lg bg-gray-200 dark:bg-gray-800"></div></div></div>@foreach(range(1, 5) as $skeleton)<div class="grid grid-cols-[1.4fr_repeat(3,minmax(7rem,1fr))] border-b border-gray-100 p-5 last:border-b-0 dark:border-gray-800"><div class="h-5 w-3/4 rounded bg-gray-200 dark:bg-gray-800"></div><div class="mx-4 h-5 rounded bg-gray-100 dark:bg-gray-800"></div><div class="mx-4 h-5 rounded bg-gray-100 dark:bg-gray-800"></div><div class="ml-4 h-5 rounded bg-gray-100 dark:bg-gray-800"></div></div>@endforeach</div>
            </div>
            <div data-skeleton-screen="cards" class="hidden space-y-6"><div class="flex flex-wrap items-end justify-between gap-4"><div class="space-y-3"><div class="h-9 w-60 rounded-lg bg-gray-200 dark:bg-gray-800"></div><div class="h-4 w-96 max-w-full rounded bg-gray-200 dark:bg-gray-800"></div></div><div class="h-11 w-40 rounded-lg bg-gray-200 dark:bg-gray-800"></div></div><div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">@foreach(range(1, 6) as $skeleton)<div class="h-64 rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900"><div class="h-4 w-24 rounded bg-gray-200 dark:bg-gray-800"></div><div class="mt-5 h-6 w-4/5 rounded bg-gray-200 dark:bg-gray-800"></div><div class="mt-4 h-4 rounded bg-gray-100 dark:bg-gray-800"></div><div class="mt-2 h-4 w-4/5 rounded bg-gray-100 dark:bg-gray-800"></div><div class="mt-10 h-9 w-28 rounded-lg bg-gray-200 dark:bg-gray-800"></div></div>@endforeach</div></div>
            <div data-skeleton-screen="training-report" class="hidden space-y-6"><div class="flex items-end justify-between"><div class="space-y-3"><div class="h-4 w-32 rounded bg-gray-200 dark:bg-gray-800"></div><div class="h-9 w-56 rounded bg-gray-200 dark:bg-gray-800"></div><div class="h-4 w-96 max-w-full rounded bg-gray-100 dark:bg-gray-800"></div></div><div class="h-10 w-36 rounded-lg bg-gray-200 dark:bg-gray-800"></div></div><div class="grid gap-3 rounded-xl border border-gray-200 bg-white p-5 md:grid-cols-2 xl:grid-cols-[1fr_1fr_1fr_7rem_6rem] dark:border-gray-800 dark:bg-gray-900">@foreach(range(1, 5) as $skeleton)<div class="h-11 rounded-lg bg-gray-100 dark:bg-gray-800"></div>@endforeach</div><div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">@foreach(range(1, 4) as $skeleton)<div class="h-36 rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900"><div class="h-4 w-32 rounded bg-gray-200 dark:bg-gray-800"></div><div class="mt-4 h-8 w-12 rounded bg-gray-100 dark:bg-gray-800"></div><div class="mt-3 h-3 w-48 rounded bg-gray-100 dark:bg-gray-800"></div></div>@endforeach</div><div class="overflow-hidden rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900"><div class="p-5"><div class="h-6 w-52 rounded bg-gray-200 dark:bg-gray-800"></div><div class="mt-3 h-4 w-72 rounded bg-gray-100 dark:bg-gray-800"></div></div>@foreach(range(1, 4) as $skeleton)<div class="grid grid-cols-[2fr_repeat(6,1fr)] gap-5 border-t border-gray-100 p-5 dark:border-gray-800">@foreach(range(1, 7) as $cell)<div class="h-4 rounded bg-gray-100 dark:bg-gray-800"></div>@endforeach</div>@endforeach</div><div class="overflow-hidden rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900"><div class="p-5"><div class="h-6 w-44 rounded bg-gray-200 dark:bg-gray-800"></div></div>@foreach(range(1, 5) as $skeleton)<div class="grid grid-cols-[1.4fr_1.6fr_1.2fr_1fr_1fr_3rem] gap-5 border-t border-gray-100 p-5 dark:border-gray-800">@foreach(range(1, 6) as $cell)<div class="h-5 rounded bg-gray-100 dark:bg-gray-800"></div>@endforeach</div>@endforeach</div></div>
            <div data-skeleton-screen="management-table" class="hidden space-y-6"><div class="space-y-3"><div class="h-4 w-32 rounded bg-gray-200 dark:bg-gray-800"></div><div class="h-9 w-64 rounded bg-gray-200 dark:bg-gray-800"></div></div><div class="grid gap-3 rounded-xl border border-gray-200 bg-white p-5 sm:grid-cols-[1fr_auto] dark:border-gray-800 dark:bg-gray-900"><div class="h-11 rounded-lg bg-gray-100 dark:bg-gray-800"></div><div class="h-11 w-28 rounded-lg bg-gray-200 dark:bg-gray-800"></div></div><div class="overflow-hidden rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900">@foreach(range(1, 6) as $skeleton)<div class="grid grid-cols-[1.4fr_1.4fr_1.2fr_1fr] gap-6 border-b border-gray-100 p-5 last:border-b-0 dark:border-gray-800">@foreach(range(1, 4) as $cell)<div class="h-5 rounded bg-gray-100 dark:bg-gray-800"></div>@endforeach</div>@endforeach</div></div>
            <div data-skeleton-screen="follow-up" class="hidden space-y-6"><div class="space-y-3"><div class="h-4 w-32 rounded bg-gray-200 dark:bg-gray-800"></div><div class="h-9 w-64 rounded bg-gray-200 dark:bg-gray-800"></div></div><div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">@foreach(range(1, 4) as $skeleton)<div class="h-28 rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900"><div class="h-4 w-36 rounded bg-gray-200 dark:bg-gray-800"></div><div class="mt-4 h-8 w-10 rounded bg-gray-100 dark:bg-gray-800"></div></div>@endforeach</div>@foreach(range(1, 3) as $skeleton)<div class="overflow-hidden rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900"><div class="h-20 border-b border-gray-100 p-5 dark:border-gray-800"><div class="h-5 w-48 rounded bg-gray-200 dark:bg-gray-800"></div></div>@foreach(range(1, 2) as $row)<div class="grid grid-cols-4 gap-5 border-b border-gray-100 p-5 dark:border-gray-800">@foreach(range(1, 4) as $cell)<div class="h-5 rounded bg-gray-100 dark:bg-gray-800"></div>@endforeach</div>@endforeach</div>@endforeach</div>
            <div data-skeleton-screen="assignment" class="hidden space-y-6"><div class="flex flex-wrap items-end justify-between gap-4"><div class="space-y-3"><div class="h-9 w-60 rounded-lg bg-gray-200 dark:bg-gray-800"></div><div class="h-4 w-96 max-w-full rounded bg-gray-200 dark:bg-gray-800"></div></div><div class="flex gap-3"><div class="h-10 w-48 rounded-lg bg-gray-200 dark:bg-gray-800"></div><div class="h-10 w-28 rounded-lg bg-gray-200 dark:bg-gray-800"></div></div></div><div class="h-32 rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900"></div><div class="grid gap-4 md:grid-cols-2">@foreach(range(1, 4) as $skeleton)<div class="h-60 rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900"><div class="h-4 w-24 rounded bg-gray-200 dark:bg-gray-800"></div><div class="mt-4 h-6 w-3/4 rounded bg-gray-200 dark:bg-gray-800"></div><div class="mt-6 h-4 rounded bg-gray-100 dark:bg-gray-800"></div><div class="mt-3 h-4 w-4/5 rounded bg-gray-100 dark:bg-gray-800"></div><div class="mt-8 h-9 w-36 rounded-lg bg-gray-200 dark:bg-gray-800"></div></div>@endforeach</div></div>
            <div data-skeleton-screen="form" class="hidden space-y-6"><div class="space-y-3"><div class="h-9 w-64 rounded-lg bg-gray-200 dark:bg-gray-800"></div><div class="h-4 w-96 max-w-full rounded bg-gray-200 dark:bg-gray-800"></div></div><div class="rounded-xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-gray-900"><div class="grid gap-5 sm:grid-cols-2">@foreach(range(1, 6) as $skeleton)<div><div class="h-4 w-24 rounded bg-gray-200 dark:bg-gray-800"></div><div class="mt-2 h-11 rounded-lg bg-gray-100 dark:bg-gray-800"></div></div>@endforeach</div><div class="mt-7 h-32 rounded-lg bg-gray-100 dark:bg-gray-800"></div></div></div>
        </div>
    </div>
    <a href="#main-content" class="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-[100] focus:rounded focus:bg-white focus:p-3 focus:text-brand-dark">Lewati ke konten</a>
    @auth
        @include('layouts.navigation')
    @endauth
    <div class="flex min-h-dvh flex-col @auth lg:pl-64 @endauth">
        @auth
        <header class="flex min-h-16 items-center justify-between gap-3 border-b border-gray-200 bg-white px-4 dark:border-gray-800 dark:bg-gray-900 sm:px-8">
            <div class="flex min-w-0 items-center gap-3">
                <x-button variant="ghost" size="sm" class="lg:hidden" @click="openSidebar()" aria-label="Buka menu" aria-controls="app-sidebar" ::aria-expanded="sidebarOpen"><x-icon name="menu" /></x-button>
                <p class="truncate text-xs font-medium text-gray-500 dark:text-gray-400">Pojok Ilmu <span class="mx-2 text-gray-300">/</span> {{ auth()->user()->isManagement() ? 'Administrasi pelatihan' : 'Ruang belajar' }}</p>
            </div>
            <div class="flex shrink-0 items-center gap-3">
                <span class="hidden text-xs text-gray-500 sm:block">{{ auth()->user()->isManagement() ? 'Pengelola' : 'Karyawan' }}</span>
                <x-button variant="ghost" size="sm" @click="toggleTheme()" aria-label="Ganti tema terang atau gelap" ::aria-pressed="darkMode"><x-icon name="moon" /></x-button>
            </div>
        </header>
        @endauth
        <main id="main-content" class="min-w-0 flex-1" tabindex="-1">
            @if($errors->any())
                <div role="alert" class="mx-4 mt-5 rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-800 dark:border-red-900 dark:bg-red-950 dark:text-red-200 sm:mx-8">
                    <p class="font-semibold">Data belum dapat disimpan</p>
                    <ul class="mt-2 list-inside list-disc">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                </div>
            @endif
            @yield('content')
        </main>
        @auth
            <footer class="flex flex-wrap justify-between gap-2 border-t border-gray-200 px-4 py-5 text-xs text-gray-500 dark:border-gray-800 sm:px-8"><span>PT Mada Wikri Tunggal</span><span>Pojok Ilmu · Pengembangan kompetensi</span></footer>
        @endauth
    </div>
    <script id="app-feedback" type="application/json">{!! json_encode(['success' => session('success'), 'error' => session('error')], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
    @stack('scripts')
</body>
</html>
