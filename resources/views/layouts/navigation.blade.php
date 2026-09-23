<nav class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 shadow-sm sticky top-0 z-30">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <!-- Brand Logo & Links -->
            <div class="flex items-center space-x-8">
                <a href="{{ auth()->user()?->isManagement() ? route('admin.dashboard') : route('operator.dashboard') }}" class="flex items-center gap-3">
                    <img src="{{ asset('assets/images/logo/logo-landscape.webp') }}" alt="Logo MWT" class="h-9 w-auto object-contain">
                    <span class="font-heading font-extrabold text-xl text-brand-dark dark:text-green-400 tracking-tight hidden sm:inline">Pojok Ilmu</span>
                </a>

                @if(auth()->user()?->isManagement())
                    <div class="hidden md:flex space-x-4">
                        <a href="{{ route('admin.dashboard') }}" class="px-3 py-2 rounded-xl text-sm font-semibold transition-colors {{ request()->routeIs('admin.dashboard') ? 'bg-brand-dark text-white shadow-md' : 'text-gray-600 dark:text-gray-300 hover:text-brand-dark dark:hover:text-green-400' }}">
                            Skill Matrix
                        </a>
                        <a href="{{ route('admin.skills.index') }}" class="px-3 py-2 rounded-xl text-sm font-semibold transition-colors {{ request()->routeIs('admin.skills.*') ? 'bg-brand-dark text-white shadow-md' : 'text-gray-600 dark:text-gray-300 hover:text-brand-dark dark:hover:text-green-400' }}">
                            Master Skill
                        </a>
                        <a href="{{ route('admin.trainings.index') }}" class="px-3 py-2 rounded-xl text-sm font-semibold transition-colors {{ request()->routeIs('admin.trainings.*') ? 'bg-brand-dark text-white shadow-md' : 'text-gray-600 dark:text-gray-300 hover:text-brand-dark dark:hover:text-green-400' }}">
                            Program & Materi
                        </a>
                    </div>
                @else
                    <div class="hidden md:flex space-x-4">
                        <a href="{{ route('operator.dashboard') }}" class="px-3 py-2 rounded-xl text-sm font-semibold transition-colors {{ request()->routeIs('operator.dashboard') ? 'bg-brand-dark text-white shadow-md' : 'text-gray-600 dark:text-gray-300 hover:text-brand-dark dark:hover:text-green-400' }}">
                            Training Saya (TNA)
                        </a>
                    </div>
                @endif
            </div>

            <!-- User Menu & Dark Mode Toggle -->
            <div class="flex items-center space-x-4">
                <div class="text-right text-xs">
                    <p class="font-bold text-gray-800 dark:text-gray-200">{{ auth()->user()?->name }}</p>
                    <p class="text-gray-500 dark:text-gray-400">{{ session('departemen', 'MWT Staff') }}</p>
                </div>

                <!-- Dark Mode Toggle Button -->
                <button @click="darkMode = !darkMode" type="button" class="p-2 rounded-xl text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 bg-gray-100 dark:bg-gray-700 transition-all" title="Toggle Theme">
                    <template x-if="!darkMode">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path></svg>
                    </template>
                    <template x-if="darkMode">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                    </template>
                </button>

                <!-- Logout Form -->
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="p-2 rounded-xl text-red-600 hover:bg-red-50 dark:hover:bg-red-950/30 transition-all" title="Keluar">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path></svg>
                    </button>
                </form>
            </div>
        </div>
    </div>
</nav>
