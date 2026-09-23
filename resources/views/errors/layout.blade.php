<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') - {{ config('app.name', 'MWT Portal') }}</title>
    <link rel="icon" type="image/webp" href="{{ asset('assets/images/logo/logo-square.webp') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --font-sans: 'Inter', sans-serif;
            --font-heading: 'Outfit', sans-serif;
        }
        body { font-family: var(--font-sans); }
        .font-heading { font-family: var(--font-heading); }
    </style>
</head>
<body class="bg-gray-50 text-gray-900 antialiased min-h-screen flex items-center justify-center p-6">
    <div class="max-w-xl w-full text-center">
        <div class="mb-8 flex justify-center">
            <img src="{{ asset('assets/images/logo/logo-square.webp') }}" alt="MWT Logo" class="w-16 h-16 rounded-2xl shadow-sm">
        </div>
        
        <h1 class="text-7xl md:text-9xl font-heading font-extrabold text-gray-200 mb-4 tracking-tighter">
            @yield('code')
        </h1>
        
        <h2 class="text-2xl md:text-3xl font-heading font-bold text-brand-dark mb-4">
            @yield('message')
        </h2>
        
        <p class="text-gray-500 mb-8 text-lg">
            @yield('description', 'Maaf, sepertinya ada masalah saat memproses permintaan Anda.')
        </p>
        
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="{{ url('/') }}" class="inline-flex items-center justify-center px-6 py-3 font-semibold rounded-xl text-white bg-brand-dark hover:bg-green-900 transition-colors shadow-md">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                Kembali ke Beranda
            </a>
            @if(URL::previous() != url('/'))
                <button onclick="window.history.back()" class="inline-flex items-center justify-center px-6 py-3 font-semibold rounded-xl text-brand-dark bg-green-50 hover:bg-green-100 transition-colors border border-green-200">
                    Kembali ke Halaman Sebelumnya
                </button>
            @endif
        </div>
        
        <div class="mt-12 text-sm text-gray-400">
            &copy; {{ date('Y') }} PT Mada Wikri Tunggal. All rights reserved.
        </div>
    </div>
</body>
</html>
