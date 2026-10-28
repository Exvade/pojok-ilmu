<?php

use App\Models\MSkill;
use App\Models\S2sProduct;
use App\Models\Training;
use App\Models\User;
use App\Services\S2sProductSyncService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    config()->set('services.s2s.base_url', 'https://s2s.test');
    config()->set('services.s2s.parts_token', 'token-pengujian');
});

test('sinkronisasi menyimpan katalog produk dari s2s', function () {
    Http::fake([
        'https://s2s.test/api/integrations/v1/parts*' => Http::response([
            'data' => [
                ['kode_part' => 'BOX-BATTERY', 'nama_part' => 'Box Battery', 'status' => 'aktif', 'updated_at' => '2026-10-28T08:00:00+07:00'],
                ['kode_part' => 'PART-LAMA', 'nama_part' => 'Part Lama', 'status' => 'nonaktif', 'updated_at' => '2026-10-28T09:00:00+07:00'],
            ],
            'meta' => ['page' => 1, 'per_page' => 500, 'total' => 2],
        ]),
    ]);

    $result = app(S2sProductSyncService::class)->sync();

    expect($result['product_count'])->toBe(2);
    $this->assertDatabaseHas('s2s_products', ['kode_part' => 'BOX-BATTERY', 'nama_part' => 'Box Battery', 'status' => 'aktif']);
    $this->assertDatabaseHas('s2s_products', ['kode_part' => 'PART-LAMA', 'status' => 'nonaktif']);
    Http::assertSent(fn ($request): bool => $request->hasHeader('Authorization', 'Bearer token-pengujian')
        && str_contains($request->url(), 'per_page=500'));
});

test('pengelola kurikulum dapat menghubungkan training dengan produk s2s', function () {
    $admin = User::create(['user_id' => 'ADMIN-S2S', 'name' => 'Pengelola Kurikulum', 'role' => 1, 'can_manage_curriculum' => true]);
    $product = S2sProduct::create(['kode_part' => 'BOX-BATTERY', 'nama_part' => 'Box Battery', 'status' => 'aktif']);

    $this->actingAs($admin)->post(route('admin.trainings.store'), [
        'kode_training' => 'BOX-BATTERY-L1',
        'departemens' => ['PRODUKSI PLASTIK A'],
        'level' => 1,
        'judul' => 'Pengenalan Box Battery',
        'deskripsi' => 'Pelatihan produk.',
        'max_retries' => 3,
        'passing_grade' => 80,
        's2s_product_ids' => [$product->id],
    ])->assertSessionHas('success');

    $training = Training::where('kode_training', 'BOX-BATTERY-L1')->firstOrFail();
    expect($training->s2sProducts->pluck('kode_part')->all())->toBe(['BOX-BATTERY']);
    expect(MSkill::where('kode_skill', 'BOX-BATTERY-L1')->exists())->toBeTrue();
});

test('halaman produk s2s tersedia untuk pengelola kurikulum', function () {
    $admin = User::create(['user_id' => 'ADMIN-S2S-PRODUK', 'name' => 'Pengelola Kurikulum', 'role' => 1, 'can_manage_curriculum' => true]);
    S2sProduct::create(['kode_part' => 'BOX-BATTERY', 'nama_part' => 'Box Battery', 'status' => 'aktif']);

    $this->actingAs($admin)->get(route('admin.s2s-products.index'))
        ->assertOk()
        ->assertSee('Produk S2S')
        ->assertSee('BOX-BATTERY');
});

test('halaman training menampilkan pencarian produk dengan istilah yang tepat', function () {
    $admin = User::create(['user_id' => 'ADMIN-S2S-TRAINING', 'name' => 'Pengelola Kurikulum', 'role' => 1, 'can_manage_curriculum' => true]);
    S2sProduct::create(['kode_part' => 'BOX-BATTERY', 'nama_part' => 'Box Battery', 'status' => 'aktif']);

    $this->actingAs($admin)->get(route('admin.trainings.index'))
        ->assertOk()
        ->assertSee('Cari kode atau nama produk...')
        ->assertSee('Produk tidak ditemukan.');
});

test('hanya superadmin yang dapat menjalankan sinkronisasi produk s2s', function () {
    $curriculumManager = User::create(['user_id' => 'ADMIN-S2S-TERBATAS', 'name' => 'Pengelola Kurikulum', 'role' => 1, 'can_manage_curriculum' => true]);

    $this->actingAs($curriculumManager)
        ->post(route('admin.s2s-products.sync'))
        ->assertRedirect(route('login'));
});

test('audit mapping menandai training yang belum dihubungkan ke produk s2s', function () {
    $admin = User::create(['user_id' => 'ADMIN-S2S-AUDIT', 'name' => 'Pengelola Kurikulum', 'role' => 1, 'can_manage_curriculum' => true]);
    $skill = MSkill::create(['kode_skill' => 'S2S-AUDIT', 'nama_skill' => 'Training Audit', 'kategori' => 'IT']);
    Training::create(['kode_training' => 'S2S-AUDIT-L1', 'skill_id' => $skill->id, 'departemen' => 'IT', 'level' => 1, 'judul' => 'Training Belum Terhubung']);

    $this->actingAs($admin)->get(route('admin.s2s-product-mapping-audit.index', ['status' => 'unmapped']))
        ->assertOk()
        ->assertSee('Training Belum Terhubung')
        ->assertSee('Belum dihubungkan');
});
