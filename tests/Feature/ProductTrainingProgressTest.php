<?php

use App\Models\Training;
use App\Models\TrainingEnrollment;
use App\Services\ProductTrainingProgressService;

test('balok dan akses level produk mengikuti post-test serta ojt', function () {
    $service = app(ProductTrainingProgressService::class);
    $levelOneTraining = new Training(['level' => 1]);
    $levelTwoTraining = new Training(['level' => 2]);

    $levelOne = new TrainingEnrollment(['status' => 'in_progress', 'ojt_status' => 'pending', 'post_test_completed_at' => now()]);
    $levelOne->setRelation('training', $levelOneTraining);
    $levelTwo = new TrainingEnrollment(['status' => 'enrolled', 'ojt_status' => 'not_started']);
    $levelTwo->setRelation('training', $levelTwoTraining);
    $enrollments = collect([$levelOne, $levelTwo]);

    expect($service->blockFor($levelOne))->toBe(1)
        ->and($service->isUnlocked($levelTwo, $enrollments))->toBeFalse()
        ->and($service->currentLevel($enrollments))->toBe(0);

    $levelOne->status = 'passed';
    $levelOne->ojt_status = 'approved';

    expect($service->blockFor($levelOne))->toBe(2)
        ->and($service->isUnlocked($levelTwo, $enrollments))->toBeTrue()
        ->and($service->currentLevel($enrollments))->toBe(1);
});
