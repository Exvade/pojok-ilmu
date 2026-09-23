# Aturan Khusus MWT Starter Kit (Wajib Dibaca AI)

Jika Anda adalah sebuah AI Assistant (Copilot, Windsurf, dll), Anda **WAJIB** mematuhi aturan spesifik perusahaan berikut ini sebelum membuat atau memodifikasi kode:

## 1. Aturan UI/UX & Frontend (SANGAT PENTING UNTUK AI)
- **Action Buttons (Tabel/Card):** Jika Anda membuat tombol aksi (*Action Buttons*) seperti "Edit", "Hapus", atau "Detail" di dalam sebuah *Data Table* atau *Card*, **WAJIB menggunakan ikon SVG** (bukan teks biasa seperti tulisan "Edit"). Gunakan *icon* ringan (contoh: Heroicons) untuk menghemat ruang dan meningkatkan estetika UI.
- **Tailwind v4 & Warna MWT:** Proyek ini menggunakan Tailwind v4 (konfigurasi ada di `resources/css/app.css`, BUKAN `tailwind.config.js`). Warna identitas perusahaan **SUDAH TERSEDIA** yaitu: `brand-dark`, `brand-light`, `brand-surface`, `brand-text`. Anda **WAJIB** menggunakan warna ini (contoh: `bg-brand-dark`, `text-brand-light`) dan JANGAN pernah menyimpulkan bahwa warna tersebut belum ada di CSS.
- **Logo Perusahaan:** Anda **WAJIB** menggunakan logo MWT yang sudah tersedia di folder `public/assets/images/logo/` (misalnya `logo-landscape.webp` atau `logo-square.webp`). **DILARANG KERAS** menggunakan logo atau avatar *dummy* (seperti inisial huruf, ui-avatars.com, atau placehold.co) untuk merepresentasikan *brand* perusahaan.
- **Komponen Wajib:** Selalu gunakan komponen bawaan Blade yang ada di `resources/views/components/` (contoh: `<x-button>`, `<x-input>`, `<x-select>`, `<x-card>`, dll). Dilarang keras merakit tag HTML dasar jika komponennya sudah ada.
- **Notifikasi (SweetAlert2):** **DILARANG KERAS** menggunakan `alert()` atau `confirm()` bawaan browser Javascript. Gunakan SweetAlert2 (`Swal.fire`) di sisi Frontend, atau kirimkan via Controller (`return back()->with('success', '...')`).
- **Dilarang Pakai Emoji:** Jangan pernah menggunakan emoji bawaan keyboard (seperti ❌, ✅, 🗑️) di antarmuka web. WAJIB gunakan ikon SVG (seperti Heroicons).
- **Responsive:** Setiap antarmuka yang dibuat wajib *responsive* di segala ukuran layar.
- **Copywriting:** Sistem internal WAJIB berbahasa Indonesia baku. Gunakan istilah `Tambah`, `Simpan`, `Ubah`, `Hapus` (JANGAN gunakan *Create*, *Submit*, *Update*, *Delete*).
- **Catatan Desain Lengkap:** Anda **WAJIB** membaca file `docs/mwt-ui-ux.md` sebelum menyentuh file tampilan (Blade/CSS).

## 2. Aturan Database & Model
- **Penamaan Tabel:** Wajib menggunakan Bahasa Inggris, *Plural*, dan *snake_case* (contoh: `employee_salaries`).
- **Migrasi:** DILARANG menggunakan `$table->timestamps()`. WAJIB gunakan `$table->mwtTimestamps()` (yang akan membuat `created_at`, `updated_at`, `created_by`, `updated_by`).
- **UUID & SoftDeletes:** Gunakan UUID (`$table->uuid('id')->primary()`) untuk data publik/sensitif. WAJIB tambahkan `$table->softDeletes()` untuk tabel data utama.
- **Traceability:** Jangan isi `created_by` atau `updated_by` secara manual di Controller. Biarkan `BaseModel` yang mengurusnya secara otomatis via Model Events.
- **Model:** Semua model wajib meng-*extend* `App\Models\BaseModel`, bukan Model bawaan Laravel, karena aturan `$guarded = []` sudah ditetapkan di sana. *(Kecuali: Model otentikasi seperti `User` tetap meng-extend `Authenticatable` bawaan Laravel)*.

## 3. Aturan Error Handling & API
- **Try-Catch & Transaksi:** Seluruh logika *database* yang kompleks di Controller WAJIB dibungkus menggunakan `DB::beginTransaction()`, `try {} catch() {}`, dan `DB::rollBack()`.
- **Response API:** Setiap *endpoint* API (JSON) wajib mengembalikan format baku: `{ success: bool, message: string, data: any|null, errors: object|null }`.

## 4. Aturan Standar Kode & Git
- Kode harus mematuhi konfigurasi **Laravel Pint** yang telah ditetapkan di `pint.json`.
- Jangan mencoba untuk melangkahi *Git Hooks* (Husky) yang akan merapikan kode sebelum *commit*.

## 5. Standar Git & Commit
- Gunakan *Conventional Commits* (`feat:`, `fix:`, `docs:`, dll).
- Pesan *commit* harus menggunakan Bahasa Indonesia yang jelas.
- DILARANG menggunakan perintah `--no-verify` saat melakukan commit.

## 6. Kerapian Kode & Format (Penting untuk AI)
- **WAJIB JALANKAN PINT:** Jika Anda (AI Assistant) selesai membuat atau memodifikasi file PHP apa pun, Anda **WAJIB** mengeksekusi perintah `./vendor/bin/pint` di terminal sebelum menyelesaikan *task* atau sebelum Anda melakukan *commit*. 
- CI/CD MWT sangat ketat. Jangan biarkan kode dengan *unused imports*, spasi berantakan, atau *syntax* yang tidak standar masuk ke GitHub!


---

<laravel-boost-guidelines>
=== foundation rules ===

# Laravel Boost Guidelines

The Laravel Boost guidelines are specifically curated by Laravel maintainers for this application. These guidelines should be followed closely to ensure the best experience when building Laravel applications.

## Foundational Context

This application is a Laravel application and its main Laravel ecosystems package & versions are below. You are an expert with them all. Ensure you abide by these specific packages & versions.

- php - 8.2
- laravel/framework (LARAVEL) - v12
- laravel/prompts (PROMPTS) - v0
- laravel/boost (BOOST) - v2
- laravel/mcp (MCP) - v0
- laravel/pail (PAIL) - v1
- laravel/pint (PINT) - v1
- laravel/sail (SAIL) - v1
- pestphp/pest (PEST) - v3
- phpunit/phpunit (PHPUNIT) - v11
- tailwindcss (TAILWINDCSS) - v4

## Skills Activation

This project has domain-specific skills available in `**/skills/**`. You MUST activate the relevant skill whenever you work in that domain—don't wait until you're stuck.

## Conventions

- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure, approach, and naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Verification Scripts

- Do not create verification scripts or tinker when tests cover that functionality and prove they work. Unit and feature tests are more important.

## Application Structure & Architecture

- Stick to existing directory structure; don't create new base folders without approval.
- Do not change the application's dependencies without approval.

## Frontend Bundling

- If the user doesn't see a frontend change reflected in the UI, it could mean they need to run `npm run build`, `npm run dev`, or `composer run dev`. Ask them.

## Documentation Files

- You must only create documentation files if explicitly requested by the user.

## Replies

- Be concise in your explanations - focus on what's important rather than explaining obvious details.

=== boost rules ===

# Laravel Boost

## Artisan

- Run Artisan commands directly via the command line (e.g., `php artisan route:list`). Use `php artisan list` to discover available commands and `php artisan [command] --help` to check parameters.
- Inspect routes with `php artisan route:list`. Filter with: `--method=GET`, `--name=users`, `--path=api`, `--except-vendor`, `--only-vendor`.
- Read configuration values using dot notation: `php artisan config:show app.name`, `php artisan config:show database.default`. Or read config files directly from the `config/` directory.

## Tinker

- Execute PHP in app context for debugging and testing code. Do not create models without user approval, prefer tests with factories instead. Prefer existing Artisan commands over custom tinker code.
- Always use single quotes to prevent shell expansion: `php artisan tinker --execute 'Your::code();'`
  - Double quotes for PHP strings inside: `php artisan tinker --execute 'User::where("active", true)->count();'`

=== php rules ===

# PHP

- Always use curly braces for control structures, even for single-line bodies.
- Use PHP 8 constructor property promotion: `public function __construct(public GitHub $github) { }`. Do not leave empty zero-parameter `__construct()` methods unless the constructor is private.
- Use explicit return type declarations and type hints for all method parameters: `function isAccessible(User $user, ?string $path = null): bool`
- Use TitleCase for Enum keys: `FavoritePerson`, `BestLake`, `Monthly`.
- Prefer PHPDoc blocks over inline comments. Only add inline comments for exceptionally complex logic.
- Use array shape type definitions in PHPDoc blocks.

=== deployments rules ===

# Deployment

- Laravel can be deployed using [Laravel Cloud](https://cloud.laravel.com/), which is the fastest way to deploy and scale production Laravel applications.

=== laravel/core rules ===

# Do Things the Laravel Way

- Use `php artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using `php artisan list` and check their parameters with `php artisan [command] --help`.
- If you're creating a generic PHP class, use `php artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

### Model Creation

- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `php artisan make:model --help` to check the available options.

## APIs & Eloquent Resources

- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.

## URL Generation

- When generating links to other pages, prefer named routes and the `route()` function.

## Testing

- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.
- When creating tests, make use of `php artisan make:test [options] {name}` to create a feature test, and pass `--unit` to create a unit test. Most tests should be feature tests.

## Vite Error

- If you receive an "Illuminate\Foundation\ViteException: Unable to locate file in Vite manifest" error, you can run `npm run build` or ask the user to run `npm run dev` or `composer run dev`.

=== laravel/v12 rules ===

# Laravel 12

- Since Laravel 11, Laravel has a new streamlined file structure which this project uses.

## Laravel 12 Structure

- In Laravel 12, middleware are no longer registered in `app/Http/Kernel.php`.
- Middleware are configured declaratively in `bootstrap/app.php` using `Application::configure()->withMiddleware()`.
- `bootstrap/app.php` is the file to register middleware, exceptions, and routing files.
- `bootstrap/providers.php` contains application specific service providers.
- The `app/Console/Kernel.php` file no longer exists; use `bootstrap/app.php` or `routes/console.php` for console configuration.
- Console commands in `app/Console/Commands/` are automatically available and do not require manual registration.

## Database

- When modifying a column, the migration must include all of the attributes that were previously defined on the column. Otherwise, they will be dropped and lost.
- Laravel 12 allows limiting eagerly loaded records natively, without external packages: `$query->latest()->limit(10);`.

### Models

- Casts can and likely should be set in a `casts()` method on a model rather than the `$casts` property. Follow existing conventions from other models.

=== pint/core rules ===

# Laravel Pint Code Formatter

- If you have modified any PHP files, you must run `vendor/bin/pint --dirty --format agent` before finalizing changes to ensure your code matches the project's expected style.
- Do not run `vendor/bin/pint --test --format agent`, simply run `vendor/bin/pint --format agent` to fix any formatting issues.

=== pest/core rules ===

## Pest

- This project uses Pest for testing. Create tests: `php artisan make:test --pest {name}`.
- The `{name}` argument should not include the test suite directory. Use `php artisan make:test --pest SomeFeatureTest` instead of `php artisan make:test --pest Feature/SomeFeatureTest`.
- Run tests: `php artisan test --compact` or filter: `php artisan test --compact --filter=testName`.
- Do NOT delete tests without approval.

</laravel-boost-guidelines>
