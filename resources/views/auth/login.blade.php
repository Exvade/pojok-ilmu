@extends('layouts.app')

@section('title', 'Masuk - Pojok Ilmu MWT')

@section('content')
<div class="h-screen w-screen max-h-screen grid grid-cols-1 lg:grid-cols-12 bg-white dark:bg-gray-900 font-sans overflow-hidden" x-data="{ liveTime: '' }" x-init="setInterval(() => { const d = new Date(); liveTime = d.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' }) + ' WIB &bull; ' + d.toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' }); }, 1000)">
    
    <!-- SISI KIRI: Banner Utama MWT Enterprise (Desktop Col 7 - 100vh) -->
    <div class="hidden lg:flex lg:col-span-7 h-full relative bg-gradient-to-br from-[#072c15] via-brand-dark to-[#0c4021] text-white p-8 xl:p-10 flex-col justify-between overflow-hidden shadow-2xl">
        <!-- Pola Latar Belakang Matrix -->
        <div class="absolute inset-0 bg-[linear-gradient(to_right,#ffffff08_1px,transparent_1px),linear-gradient(to_bottom,#ffffff08_1px,transparent_1px)] bg-[size:24px_24px] pointer-events-none"></div>
        <div class="absolute -bottom-24 -left-24 w-96 h-96 bg-brand-light/20 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -top-24 -right-24 w-96 h-96 bg-emerald-400/10 rounded-full blur-3xl pointer-events-none"></div>

        <!-- Header Atas: Kartu Logo MWT Resmi -->
        <div class="relative z-10 flex items-center justify-between">
            <div class="bg-white shadow-xl px-4 py-2.5 rounded-2xl border border-white/20 inline-flex items-center justify-center transform hover:scale-105 transition-all duration-300">
                <img src="{{ asset('assets/images/logo/logo-landscape.webp') }}" alt="Logo MWT" class="h-8 w-auto object-contain">
            </div>

            <span class="px-3.5 py-1.5 rounded-full text-xs font-semibold bg-brand-light/20 border border-brand-light/40 text-green-300 backdrop-blur-md flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5 text-brand-light" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                Sistem Pelatihan & Matriks Kompetensi
            </span>
        </div>

        <!-- Teks Hero Utama -->
        <div class="relative z-10 my-auto space-y-6 max-w-xl">
            <div class="space-y-3">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-lg bg-green-500/20 text-green-300 text-xs font-semibold border border-green-400/30">
                    <span class="w-2 h-2 rounded-full bg-brand-light animate-ping"></span>
                    Perbaikan Berkelanjutan (CI) & Industri 4.0
                </div>

                <h1 class="text-3xl xl:text-4xl font-extrabold font-heading text-white leading-tight tracking-tight">
                    Dari Target Menuju Kompetensi.<br>
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-green-300 via-brand-light to-emerald-200">
                        Belajar, Tumbuh, Berprestasi.
                    </span>
                </h1>

                <p class="text-green-100/90 text-xs xl:text-sm leading-relaxed font-normal">
                    Sistem pelatihan mandiri multimedia dan otomatisasi <strong>4-Block Skill Matrix</strong> untuk mencetak tenaga kerja terakreditasi di lini produksi PT Mada Wikri Tunggal.
                </p>
            </div>

            <!-- Grid 3 Pilar Pelatihan -->
            <div class="grid grid-cols-3 gap-3 pt-3 border-t border-white/10">
                <div class="p-3.5 rounded-2xl bg-white/5 backdrop-blur-md border border-white/10 transition-transform hover:-translate-y-1 duration-300">
                    <div class="w-8 h-8 rounded-xl bg-brand-light/20 text-green-300 flex items-center justify-center mb-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C20.832 18.477 19.247 18 17.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                    </div>
                    <h4 class="font-bold text-xs text-white font-heading">Pelatihan Mandiri</h4>
                    <p class="text-[10px] text-green-200/80 mt-0.5">Video GDrive, Foto & SOP Instruksi Kerja.</p>
                </div>

                <div class="p-3.5 rounded-2xl bg-white/5 backdrop-blur-md border border-white/10 transition-transform hover:-translate-y-1 duration-300">
                    <div class="w-8 h-8 rounded-xl bg-emerald-400/20 text-emerald-300 flex items-center justify-center mb-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2"></path></svg>
                    </div>
                    <h4 class="font-bold text-xs text-white font-heading">Matriks Kompetensi</h4>
                    <p class="text-[10px] text-green-200/80 mt-0.5">Pembaruan otomatis 4-block level 1-4.</p>
                </div>

                <div class="p-3.5 rounded-2xl bg-white/5 backdrop-blur-md border border-white/10 transition-transform hover:-translate-y-1 duration-300">
                    <div class="w-8 h-8 rounded-xl bg-blue-400/20 text-blue-300 flex items-center justify-center mb-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path></svg>
                    </div>
                    <h4 class="font-bold text-xs text-white font-heading">Akreditasi Operator</h4>
                    <p class="text-[10px] text-green-200/80 mt-0.5">Tenaga kerja terakreditasi standar mutu.</p>
                </div>
            </div>
        </div>

        <!-- Footer Kiri: Waktu Real-Time & Hak Cipta -->
        <div class="relative z-10 flex items-center justify-between text-[11px] text-green-200/70 border-t border-white/10 pt-3">
            <div class="flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-brand-light"></span>
                <span x-html="liveTime">Memuat jam...</span>
            </div>
            <span>&copy; {{ date('Y') }} PT Mada Wikri Tunggal</span>
        </div>
    </div>

    <!-- SISI KANAN: Panel Formulir Masuk (Desktop Col 5 - 100vh tanpa scroll) -->
    <div class="lg:col-span-5 h-full flex flex-col justify-between px-6 sm:px-10 py-6 xl:py-8 bg-white dark:bg-gray-900 overflow-y-auto lg:overflow-hidden">
        
        <!-- Header Logo Mobile -->
        <div class="lg:hidden text-center mb-4">
            <img src="{{ asset('assets/images/logo/logo-landscape.webp') }}" alt="Logo MWT" class="h-10 mx-auto object-contain mb-1">
            <h2 class="text-xl font-extrabold font-heading text-brand-dark dark:text-green-400">Pojok Ilmu</h2>
            <p class="text-[11px] text-gray-500">Sistem Pelatihan & Matriks Kompetensi Operator</p>
        </div>

        <div class="max-w-md w-full mx-auto my-auto space-y-5">
            
            <!-- Judul Sapaan dengan Icon SVG (Tanpa Emoji) -->
            <div class="space-y-1.5">
                <div class="flex items-center gap-2">
                    <h2 class="text-2xl xl:text-3xl font-extrabold font-heading text-gray-900 dark:text-gray-100 tracking-tight">
                        Selamat datang kembali
                    </h2>
                    <div class="p-1 rounded-lg bg-green-100 text-brand-dark dark:bg-green-950/60 dark:text-green-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 10l-2 1m0 0l-2-1m2 1v2.5M20 7l-2 1m2-1l-2-1m2 1v2.5M14 4l-2-1-2 1M4 7l2-1M4 7l2 1M4 7v2.5M12 21l-2-1m2 1l2-1m-2 1v-2.5M6 18l-2-1v-2.5M18 18l2-1v-2.5"></path></svg>
                    </div>
                </div>
                <p class="text-xs text-gray-500 dark:text-gray-400">
                    Masuk dengan akun terdaftar Anda untuk melanjutkan ke portal Pojok Ilmu MWT.
                </p>
            </div>

            <!-- Tab Mode Masuk -->
            <div class="flex p-1 rounded-2xl bg-gray-100 dark:bg-gray-800 border border-gray-200 dark:border-gray-700">
                <a href="{{ route('login') }}" class="flex-1 text-center py-2 px-3 text-xs font-bold rounded-xl bg-brand-dark text-white shadow-md transition-all flex items-center justify-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path></svg>
                    Masuk Manual
                </a>
                <a href="{{ route('login.scan') }}" class="flex-1 text-center py-2 px-3 text-xs font-bold rounded-xl text-gray-600 dark:text-gray-300 hover:text-brand-dark dark:hover:text-green-400 transition-all flex items-center justify-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path></svg>
                    Pindai Barcode / QR
                </a>
            </div>

            <!-- Formulir Masuk -->
            <form action="{{ route('login.post') }}" method="POST" class="space-y-4">
                @csrf

                <!-- Input User ID / NPK -->
                <div>
                    <label for="user_id" class="block text-[11px] font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1.5">
                        Username / NPK Operator
                    </label>
                    <div class="relative rounded-xl shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                        </div>
                        <input 
                            type="text" 
                            id="user_id" 
                            name="user_id" 
                            value="{{ old('user_id') }}" 
                            placeholder="Masukkan NPK (misal: OP001 atau HR001)" 
                            required 
                            autofocus 
                            class="w-full pl-9 pr-3 py-2.5 text-xs sm:text-sm rounded-xl border border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white focus:ring-2 focus:ring-brand-light focus:border-transparent transition-all uppercase"
                        />
                    </div>
                </div>

                <!-- Input Password -->
                <div>
                    <div class="flex justify-between items-center mb-1.5">
                        <label for="password" class="block text-[11px] font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                            Kata Sandi / Password
                        </label>
                        <span class="text-[10px] text-gray-400">(Wajib untuk Admin/Management)</span>
                    </div>
                    <div class="relative rounded-xl shadow-sm" x-data="{ show: false }">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                        </div>
                        <input 
                            :type="show ? 'text' : 'password'" 
                            id="password" 
                            name="password" 
                            placeholder="Masukkan kata sandi akun Anda" 
                            class="w-full pl-9 pr-9 py-2.5 text-xs sm:text-sm rounded-xl border border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white focus:ring-2 focus:ring-brand-light focus:border-transparent transition-all"
                        />
                        <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                            <template x-if="!show">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                            </template>
                            <template x-if="show">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858-5.908a10.025 10.025 0 012.122-.363c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21M3 3l18 18"></path></svg>
                            </template>
                        </button>
                    </div>
                </div>

                <!-- Tombol Masuk Utama -->
                <div class="pt-1">
                    <button type="submit" class="w-full py-3 px-5 rounded-xl font-heading font-extrabold text-sm text-white bg-gradient-to-r from-brand-dark to-green-800 hover:from-green-900 hover:to-brand-dark shadow-lg shadow-green-900/20 transform hover:-translate-y-0.5 transition-all duration-300 flex items-center justify-center gap-2 group">
                        <span>Masuk ke Sistem</span>
                        <svg class="w-4 h-4 transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                    </button>
                </div>
            </form>

            <!-- Akses Cepat Operator -->
            <div class="pt-4 border-t border-gray-100 dark:border-gray-800 space-y-2.5">
                <p class="text-center text-[10px] font-bold text-gray-400 uppercase tracking-widest">Akses Cepat Operator</p>
                <div class="grid grid-cols-2 gap-2.5">
                    <a href="{{ route('login.scan') }}" class="p-3 rounded-xl border border-gray-200 dark:border-gray-700 hover:border-brand-light bg-gray-50/50 dark:bg-gray-800/50 hover:bg-green-50/50 transition-all flex flex-col items-center text-center group">
                        <div class="p-1.5 rounded-lg bg-green-100 dark:bg-green-950/60 text-brand-dark dark:text-green-400 mb-1 group-hover:scale-110 transition-transform">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path></svg>
                        </div>
                        <span class="text-xs font-bold text-gray-800 dark:text-gray-200">Pindai Barcode NPK</span>
                        <span class="text-[10px] text-gray-400">Masuk Instan Tanpa Ketik</span>
                    </a>

                    <div class="p-3 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50 flex flex-col items-center text-center">
                        <div class="p-1.5 rounded-lg bg-blue-100 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 mb-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <span class="text-xs font-bold text-gray-800 dark:text-gray-200">Bantuan HRD</span>
                        <span class="text-[10px] text-gray-400">Status Akun & Remedi</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Catatan Kaki -->
        <div class="text-center text-[10px] text-gray-400 pt-2 border-t border-gray-100 dark:border-gray-800">
            <p>Standardisasi Sistem Pelatihan & Matriks Kompetensi MWT</p>
            <p class="font-mono text-[10px] text-gray-400">v1.0.0 &bull; PT Mada Wikri Tunggal</p>
        </div>
    </div>
</div>
@endsection
