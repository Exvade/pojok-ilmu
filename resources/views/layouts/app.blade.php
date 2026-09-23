<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', config('app.name', 'MWT Starter Kit'))</title>
    
    <link rel="icon" type="image/webp" href="{{ asset('assets/images/logo/logo-square.webp') }}">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Prevent Dark Mode FOUC (Flash of Unstyled Content) -->
    <script>
        if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>
    

    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        :root {
            --font-sans: 'Inter', sans-serif;
            --font-heading: 'Outfit', sans-serif;
        }
        body { font-family: var(--font-sans); }
        h1, h2, h3, h4, h5, h6, .font-heading { font-family: var(--font-heading); }
    </style>
</head>
<body x-data="{ 
        darkMode: localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)
      }" 
      x-init="$watch('darkMode', val => { localStorage.setItem('theme', val ? 'dark' : 'light'); if(val) { document.documentElement.classList.add('dark'); } else { document.documentElement.classList.remove('dark'); } })" 
      :class="{ 'dark': darkMode }"
      class="bg-brand-surface dark:bg-gray-900 font-sans text-brand-text dark:text-gray-100 flex flex-col min-h-screen transition-colors duration-300">
    
    <!-- Flash Messages via Native Toast -->
    <x-toast />
    <x-confirm-modal />
    @if(session('success'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'success', title: 'Berhasil!', message: '{{ session('success') }}' } }));
            });
        </script>
    @endif

    @if(session('error'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'error', title: 'Gagal!', message: '{{ session('error') }}' } }));
            });
        </script>
    @endif

    <main class="flex-grow">
        @yield('content')
    </main>

</body>
</html>
