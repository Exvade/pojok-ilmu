# Panduan UI/UX & Frontend MWT

Dokumen ini berisi standar desain antarmuka pengguna untuk seluruh aplikasi internal PT Mada Wikri Tunggal. Seluruh developer wajib mematuhi aturan warna, layout, dan penamaan komponen berikut.

## 1. Palet Warna (Color Palette)
Aplikasi MWT memiliki dua warna utama. Jangan menggunakan warna kustom lain di luar palet Tailwind kecuali sangat mendesak.

- **Brand Dark (Hijau Tua)**
  - Digunakan untuk: Navbar, Sidebar, Tombol Primer (Hover), Footer.
  - Nilai HEX: `#14532d` (Tailwind: `green-900`)
- **Brand Light (Hijau Muda)**
  - Digunakan untuk: Tombol Primer, Aksen Icon, Badge.
  - Nilai HEX: `#22c55e` (Tailwind: `green-500`)
- **Surface (Background)**
  - Digunakan untuk: Latar belakang halaman aplikasi.
  - Nilai HEX: `#f8fafc` (Tailwind: `slate-50`)
- **Semantic Colors (Wajib untuk Feedback)**
  - **Error/Danger:** Wajib gunakan `#DC2626` (Tailwind: `red-600`) untuk validasi gagal, tombol hapus, peringatan kritis.
  - **Warning:** Wajib gunakan `#D97706` (Tailwind: `amber-600`) untuk peringatan sedang, konfirmasi aksi berisiko.
  - **Info:** Wajib gunakan `#0284C7` (Tailwind: `sky-600`) untuk informasi umum atau link rujukan.
  
## 2. Tipografi & Border Radius (Corner)
AI atau Developer dilarang menebak-nebak ukuran sudut (border-radius) dan font. Patuhi aturan ini:
- **Font Utama:** Gunakan `font-sans` (Inter/System Font) untuk body, dan `font-heading` (Outfit/Montserrat) untuk judul (h1-h6).
- **Border Radius:**
  - **Card/Modal/Wadah Besar:** Wajib gunakan `rounded-xl` atau `rounded-2xl`. (Jangan gunakan kotak tajam / `rounded-none`).
  - **Tombol & Input Form:** Wajib gunakan `rounded-md` atau `rounded-lg`. (Jangan gunakan tombol melingkar / `rounded-full` kecuali untuk icon khusus).
  - **Badge/Label:** Wajib gunakan `rounded-full`.

## 3. Standar Alert / Popup (SweetAlert)
Semua pesan sukses, gagal, atau konfirmasi hapus wajib menggunakan **SweetAlert2**. Dilarang menggunakan fungsi `alert()` bawaan browser atau pesan `flash` statis biasa tanpa desain.

**Contoh SweetAlert Sukses:**
```javascript
Swal.fire({
  icon: 'success',
  title: 'Berhasil!',
  text: 'Data karyawan berhasil disimpan.',
  confirmButtonColor: '#22c55e'
});
```

## 4. Gaya Bahasa (Copywriting)
Sistem internal MWT harus menggunakan **Bahasa Indonesia Baku dan Sopan**.
- **Hindari Kata:** `Create`, `Update`, `Delete`, `Submit`.
- **Gunakan Kata:** `Tambah`, `Simpan`, `Ubah`, `Hapus`.
- Pesan kesalahan (error) tidak boleh menyalahkan user (Contoh salah: "Anda salah memasukkan password"). Gunakan pesan pasif (Contoh benar: "Kombinasi email dan password tidak sesuai").

## 5. Standar CSS
- Wajib menggunakan **Tailwind CSS**.
- **Dilarang** menulis kode CSS manual (Vanilla CSS di tag `<style>`) kecuali untuk animasi khusus atau perbaikan komponen *library* eksternal yang sulit dimodifikasi lewat Tailwind.

## 6. Ikonografi (Iconography)
- **DILARANG KERAS** menggunakan emoji bawaan *keyboard* (contoh: ❌, 😃, 🗑️) sebagai ikon di dalam aplikasi.
- Semua ikon wajib menggunakan aset gambar atau SVG yang konsisten. Sangat disarankan menggunakan ikon dari *library* profesional seperti **Heroicons**, **FontAwesome**, atau **Lucide**.

## 7. Responsivitas Layar (Responsive Design)
- Semua halaman web dan komponen aplikasi **WAJIB** responsif.
- Tampilan harus tetap berfungsi dan rapi saat dibuka di layar *Desktop*, *Tablet*, maupun *Mobile*.
- Manfaatkan kelas-kelas *breakpoint* bawaan Tailwind (`sm:`, `md:`, `lg:`, `xl:`) secara aktif untuk memastikan tata letak beradaptasi dengan mulus ke segala ukuran perangkat.
