@extends('layouts.app')

@section('title', 'Pindai Barcode Login - Pojok Ilmu MWT')

@section('content')
<div x-data="scanLogin()" x-init="initScanner()" class="h-screen w-screen max-h-screen grid grid-cols-1 lg:grid-cols-12 bg-white dark:bg-gray-900 font-sans overflow-hidden" x-data="{ liveTime: '' }" x-init="setInterval(() => { const d = new Date(); liveTime = d.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' }) + ' WIB &bull; ' + d.toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' }); }, 1000)">
    
    <!-- SISI KIRI: Banner Utama MWT Enterprise (Desktop Col 7 - 100vh) -->
    <div class="hidden lg:flex lg:col-span-7 h-full relative bg-gradient-to-br from-[#072c15] via-brand-dark to-[#0c4021] text-white p-8 xl:p-10 flex-col justify-between overflow-hidden shadow-2xl">
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
                Otentikasi Barcode / QR Instan
            </span>
        </div>

        <!-- Teks Hero Utama -->
        <div class="relative z-10 my-auto space-y-6 max-w-xl">
            <div class="space-y-3">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-lg bg-green-500/20 text-green-300 text-xs font-semibold border border-green-400/30">
                    <span class="w-2 h-2 rounded-full bg-brand-light animate-ping"></span>
                    Pemindaian Operator Instan
                </div>

                <h1 class="text-3xl xl:text-4xl font-extrabold font-heading text-white leading-tight tracking-tight">
                    Pindai Kartu NPK.<br>
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-green-300 via-brand-light to-emerald-200">
                        Langsung Akses Pelatihan.
                    </span>
                </h1>

                <p class="text-green-100/90 text-xs xl:text-sm leading-relaxed font-normal">
                    Fasilitas masuk instan khusus operator tanpa perlu mengetik kata sandi. Cukup tembakkan alat pemindai fisik ke barcode ID / NPK Anda.
                </p>
            </div>

            <!-- Panduan 3 Langkah Pemindaian -->
            <div class="grid grid-cols-3 gap-3 pt-3 border-t border-white/10">
                <div class="p-3.5 rounded-2xl bg-white/5 backdrop-blur-md border border-white/10">
                    <div class="w-7 h-7 rounded-full bg-brand-light text-brand-dark font-extrabold flex items-center justify-center mb-1.5 text-xs">1</div>
                    <h4 class="font-bold text-xs text-white">Siapkan Kartu ID</h4>
                    <p class="text-[10px] text-green-200/80 mt-0.5">Pastikan barcode NPK terlihat jelas.</p>
                </div>
                <div class="p-3.5 rounded-2xl bg-white/5 backdrop-blur-md border border-white/10">
                    <div class="w-7 h-7 rounded-full bg-brand-light text-brand-dark font-extrabold flex items-center justify-center mb-1.5 text-xs">2</div>
                    <h4 class="font-bold text-xs text-white">Arahkan Alat Pemindai</h4>
                    <p class="text-[10px] text-green-200/80 mt-0.5">Tembakkan pemindai ke area scan.</p>
                </div>
                <div class="p-3.5 rounded-2xl bg-white/5 backdrop-blur-md border border-white/10">
                    <div class="w-7 h-7 rounded-full bg-brand-light text-brand-dark font-extrabold flex items-center justify-center mb-1.5 text-xs">3</div>
                    <h4 class="font-bold text-xs text-white">Otomatis Masuk</h4>
                    <p class="text-[10px] text-green-200/80 mt-0.5">Langsung masuk ke portal pelatihan.</p>
                </div>
            </div>
        </div>

        <!-- Footer Kiri -->
        <div class="relative z-10 flex items-center justify-between text-[11px] text-green-200/70 border-t border-white/10 pt-3">
            <div class="flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-brand-light"></span>
                <span>Sistem Siap Digunakan</span>
            </div>
            <span>&copy; {{ date('Y') }} PT Mada Wikri Tunggal</span>
        </div>
    </div>

    <!-- SISI KANAN: Panel Pemindaian (Desktop Col 5 - 100vh tanpa scroll) -->
    <div class="lg:col-span-5 h-full flex flex-col justify-between px-6 sm:px-10 py-6 xl:py-8 bg-white dark:bg-gray-900 overflow-y-auto lg:overflow-hidden">
        
        <div class="max-w-md w-full mx-auto my-auto space-y-5">
            
            <!-- Judul Sapaan -->
            <div class="space-y-1.5">
                <h2 class="text-2xl xl:text-3xl font-extrabold font-heading text-gray-900 dark:text-gray-100 tracking-tight">
                    Pindai Barcode / QR NPK
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400">
                    Arahkan alat pemindai barcode fisik ke kolom input di bawah ini.
                </p>
            </div>

            <!-- Tab Mode Masuk -->
            <div class="flex p-1 rounded-2xl bg-gray-100 dark:bg-gray-800 border border-gray-200 dark:border-gray-700">
                <a href="{{ route('login') }}" class="flex-1 text-center py-2 px-3 text-xs font-bold rounded-xl text-gray-600 dark:text-gray-300 hover:text-brand-dark dark:hover:text-green-400 transition-all flex items-center justify-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path></svg>
                    Masuk Manual
                </a>
                <a href="{{ route('login.scan') }}" class="flex-1 text-center py-2 px-3 text-xs font-bold rounded-xl bg-brand-dark text-white shadow-md transition-all flex items-center justify-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path></svg>
                    Pindai Barcode / QR
                </a>
            </div>

            <!-- Grafis Pemindai Interaktif & Input Otomatis -->
            <div class="space-y-4 text-center">
                <div class="relative w-36 h-36 mx-auto flex items-center justify-center rounded-3xl bg-green-950/5 dark:bg-green-950/20 border-2 border-dashed border-brand-light shadow-inner overflow-hidden">
                    <!-- Garis Laser -->
                    <div class="absolute inset-x-0 h-1 bg-gradient-to-r from-transparent via-brand-light to-transparent animate-pulse shadow-[0_0_15px_#22c55e]"></div>
                    
                    <div class="space-y-1.5 p-3">
                        <svg class="w-12 h-12 mx-auto text-brand-dark dark:text-green-400 animate-bounce" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path>
                        </svg>
                        <p class="text-[10px] font-extrabold uppercase text-brand-dark dark:text-green-400 tracking-wider">SIAP MEMBACA BARCODE</p>
                    </div>
                </div>

                <form @submit.prevent="submitScan" class="space-y-3">
                    <div>
                        <label for="barcode_input" class="block text-[11px] font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1.5">
                            Input NPK (Fokus Otomatis Aktif)
                        </label>
                        <input 
                            type="text" 
                            id="barcode_input" 
                            x-ref="barcodeInput"
                            x-model="token" 
                            placeholder="Tembakkan alat pemindai ke sini..." 
                            required 
                            autocomplete="off"
                            class="w-full text-center py-2.5 px-4 text-base font-mono tracking-widest font-extrabold rounded-xl border-2 border-brand-light focus:ring-4 focus:ring-green-500/20 uppercase dark:bg-gray-800 dark:text-white"
                        />
                    </div>

                    <button type="submit" class="w-full py-3 px-5 rounded-xl font-heading font-extrabold text-sm text-white bg-gradient-to-r from-brand-dark to-green-800 hover:from-green-900 hover:to-brand-dark shadow-lg shadow-green-900/20 transition-all flex items-center justify-center gap-2" ::disabled="loading">
                        <template x-if="!loading">
                            <span>Kirim Hasil Scan NPK</span>
                        </template>
                        <template x-if="loading">
                            <span>Memproses Masuk...</span>
                        </template>
                    </button>
                </form>
            </div>
        </div>

        <!-- Catatan Kaki -->
        <div class="text-center text-[10px] text-gray-400 pt-2 border-t border-gray-100 dark:border-gray-800">
            <p>Standardisasi Sistem Pelatihan & Matriks Kompetensi MWT</p>
            <p class="font-mono text-[10px] text-gray-400">v1.0.0 &bull; PT Mada Wikri Tunggal</p>
        </div>
    </div>
</div>

<script>
function scanLogin() {
    return {
        token: '',
        loading: false,
        initScanner() {
            this.$nextTick(() => {
                this.keepFocus();
            });

            document.addEventListener('click', () => {
                if (this.$refs.barcodeInput) {
                    this.$refs.barcodeInput.focus();
                }
            });
        },
        keepFocus() {
            if (this.$refs.barcodeInput) {
                this.$refs.barcodeInput.focus();
            }
        },
        submitScan() {
            if (!this.token.trim()) return;
            this.loading = true;

            fetch('{{ route("login.scan.post") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ token: this.token })
            })
            .then(res => res.json())
            .then(data => {
                this.loading = false;
                if (data.success) {
                    window.dispatchEvent(new CustomEvent('notify', { 
                        detail: { type: 'success', title: 'Berhasil Masuk', message: data.message } 
                    }));
                    setTimeout(() => {
                        window.location.href = data.redirect;
                    }, 500);
                } else {
                    window.dispatchEvent(new CustomEvent('notify', { 
                        detail: { type: 'error', title: 'Gagal Masuk', message: data.message } 
                    }));
                    this.token = '';
                    this.keepFocus();
                }
            })
            .catch(err => {
                this.loading = false;
                window.dispatchEvent(new CustomEvent('notify', { 
                    detail: { type: 'error', title: 'Kesalahan Sistem', message: 'Terjadi kesalahan sistem.' } 
                }));
                this.token = '';
                this.keepFocus();
            });
        }
    }
}
</script>
@endsection
