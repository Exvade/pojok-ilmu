<?php

use App\Http\Controllers\Admin\QuizController;
use Illuminate\Support\Facades\Route;

test('rute hapus soal memanggil metode penghapusan soal evaluasi', function () {
    $route = Route::getRoutes()->getByName('admin.questions.destroy');

    expect($route)->not->toBeNull()
        ->and($route->getActionName())->toBe(QuizController::class . '@destroyQuestion');
});

test('rute ubah soal memanggil metode pembaruan soal evaluasi', function () {
    $route = Route::getRoutes()->getByName('admin.questions.update');

    expect($route)->not->toBeNull()
        ->and($route->methods())->toContain('PUT')
        ->and($route->getActionName())->toBe(QuizController::class . '@updateQuestion');
});
