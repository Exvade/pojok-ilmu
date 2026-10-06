<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') · Pojok Ilmu</title>
    <link rel="icon" type="image/webp" href="{{ asset('assets/images/logo/logo-square.webp') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-dvh items-center justify-center bg-brand-surface p-5 font-sans text-brand-text antialiased">
    <main class="w-full max-w-lg rounded-lg border border-gray-200 bg-white p-6 sm:p-10">
        <img src="{{ asset('assets/images/logo/logo-landscape.webp') }}" alt="PT Mada Wikri Tunggal" class="mb-10 w-56">
        <p class="mb-3 text-sm font-semibold uppercase tracking-widest text-brand-dark">Kesalahan @yield('code')</p>
        <h1 class="mb-3 text-2xl font-semibold tracking-tight">@yield('message')</h1>
        <p class="mb-8 text-sm leading-6 text-gray-500">@yield('description', 'Permintaan belum dapat diproses. Silakan coba kembali.')</p>
        <div class="flex flex-wrap gap-3">
            <x-button :href="url('/')" variant="primary">Kembali ke beranda</x-button>
            <x-button variant="outline" onclick="window.history.back()">Halaman sebelumnya</x-button>
        </div>
        <p class="mt-10 border-t border-gray-100 pt-5 text-xs text-gray-500">Pojok Ilmu · PT Mada Wikri Tunggal</p>
    </main>
</body>
</html>
