# Panduan Error Handling & API Response MWT

Setiap baris kode yang berpotensi memunculkan kesalahan (*error*), terutama yang berinteraksi dengan API eksternal atau Database kustom, WAJIB di-handle dengan baik agar aplikasi tidak mengalami *crash* di depan pengguna.

## 1. Aturan Try-Catch di Controller
Semua logika kompleks di Controller wajib dibungkus dengan blok `try-catch`. 

**Contoh yang Benar:**
```php
public function store(Request $request)
{
    DB::beginTransaction();
    try {
        // Logika bisnis...
        DB::commit();
        return redirect()->route('dashboard')->with('success', 'Data berhasil disimpan.');
    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Gagal menyimpan data: ' . $e->getMessage());
        return back()->with('error', 'Terjadi kesalahan sistem. Silakan coba lagi.');
    }
}
```
**Larangan Keras:** Jangan pernah menampilkan pesan error asli `$e->getMessage()` ke user akhir di *production* karena berpotensi membocorkan struktur database!

## 2. Standar Response API (JSON)
Semua API MWT harus merespons dengan format standar berikut agar *frontend developer* mudah melakukan validasi.

**Response Sukses (200 OK):**
```json
{
    "success": true,
    "message": "Data pengguna berhasil diambil.",
    "data": {
        "id": 1,
        "name": "Budi Santoso"
    }
}
```

**Response Gagal / Validasi (422 / 500):**
```json
{
    "success": false,
    "message": "Validasi gagal. Pastikan email belum terdaftar.",
    "data": null,
    "errors": {
        "email": ["Email sudah digunakan."]
    }
}
```

## 3. Log Error (Telescope / Sentry)
Jika menggunakan Laravel Telescope atau layanan seperti Sentry, pastikan Anda menggunakan fasad `Log::error()` untuk setiap pengecualian yang tertangkap di dalam fungsi utama.
