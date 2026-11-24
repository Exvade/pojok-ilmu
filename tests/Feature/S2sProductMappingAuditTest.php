<?php

use App\Models\MManpower;
use App\Models\MSkill;
use App\Models\S2sProduct;
use App\Models\Training;
use App\Models\TrainingEnrollment;
use App\Models\User;
use App\Services\S2sProductSuggestionService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function mappingAuditAdmin(): User
{
    return User::create(['user_id' => 'ADMIN-MAP', 'name' => 'Pengelola Kurikulum', 'role' => 1, 'can_manage_curriculum' => true]);
}

function mappingAuditTraining(string $code, string $title, array $attributes = []): Training
{
    $skill = MSkill::firstOrCreate(['kode_skill' => 'MAP-SKILL'], ['nama_skill' => 'Produk', 'kategori' => 'Produksi']);

    return Training::create(['kode_training' => $code, 'skill_id' => $skill->id, 'level' => 1, 'judul' => $title, 'departemen' => 'PRODUKSI PLASTIK A', ...$attributes]);
}

test('saran produk mencocokkan kata pada nama part tanpa memperhatikan tanda baca dan kata assy', function () {
    $products = collect([
        S2sProduct::create(['kode_part' => '6433A-K1A', 'nama_part' => 'BOX ASSY, BATTERY', 'status' => 'aktif']),
        S2sProduct::create(['kode_part' => '64330-K0J', 'nama_part' => 'BOX, BATTERY', 'status' => 'aktif']),
        S2sProduct::create(['kode_part' => '6431A-GJR', 'nama_part' => 'CASE,BATTERY ASSY', 'status' => 'aktif']),
        S2sProduct::create(['kode_part' => '1757A-K59', 'nama_part' => 'COVER, FUEL TANK ASSY', 'status' => 'aktif']),
    ]);

    $service = app(S2sProductSuggestionService::class);

    expect($service->suggest(mappingAuditTraining('MAP-1', 'BOX BATTERY'), $products)->pluck('kode_part')->all())->toBe(['64330-K0J', '6433A-K1A'])
        ->and($service->suggest(mappingAuditTraining('MAP-2', 'cover assy fuel tank'), $products)->pluck('kode_part')->all())->toBe(['1757A-K59'])
        ->and($service->suggest(mappingAuditTraining('MAP-3', 'Budaya Kerja 5R'), $products))->toBeEmpty();
});

test('pengelola dapat menghubungkan produk dari saran di halaman audit', function () {
    $training = mappingAuditTraining('MAP-BOX', 'BOX BATTERY');
    $product = S2sProduct::create(['kode_part' => '64330-K0J', 'nama_part' => 'BOX, BATTERY', 'status' => 'aktif']);
    S2sProduct::create(['kode_part' => 'LAMA-01', 'nama_part' => 'BOX BATTERY', 'status' => 'nonaktif']);

    $this->actingAs(mappingAuditAdmin())->get(route('admin.s2s-product-mapping-audit.index'))->assertOk()
        ->assertSee('Saran produk dengan nama mirip')
        ->assertSee('64330-K0J')
        ->assertDontSee('LAMA-01')
        ->assertSee('Jadikan training umum');

    $this->post(route('admin.s2s-product-mapping-audit.attach', $training->id), ['product_ids' => [$product->id]])
        ->assertSessionHas('success');

    expect($training->fresh()->s2sProducts->pluck('kode_part')->all())->toBe(['64330-K0J']);

    $this->post(route('admin.s2s-product-mapping-audit.attach', $training->id), ['product_ids' => []])
        ->assertSessionHasErrors('product_ids');
});

test('training umum tidak dihitung belum dihubungkan dan tampil umum pada tabel operator', function () {
    $training = mappingAuditTraining('MAP-5R', 'Budaya Kerja 5R');
    mappingAuditTraining('MAP-LAIN', 'Training Lain');

    $this->actingAs(mappingAuditAdmin())->post(route('admin.s2s-product-mapping-audit.general', $training->id), ['is_general' => 1])
        ->assertSessionHas('success');
    expect($training->fresh()->is_general)->toBeTrue();

    $summary = $this->get(route('admin.s2s-product-mapping-audit.index'))->assertOk()
        ->assertSee('Batalkan training umum')
        ->viewData('summary');
    expect($summary['general'])->toBe(1)->and($summary['unmapped'])->toBe(1);

    $operator = MManpower::create(['mp_id' => 'MAP-OP', 'nik' => 'MAP-OP', 'nama' => 'Operator Umum', 'departemen' => 'PRODUKSI PLASTIK A', 'bagian' => 'OPERATOR', 'status' => true]);
    TrainingEnrollment::create(['training_id' => $training->id, 'manpower_id' => $operator->id, 'status' => 'enrolled']);
    $this->actingAs(User::create(['user_id' => 'MAP-OP', 'name' => 'Operator Umum', 'role' => 0]))
        ->get(route('operator.dashboard'))->assertOk()
        ->assertSeeInOrder(['Budaya Kerja 5R', 'Umum']);

    $this->actingAs(User::where('user_id', 'ADMIN-MAP')->first())
        ->post(route('admin.s2s-product-mapping-audit.general', $training->id), ['is_general' => 0]);
    expect($training->fresh()->is_general)->toBeFalse();
});

test('centang training umum di form training mengosongkan produk', function () {
    $product = S2sProduct::create(['kode_part' => 'BOX-01', 'nama_part' => 'BOX BATTERY', 'status' => 'aktif']);

    $this->actingAs(mappingAuditAdmin())->post(route('admin.trainings.store'), [
        'kode_training' => 'UMUM-01',
        'departemens' => ['PRODUKSI PLASTIK A'],
        'level' => 1,
        'judul' => 'Budaya Kerja 5R',
        'max_retries' => 3,
        'passing_grade' => 80,
        'is_general' => 1,
        's2s_product_ids' => [$product->id],
    ])->assertSessionHas('success');

    $training = Training::where('kode_training', 'UMUM-01')->firstOrFail();
    expect($training->is_general)->toBeTrue()
        ->and($training->s2sProducts)->toBeEmpty();
});
