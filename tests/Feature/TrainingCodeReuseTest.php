<?php

use App\Models\MSkill;
use App\Models\Training;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('kode training dapat dipakai ulang setelah training dihapus', function () {
    $skill = MSkill::create([
        'kode_skill' => 'KODE-ULANG',
        'nama_skill' => 'Kode training ulang',
        'kategori' => 'Pengujian',
    ]);

    $deletedTraining = Training::create([
        'kode_training' => 'TRAINING-ULANG',
        'skill_id' => $skill->id,
        'level' => 1,
        'judul' => 'Training lama',
    ]);

    $deletedTraining->delete();

    expect(Training::withTrashed()->findOrFail($deletedTraining->id)->kode_training)
        ->toStartWith('TRAINING-ULANG--DIHAPUS--');

    $newTraining = Training::create([
        'kode_training' => 'TRAINING-ULANG',
        'skill_id' => $skill->id,
        'level' => 1,
        'judul' => 'Training baru',
    ]);

    expect($newTraining->kode_training)->toBe('TRAINING-ULANG')
        ->and(Training::where('kode_training', 'TRAINING-ULANG')->count())->toBe(1);
});
