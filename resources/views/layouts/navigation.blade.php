<div x-show="sidebarOpen" x-cloak @click="closeSidebar()" class="fixed inset-0 z-30 bg-gray-950/50 lg:hidden" aria-hidden="true"></div>
<aside id="app-sidebar" x-ref="sidebar" :inert="!sidebarOpen && !desktop" :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'" @keydown.escape.window="closeSidebar()" @keydown.tab="trapSidebar($event)" class="fixed inset-y-0 left-0 z-40 flex w-64 flex-col border-r border-gray-200 bg-white transition-transform duration-200 dark:border-gray-800 dark:bg-gray-900" aria-label="Menu utama">
    <div class="flex h-20 shrink-0 items-center justify-between border-b border-gray-100 px-5 dark:border-gray-800">
        <a href="{{ auth()->user()->isManagement() ? route('admin.dashboard') : route('operator.dashboard') }}" aria-label="Beranda Pojok Ilmu">
            <img src="{{ asset('assets/images/logo/logo-landscape.webp') }}" alt="PT Mada Wikri Tunggal" class="w-48 dark:hidden">
            <img src="{{ asset('assets/images/logo/logo-landscape-light.webp') }}" alt="PT Mada Wikri Tunggal" class="hidden w-48 dark:block">
        </a>
        <button type="button" @click="closeSidebar()" aria-label="Tutup menu" class="ml-2 text-gray-500 lg:hidden"><x-icon name="close" /></button>
    </div>
    <div class="px-6 pb-5 pt-6">
        <p class="text-xl font-bold tracking-tight text-brand-dark dark:text-green-400">Pojok Ilmu<span class="text-brand-light">.</span></p>
        <p class="mt-1 text-xs text-gray-500">Belajar. Berkembang. Berkontribusi.</p>
    </div>
    <nav class="flex-1 space-y-1 overflow-y-auto px-3" aria-label="Navigasi aplikasi">
        <p class="px-3 pb-2 text-[10px] font-semibold uppercase tracking-[0.16em] text-gray-400">{{ auth()->user()->isManagement() ? 'Manajemen pembelajaran' : 'Pembelajaran' }}</p>
        @php
            $user = auth()->user();
            $items = $user->isManagement() ? [['admin.dashboard', 'admin.dashboard', 'grid', 'Ringkasan']]
                : [['operator.dashboard', 'operator.dashboard', 'book', 'Training saya'], ['operator.profile', 'operator.profile', 'user', 'Profil saya']];
            if ($user->isManagement()) $items[] = ['operator.dashboard', 'operator.*', 'book', 'Pratinjau training'];
            if ($user->canManageCompetency()) $items[] = ['admin.product-assignments.index', 'admin.product-assignments.*', 'user', 'Penugasan training'];
            if ($user->canManageCompetency()) $items[] = ['admin.training-reports.index', 'admin.training-reports.*', 'chart', 'Laporan training'];
            if ($user->canManageCurriculum()) $items[] = ['admin.trainings.index', 'admin.trainings.*', 'book', 'Training'];
            if ($user->canAssessOjt()) $items[] = ['admin.ojt.index', 'admin.ojt.*', 'clipboard', 'Dashboard OJT'];
            if ($user->is_superadmin) $items[] = ['admin.roles.index', 'admin.roles.*', 'user', 'Kelola role'];
            if ($user->is_superadmin) $items[] = ['admin.haris-sync.index', 'admin.haris-sync.*', 'reset', 'Sinkronisasi HaRis'];
            if ($user->is_superadmin) $items[] = ['admin.haris-data-quality.index', 'admin.haris-data-quality.*', 'clipboard', 'Kualitas data HaRis'];
        @endphp
        @foreach($items as [$route, $pattern, $icon, $label])
            <a href="{{ route($route) }}" @if(request()->routeIs($pattern)) aria-current="page" @endif class="flex items-center gap-3 rounded-md px-3 py-3 text-sm font-medium transition-colors {{ request()->routeIs($pattern) ? 'bg-brand-dark text-white' : 'text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800' }}"><x-icon :name="$icon" />{{ $label }}</a>
        @endforeach
        <div class="mx-3 mt-7 border-t border-gray-100 pt-5 dark:border-gray-800">
            <p class="text-xs font-semibold text-gray-700 dark:text-gray-300">Dari pembelajaran ke leveling</p>
            <p class="mt-2 text-xs leading-5 text-gray-500">Materi, evaluasi, dan capaian level dalam satu ruang.</p>
        </div>
    </nav>
    <div class="shrink-0 border-t border-gray-200 p-4 dark:border-gray-800">
        <div class="mb-3 flex items-start gap-3">
            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-md bg-green-50 text-brand-dark dark:bg-gray-800 dark:text-green-400"><x-icon name="user" /></span>
            <div class="min-w-0"><p class="break-words text-xs font-semibold">{{ auth()->user()->name }}</p><p class="mt-1 break-words text-[11px] text-gray-500">{{ auth()->user()->manpower?->departemen ?? 'Pengelola aplikasi' }}</p></div>
        </div>
        <form action="{{ route('logout') }}" method="POST">@csrf<x-button type="submit" variant="outline" size="sm" class="w-full"><x-icon name="logout" class="h-4 w-4" />Keluar</x-button></form>
    </div>
</aside>
