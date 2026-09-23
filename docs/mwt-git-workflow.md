# Panduan Git & Kolaborasi MWT

Untuk mencegah konflik *source code* dan mempermudah pelacakan perbaikan fitur, seluruh *developer* MWT wajib mematuhi standar *Git Workflow* berikut ini.

## 1. Konvensi Pesan Commit (Conventional Commits)
Pesan *commit* tidak boleh asal-asalan (Contoh salah: "perbaikan dikit", "fix error", "update file"). Gunakan awalan standar agar pesan mudah dibaca.

- `feat:` -> Untuk penambahan fitur baru. (Contoh: `feat: tambah modul login admin`)
- `fix:` -> Untuk perbaikan *bug*. (Contoh: `fix: atasi error cetak pdf pada invoice`)
- `docs:` -> Untuk perubahan dokumentasi. (Contoh: `docs: perbarui panduan instalasi readme`)
- `style:` -> Untuk perubahan desain, warna, spasi (tanpa mengubah logika). (Contoh: `style: rapikan margin tombol submit`)
- `refactor:` -> Untuk merapikan kode tanpa menambah fitur atau memperbaiki *bug*. (Contoh: `refactor: pecah controller menjadi service class`)
- `chore:` -> Untuk perubahan konfigurasi sistem/build. (Contoh: `chore: update versi laravel ke 12`)

## 2. Strategi Branching (Percabangan)
Dilarang keras melakukan *commit* dan *push* secara langsung ke *branch* `main` atau `master`.

- **`main`**: Kode stabil yang ada di tahap *Production* (Live).
- **`staging`**: Kode untuk tahap pengujian (*Testing/QA*).
- **`feature/nama-fitur`**: Buat branch ini jika Anda mengerjakan fitur baru. (Contoh: `feature/laporan-keuangan`)
- **`bugfix/nama-bug`**: Buat branch ini jika Anda memperbaiki *bug* dari tiket Jira/Trello. (Contoh: `bugfix/login-gagal`)

**Alur Kerja Standar:**
1. Anda berada di branch `main`.
2. Lakukan `git pull origin main`.
3. Buat branch baru: `git checkout -b feature/nama-fitur-anda`.
4. Lakukan koding, lalu *commit* sesuai konvensi.
5. Push ke repo: `git push origin feature/nama-fitur-anda`.
6. Buat **Pull Request (PR)** untuk digabung (*merge*) ke `staging` terlebih dahulu.
