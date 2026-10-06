# UI Pojok Ilmu

## Pola desain

- Sidebar permanen pada desktop, drawer dengan focus trap pada mobile.
- Warna hijau MWT, latar abu-abu, radius 6 px untuk kontrol dan 8 px untuk panel.
- Gunakan `x-button`, `x-input`, `x-select`, `x-textarea`, `x-card`, `x-table`, dan `x-modal`; jangan membuat ulang style kontrol di halaman.
- Modal dibuka melalui event `open-modal` dengan nama modal. Form mengirim `_modal` untuk membuka kembali form yang gagal validasi.
- Konfirmasi tindakan melalui atribut `data-confirm` pada form, bukan `window.confirm`.
- Alpine dan SweetAlert dibundel oleh Vite, tidak bergantung pada CDN.

## Verifikasi

Jalankan `npm run build` dan `php artisan test`. Tes menggunakan SQLite in-memory sesuai `phpunit.xml`, bukan database karyawan.

Audit browser opsional tersedia di `tests/ui-audit.cjs`. Variabel lingkungan yang diperlukan:

- `UI_PLAYWRIGHT_PATH`: lokasi modul Playwright yang tersedia.
- `UI_BROWSER_PATH`: executable Chrome/Chromium.
- `UI_BASE_URL`: server lokal (default `http://127.0.0.1:8015`).
- `UI_ADMIN_ID`, `UI_ADMIN_PASSWORD`, `UI_EMPLOYEE_ID`: akun uji yang diizinkan, jangan simpan kredensial dalam source.

Untuk menyertakan preview materi, soal, dan hasil ujian tanpa membuat enrollment pada database lokal, jalankan tes dengan `UI_CAPTURE_FIXTURES=1` terlebih dahulu. HTML fixture dan screenshot ditulis ke `storage/app/ui-audit` dan tidak boleh dipublikasikan; screenshot dapat memuat data karyawan.

Audit mencakup tampilan 1440 px dan 390 px, overflow halaman, error JavaScript, sidebar, modal, pembatalan hapus, dark mode, login, serta interaksi soal dari fixture. Tidak mengirim perubahan materi atau hasil ujian pada database lokal. Akses dashboard karyawan tetap mengikuti perilaku aplikasi yang dapat menghasilkan TNA.

Ini bukan pengujian keamanan/production lengkap: akses video eksternal, seluruh kombinasi data, dan semua perangkat belum terverifikasi.
