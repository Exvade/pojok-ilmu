<?php

use App\Http\Controllers\Admin\QuizController;
use Illuminate\Support\Facades\Route;

test('rute hapus soal memanggil metode penghapusan soal evaluasi', function () {
    $route = Route::getRoutes()->getByName('admin.questions.destroy');

    expect($route)->not->toBeNull()
        ->and($route->getActionName())->toBe(QuizController::class . '@destroyQuestion');
});
