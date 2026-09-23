# Pojok Ilmu – Training System & Operator Skill Matrix
> **From Target to Competency: Learn, Grow, Achieve.**  
> *Sistem Manajemen Pelatihan & Matriks Kompetensi Karyawan PT Mada Wikri Tunggal (MWT).*

![Pojok Ilmu Banner](public/assets/images/logo/logo-landscape.webp)

---

## 📌 Tentang Pojok Ilmu

**Pojok Ilmu** adalah platform digital internal PT Mada Wikri Tunggal yang dirancang untuk me-modernisasi proses pelatihan manual (*Training Needs Analysis / TNA*) menjadi sistem manajemen kompetensi berbasis otomatisasi. 

Sistem ini memfasilitasi pembelajaran mandiri karyawan (*Self-Training*), pengujian berjenjang (Level 1 s/d Level 4), serta memperbarui **Skill Matrix Operator (4-Block Level)** secara *real-time* untuk memastikan seluruh proses produksi dijalankan oleh **tenaga kerja yang terakreditasi**.

---

## 🔗 Integrasi Basis Data Terpadu (Connected to S2S Database)

Aplikasi **Pojok Ilmu** telah terhubung secara langsung (*multi-database connection*) ke **Database S2S (`db_s2smfg`)**:
- Data pengguna (`users`) dan data karyawan (`m_manpower`) dibaca langsung dari database **S2S**.
- Tidak perlu membuat ulang atau menduplikasi database pengguna di Pojok Ilmu.
- Otentikasi manual maupun scan barcode/QR langsung tervalidasi dengan data terpusat S2S.

Konfigurasi koneksi S2S di `.env`:
```env
DB_S2S_CONNECTION=s2s
DB_S2S_HOST=127.0.0.1
DB_S2S_PORT=3306
DB_S2S_DATABASE=db_s2smfg
DB_S2S_USERNAME=root
DB_S2S_PASSWORD=
```

---

## 🚀 Fitur-Fitur Utama

### 1. 🔐 Dual-Auth Login System (Manual & Barcode Scan)
- **Manual Login:** Login NPK + Password untuk Admin, HR, dan Supervisor.
- **Scan Login (`/login/scan`):** Instant login untuk Operator dengan melakukan *scanning* barcode/QR Code NPK pada kartu ID (tanpa mengetik password).

### 2. 📚 Modul Pembelajaran Multimedia (Self-Training)
- **Multi-Format Content:** Mendukung Teks/Rich Text, Foto (WebP compressed), dan **Embed Video Google Drive / YouTube Unlisted**.
- **Hemat Bandwidth & Server:** Video diputar langsung melalui pemain media bawaan Google Drive tanpa membebani *storage* server internal.
- **Progress Belajar:** Melacak penyelesaian materi sebelum operator dapat membuka kuis.

### 3. 📝 Engine Assessment & Bank Soal Multimedia
- **Soal Berjenjang:** Bank soal multimedia (Teks, Gambar, dan Video GDrive) yang dikelompokkan berdasarkan **Level 1 s/d Level 4**.
- **Passing Grade & Timer:** Pengaturan kelulusan otomatis, durasi waktu, serta pengacakan soal.
- **Logika Remedi & Eskalasi:** Jika operator gagal ujian melebihi batas pengulangan (remedi), sistem otomatis mengirim **Eskalasi ke HR/Atasan**.

### 4. 📊 Interactive 4-Block Skill Matrix Dashboard
- **Visualisasi 4-Block Level:** Tampilan matriks kompetensi operator (*Current Level* vs *Target Level*) secara interaktif per skill, part, atau mesin.
- **Auto-TNA Generator:** Sistem otomatis mendeteksi *skill gap* ($\Delta\text{Gap}$) dan mendaftarkan operator ke program training yang relevan.
- **Akreditasi Tenaga Kerja:** Status kelulusan langsung memperbarui *Skill Matrix* aktual dan mencatat riwayat pelatihan.

---

## 🛠️ Teknologi & Standar Kode

- **Backend:** PHP 8.2 & Laravel 12 (Model inheriting `BaseModel`, Multi-DB S2S Connection, UUID Primary Keys, `mwtTimestamps()`, `softDeletes()`).
- **Frontend:** Tailwind CSS v4 (`resources/css/app.css` dengan palet warna resmi MWT `brand-dark`, `brand-light`, `brand-surface`), Alpine.js, dan SweetAlert2.
- **Pengujian & Format:** Pest 3 PHP, Laravel Pint (`./vendor/bin/pint`).
- **UI Components:** Menggunakan Blade Components terstandarisasi MWT (`<x-button>`, `<x-input>`, `<x-card>`, `<x-table>`, `<x-select>`, `<x-modal>`, `<x-toast>`).

---

## 💻 Panduan Instalasi & Penggunaan

### 1. Clone & Install Dependencies
```bash
git clone https://github.com/Exvade/pojok-ilmu.git
cd pojok-ilmu

composer install
npm install
```

### 2. Konfigurasi Environment & Database
```bash
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
```

### 3. Jalankan Server Dev
```bash
composer run dev
```

---

© 2026 PT Mada Wikri Tunggal. All Rights Reserved.
