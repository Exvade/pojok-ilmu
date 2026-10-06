@props(['title', 'description'])
<div class="grid min-h-dvh lg:grid-cols-2">
    <section class="relative hidden flex-col justify-between bg-brand-dark p-10 text-white lg:flex xl:p-16">
        <img src="{{ asset('assets/images/logo/logo-landscape.webp') }}" alt="PT Mada Wikri Tunggal" class="w-64 rounded-md bg-white p-3">
        <div class="my-16 max-w-lg">
            <p class="mb-6 text-xs font-semibold uppercase tracking-[0.2em] text-green-300">Learning & development</p>
            <h1 class="text-5xl font-semibold leading-tight tracking-tight">Kompetensi tumbuh.<br><span class="text-green-300">Masa depan maju.</span></h1>
            <p class="mt-6 text-base leading-7 text-green-100">Ruang belajar karyawan MWT. Kembangkan kemampuan melalui materi terarah, evaluasi, dan target kompetensi yang jelas.</p>
            <div class="mt-10 grid grid-cols-3 gap-5 border-t border-white/20 pt-6">
                @foreach(['01' => 'Pelajari materi', '02' => 'Uji pemahaman', '03' => 'Tingkatkan level'] as $number => $label)
                    <div><span class="text-sm text-green-300">{{ $number }}</span><p class="mt-2 text-sm font-medium">{{ $label }}</p></div>
                @endforeach
            </div>
        </div>
        <p class="text-xs text-green-200">PT Mada Wikri Tunggal · Metal and Plastic Industries</p>
    </section>
    <section class="flex min-w-0 flex-col bg-white px-6 py-8 dark:bg-gray-900 sm:px-12 lg:px-16">
        <div class="mb-8 flex items-center justify-between">
            <span class="text-xl font-bold tracking-tight text-brand-dark dark:text-green-400">Pojok Ilmu.</span>
            <x-button variant="ghost" size="sm" @click="toggleTheme()" aria-label="Ganti tema terang atau gelap"><x-icon name="moon" /></x-button>
        </div>
        <div class="mx-auto my-auto w-full max-w-md py-8">
            <img src="{{ asset('assets/images/logo/logo-landscape.webp') }}" alt="PT Mada Wikri Tunggal" class="mb-8 w-56 rounded bg-white p-2 lg:hidden">
            <h2 class="text-3xl font-semibold tracking-tight">{{ $title }}</h2>
            <p class="mb-7 mt-3 text-sm leading-6 text-gray-500 dark:text-gray-400">{{ $description }}</p>
            <nav class="mb-7 grid grid-cols-2 gap-1 rounded-md bg-gray-100 p-1 dark:bg-gray-800" aria-label="Metode masuk">
                @foreach([['login', 'user', 'Masuk dengan NIK'], ['login.scan', 'scan', 'Pindai kartu']] as [$route, $icon, $label])
                    <a href="{{ route($route) }}" @if(request()->routeIs($route)) aria-current="page" @endif class="flex items-center justify-center gap-2 rounded px-3 py-2.5 text-xs font-semibold {{ request()->routeIs($route) ? 'bg-white text-brand-dark shadow-sm dark:bg-gray-700 dark:text-green-300' : 'text-gray-500 dark:text-gray-400' }}"><x-icon :name="$icon" class="h-4 w-4" />{{ $label }}</a>
                @endforeach
            </nav>
            {{ $slot }}
            <div class="mt-8 border-t border-gray-200 pt-5 text-xs leading-5 text-gray-500 dark:border-gray-700">Butuh bantuan akses? Hubungi HR atau tim IT perusahaan.</div>
        </div>
        <p class="mt-8 text-xs text-gray-400">&copy; {{ date('Y') }} PT Mada Wikri Tunggal</p>
    </section>
</div>
