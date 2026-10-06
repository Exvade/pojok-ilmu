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
<body x-data="appShell" class="min-h-dvh bg-brand-surface font-sans text-brand-text antialiased dark:bg-gray-950 dark:text-gray-100">
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
