# Standar Keamanan Aplikasi (Security Guidelines)

Dokumen ini berisi aturan baku terkait keamanan aplikasi yang wajib dipatuhi oleh seluruh developer PT Mada Wikri Tunggal. Tujuannya adalah untuk mencegah celah keamanan (vulnerabilities) yang dapat merugikan perusahaan dan pengguna.

---

## 1. Autentikasi dan Otorisasi

### 1.1 Autentikasi (Authentication)
* Gunakan fitur bawaan Laravel (`laravel/breeze` atau `laravel/ui` yang menggunakan `Auth::attempt()`) untuk aplikasi web biasa.
* Untuk aplikasi berbasis API (SPA, Mobile), **wajib** menggunakan **Laravel Sanctum** atau **Passport** (hindari penggunaan token JWT custom buatan sendiri).
* Jangan pernah menyimpan *password* dalam bentuk *plaintext*. Selalu gunakan `Hash::make()` atau biarkan fitur registrasi bawaan Laravel yang menanganinya.

### 1.2 Otorisasi (Authorization)
* Selalu periksa hak akses pengguna sebelum melakukan aksi krusial (Create, Update, Delete).
* Gunakan **Laravel Policies** atau **Gates**.
* Contoh yang disarankan di Controller:
  ```php
  // BAIK: Menggunakan metode bawaan authorize yang menggunakan Policy
  public function update(Request $request, Post $post)
  {
      $this->authorize('update', $post);
      // proses update
  }
  ```

---

## 2. Validasi Data (Never Trust User Input)

Semua data yang datang dari luar sistem (`$request`) harus dianggap **TIDAK AMAN**.

* **Wajib** memvalidasi semua input menggunakan **Form Request** atau `$request->validate()`.
* Jangan pernah menggunakan request masif langsung ke model tanpa proteksi fillable, seperti `User::create($request->all());`.
* Gunakan atribut `$fillable` atau `$guarded` di Model dengan hati-hati.

**Contoh Praktik yang Baik (Form Request):**
```php
public function store(StoreUserRequest $request)
{
    // Hanya data yang lolos validasi yang akan diambil
    $validated = $request->validated();
    User::create($validated);
}
```

---

## 3. Pencegahan Celah Keamanan Umum

### 3.1 Cross-Site Scripting (XSS)
* Laravel secara otomatis melindungi dari XSS jika menggunakan sintaks blade `{{ $variable }}` karena melakukan *escape* HTML.
* Hindari penggunaan `{!! $variable !!}` kecuali jika Anda yakin 100% bahwa `$variable` tersebut bersih dan berasal dari sistem (bukan input pengguna), atau telah disaring (purified).

### 3.2 Cross-Site Request Forgery (CSRF)
* Pastikan token `@csrf` ada di setiap tag `<form>` HTML metode POST, PUT, PATCH, DELETE.
* Jangan menonaktifkan *middleware* `VerifyCsrfToken` tanpa alasan kuat.

### 3.3 SQL Injection
* Gunakan **Eloquent ORM** atau **Query Builder** bawaan Laravel. Keduanya menggunakan mekanisme *PDO parameter binding* yang otomatis melindungi dari SQL Injection.
* **JANGAN PERNAH** menggunakan *raw SQL* (misal: `DB::statement()`) dengan menyisipkan input pengguna secara langsung seperti:
  ```php
  // BURUK - Sangat berbahaya!
  DB::select("SELECT * FROM users WHERE email = '" . $_POST['email'] . "'");
  ```

---

## 4. Manajemen Konfigurasi (Environment Variables)

* Jangan pernah meng-*commit* file `.env` ke *repository* Git. Gunakan `.env.example` sebagai referensi.
* Jangan menggunakan fungsi `env()` secara langsung di dalam *controller* atau logika aplikasi. Gunakan `config()` sebagai *wrapper*.
* Pastikan `APP_DEBUG=false` di *environment* produksi (*Production*).

---

## 5. Mengunggah File (File Uploads)

* Jangan pernah mempercayai ekstensi file begitu saja. Gunakan validasi Laravel (contoh: `'file' => 'required|mimes:jpg,png,pdf|max:2048'`).
* Simpan file unggahan publik di dalam `storage/app/public` (bukan langsung di `/public`). Jangan lupa jalankan `php artisan storage:link`.
* Untuk file sensitif, simpan di `storage/app` (non-publik) dan buat *route* khusus dengan *middleware* otentikasi untuk mengunduhnya.

---

Dengan mengikuti panduan keamanan di atas, kita dapat meminimalisir risiko kebocoran data dan sistem yang bisa dieksploitasi oleh pihak yang tidak bertanggung jawab.
