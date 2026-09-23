@extends('layouts.app')

@section('title', 'MWT Starter Kit')

@section('content')

    <div class="min-h-screen flex flex-col bg-gray-50 dark:bg-gray-900">
        <!-- Navbar / Header -->
        <header
            class="w-full py-4 px-6 md:px-10 border-b border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 flex justify-between items-center shadow-sm">
            <div class="flex items-center gap-3">
                <img src="{{ asset('assets/images/logo/logo-landscape.webp') }}" alt="MWT Logo"
                    class="h-8 w-auto dark:hidden block">
                <img src="{{ asset('assets/images/logo/logo-landscape-light.webp') }}" alt="MWT Logo"
                    class="h-8 w-auto hidden dark:block">
                <div class="h-6 w-px bg-gray-300 dark:bg-gray-700 mx-2 hidden sm:block"></div>
                <span class="font-bold text-gray-700 dark:text-gray-300 hidden sm:block">Starter Kit</span>
            </div>

            <!-- Dark Mode Toggle -->
            <button @click="darkMode = !darkMode"
                class="p-2 rounded-lg bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors border border-gray-200 dark:border-gray-700">
                <svg x-show="!darkMode" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path>
                </svg>
                <svg x-show="darkMode" x-cloak class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z">
                    </path>
                </svg>
            </button>
        </header>

        <!-- Main Content -->
        <main class="flex-grow max-w-6xl mx-auto w-full px-6 py-10 flex flex-col gap-10">

            <!-- Header Title -->
            <section class="border-b border-gray-200 dark:border-gray-800 pb-8">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-3">Selamat Datang di MWT Starter
                    Kit</h2>
                <p class="text-gray-600 dark:text-gray-400 text-lg">Kerangka proyek standar PT Mada Wikri Tunggal.
                    Dilengkapi dengan Tailwind v4, komponen bawaan, dan panduan dasar.</p>
            </section>

            <!-- Quick Start Grid -->
            <section class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <x-card class="flex flex-col h-full">
                    <div class="flex items-center gap-3 mb-4">
                        <div
                            class="w-10 h-10 bg-gray-100 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg flex items-center justify-center text-gray-700 dark:text-gray-300">
                            <span class="font-bold">1</span>
                        </div>
                        <h4 class="font-bold text-lg">Konfigurasi Database</h4>
                    </div>
                    <p class="text-gray-600 dark:text-gray-400 text-sm flex-grow">Atur konfigurasi pada file
                        <code>.env</code> untuk menyambungkan aplikasi dengan database lokal Anda sebelum memulai
                        pengembangan.</p>
                </x-card>

                <x-card class="flex flex-col h-full">
                    <div class="flex items-center gap-3 mb-4">
                        <div
                            class="w-10 h-10 bg-gray-100 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg flex items-center justify-center text-gray-700 dark:text-gray-300">
                            <span class="font-bold">2</span>
                        </div>
                        <h4 class="font-bold text-lg">Jalankan Migrasi</h4>
                    </div>
                    <p class="text-gray-600 dark:text-gray-400 text-sm flex-grow">Gunakan perintah <code>php artisan
                            migrate</code>. Sistem ini telah disematkan fitur audit trail otomatis untuk pencatatan
                        aktivitas pengguna.</p>
                </x-card>

                <x-card class="flex flex-col h-full">
                    <div class="flex items-center gap-3 mb-4">
                        <div
                            class="w-10 h-10 bg-gray-100 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg flex items-center justify-center text-gray-700 dark:text-gray-300">
                            <span class="font-bold">3</span>
                        </div>
                        <h4 class="font-bold text-lg">Pelajari Komponen</h4>
                    </div>
                    <p class="text-gray-600 dark:text-gray-400 text-sm flex-grow">Gunakan komponen UI standar MWT yang telah
                        disediakan agar desain aplikasi selaras dengan identitas perusahaan.</p>
                </x-card>
            </section>

            <!-- Components Showcase -->
            <section>
                <h3
                    class="text-xl font-bold mb-6 text-gray-900 dark:text-white border-b border-gray-200 dark:border-gray-800 pb-2">
                    Komponen Dasar (UI)</h3>

                <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-6 lg:p-8">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-10">

                        <!-- Buttons & Inputs -->
                        <div class="space-y-6">
                            <div>
                                <h4 class="text-sm font-semibold text-gray-500 mb-3 uppercase tracking-wide">Buttons</h4>
                                <div class="flex flex-wrap gap-3">
                                    <x-button variant="primary">Simpan</x-button>
                                    <x-button variant="secondary">Batal</x-button>
                                    <x-button variant="outline">Kembali</x-button>
                                    <x-button variant="danger">Hapus</x-button>
                                </div>
                            </div>
                            
                            <div>
                                <h4 class="text-sm font-semibold text-gray-500 mb-3 uppercase tracking-wide">Buttons with Icon</h4>
                                <div class="flex flex-wrap gap-3">
                                    <x-button variant="primary">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path></svg>
                                        Unduh Data
                                    </x-button>
                                    <x-button variant="danger">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        Hapus Permanen
                                    </x-button>
                                    <x-button variant="outline">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                                        Kembali
                                    </x-button>
                                </div>
                            </div>

                            <div>
                                <h4 class="text-sm font-semibold text-gray-500 mb-3 uppercase tracking-wide">Input Kolom
                                </h4>
                                <div class="space-y-4 max-w-sm">
                                    <div>
                                        <label class="block text-sm font-medium mb-1">Nama Lengkap</label>
                                        <x-input placeholder="Contoh: Budi Santoso" />
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium mb-1">Departemen (x-select)</label>
                                        <x-select>
                                            <option>IT & Development</option>
                                            <option>Human Resources</option>
                                            <option>Finance</option>
                                        </x-select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium mb-1">Kata Sandi (x-password)</label>
                                        <x-password placeholder="Masukkan kata sandi..." />
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium mb-1 text-red-600 dark:text-red-400">Email
                                            Perusahaan</label>
                                        <x-input placeholder="email@mw-tunggal.co.id" :error="true" />
                                        <span class="text-xs text-red-600 dark:text-red-400 mt-1 block">Domain email tidak
                                            valid.</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Alerts -->
                        <div class="space-y-6">
                            <div>
                                <h4 class="text-sm font-semibold text-gray-500 mb-3 uppercase tracking-wide">Alert Boxes
                                </h4>
                                <div class="space-y-3">
                                    <x-alert type="success" title="Sukses">Data berhasil ditambahkan ke database.</x-alert>
                                    <x-alert type="danger" title="Peringatan">Terjadi kesalahan pada sistem
                                        koneksi.</x-alert>
                                </div>
                            </div>

                            <div>
                                <h4 class="text-sm font-semibold text-gray-500 mb-3 uppercase tracking-wide">Notifikasi
                                    Global (Native Toast)</h4>
                                <x-button variant="primary"
                                    onclick="window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'success', title: 'Berhasil!', message: 'Native Toast Alpine.js telah terintegrasi di sistem!' } }))">
                                    Test Notifikasi
                                </x-button>
                            </div>
                        </div>

                    </div>
                </div>
            </section>

            <!-- Komponen Lanjutan -->
            <section>
                <h3 class="text-xl font-bold mb-6 text-gray-900 dark:text-white border-b border-gray-200 dark:border-gray-800 pb-2">Komponen Lanjutan</h3>
                
                <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-6 lg:p-8 space-y-10">
                    
                    <!-- File Upload & Interactivity -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
                        <div>
                            <h4 class="text-sm font-semibold text-gray-500 mb-3 uppercase tracking-wide">File Upload (Drag & Drop)</h4>
                            <x-file-upload name="document" label="Unggah Laporan Mingguan" accept=".pdf,.doc,.docx" />
                        </div>
                        <div>
                            <h4 class="text-sm font-semibold text-gray-500 mb-3 uppercase tracking-wide">Modal & Toast</h4>
                            <div class="flex flex-col gap-4">
                                <x-button variant="outline" @click="$dispatch('open-modal', 'demo-modal')">
                                    Buka Modal Konfirmasi
                                </x-button>
                                
                                <x-button variant="primary" @click="$dispatch('notify', { type: 'success', title: 'Berhasil', message: 'Tindakan Anda telah disimpan.' })">
                                    Tampilkan Toast Sukses
                                </x-button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Table & Empty State -->
                    <div>
                        <h4 class="text-sm font-semibold text-gray-500 mb-3 uppercase tracking-wide">Data Table & Empty State</h4>
                        <x-table :headers="['Nama Pegawai', 'Departemen', 'Status', 'Aksi']">
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap"><div class="font-semibold text-gray-900 dark:text-white">Budi Santoso</div></td>
                                <td class="px-6 py-4 whitespace-nowrap">IT & Development</td>
                                <td class="px-6 py-4 whitespace-nowrap"><span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-brand-dark border border-green-200">Aktif</span></td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-3">
                                        <button class="text-gray-400 hover:text-brand-light transition-colors" title="Edit">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                        </button>
                                        <button class="text-gray-400 hover:text-red-500 transition-colors" title="Hapus">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap"><div class="font-semibold text-gray-900 dark:text-white">Siti Aminah</div></td>
                                <td class="px-6 py-4 whitespace-nowrap">Human Resources</td>
                                <td class="px-6 py-4 whitespace-nowrap"><span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 border border-gray-200">Cuti</span></td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-3">
                                        <button class="text-gray-400 hover:text-brand-light transition-colors" title="Edit">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                        </button>
                                        <button class="text-gray-400 hover:text-red-500 transition-colors" title="Hapus">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </x-table>
                        
                        <div class="mt-6">
                            <x-empty-state 
                                title="Data Tidak Ditemukan" 
                                description="Anda belum menambahkan laporan bulan ini. Klik tombol di bawah untuk mulai.">
                                <x-slot name="action">
                                    <x-button variant="primary">Tambah Laporan</x-button>
                                </x-slot>
                            </x-empty-state>
                        </div>
                    </div>
                    
                </div>
            </section>

        </main>

        <!-- Demo Modal -->
        <x-modal name="demo-modal" maxWidth="md">
            <div class="p-6">
                <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-2">Konfirmasi Penghapusan</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">Apakah Anda yakin ingin menghapus data ini? Tindakan ini tidak dapat dibatalkan.</p>
                <div class="flex justify-end gap-3">
                    <x-button variant="outline" @click="$dispatch('close-modal', 'demo-modal')">Batal</x-button>
                    <x-button variant="danger" @click="$dispatch('close-modal', 'demo-modal'); $dispatch('notify', { type: 'error', message: 'Data telah dihapus.' })">Ya, Hapus</x-button>
                </div>
            </div>
        </x-modal>

        <!-- Footer -->
        <footer
            class="py-6 text-center text-sm text-gray-500 bg-white dark:bg-gray-900 border-t border-gray-200 dark:border-gray-800">
            &copy; {{ date('Y') }} PT Mada Wikri Tunggal. All Rights Reserved.
        </footer>
    </div>
@endsection
