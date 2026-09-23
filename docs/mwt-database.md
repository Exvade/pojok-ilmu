# Panduan Basis Data (Database) MWT

Untuk menjaga konsistensi antar sistem dan kemudahan migrasi di masa depan, seluruh pembuatan tabel dan relasi *database* di PT MWT wajib mematuhi konvensi berikut.

## 1. Penamaan Tabel (Table Naming Convention)
Semua tabel di dalam *database* (MySQL/PostgreSQL) **WAJIB** menggunakan:
- Bahasa Inggris.
- Berbentuk Jamak (*Plural*).
- Ditulis dengan gaya `snake_case`.

**Contoh Benar:** `users`, `employee_salaries`, `company_assets`.
**Contoh Salah:** `tb_user`, `GajiKaryawan`, `Asset`.

## 2. Primary Key dan UUID
Secara default, gunakan `id` (Auto Increment / BigInt) untuk tabel internal yang aman.
Namun, jika tabel menyimpan data sensitif yang URL-nya bisa dibaca publik (misal: Transaksi, Invoice, File Rahasia), **WAJIB menggunakan UUID** agar tidak mudah ditebak (*ID enumeration attack*).

**Contoh Migration dengan UUID:**
```php
$table->uuid('id')->primary();
```

## 3. Penghapusan Data (Soft Deletes)
Semua tabel transaksi dan master data krusial **WAJIB** menerapkan mekanisme `SoftDeletes`. Dilarang menggunakan fungsi hapus permanen (hard delete) kecuali untuk tabel perantara (*pivot table*).

Tambahkan pada Migration:
```php
$table->softDeletes();
```
Tambahkan pada Model:
```php
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model {
    use SoftDeletes;
}
```

## 4. Timestamps
Semua tabel wajib memiliki fungsi `$table->timestamps();` untuk mencatat `created_at` dan `updated_at`. Jangan menonaktifkan properti `public $timestamps = false;` di Model tanpa persetujuan Lead Developer.
