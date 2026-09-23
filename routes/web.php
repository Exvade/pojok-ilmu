<?php

use App\Http\Controllers\Admin\QuizController as AdminQuizController;
use App\Http\Controllers\Admin\SkillController;
use App\Http\Controllers\Admin\SkillMatrixController;
use App\Http\Controllers\Admin\TrainingController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Operator\DashboardController as OperatorDashboardController;
use App\Http\Controllers\Operator\MaterialController as OperatorMaterialController;
use App\Http\Controllers\Operator\QuizController as OperatorQuizController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Root Route
Route::get('/', function () {
    if (Auth::check()) {
        return Auth::user()->isManagement()
            ? redirect()->route('admin.dashboard')
            : redirect()->route('operator.dashboard');
    }

    return redirect()->route('login');
});

// Guest Auth Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.post');
    Route::get('/login/scan', [LoginController::class, 'showScanner'])->name('login.scan');
    Route::post('/login/scan', [LoginController::class, 'loginViaScan'])->name('login.scan.post');
});

// Logout Route
Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

// Admin & Management Routes
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [SkillMatrixController::class, 'index'])->name('dashboard');
    Route::post('/reset-remedi/{enrollmentId}', [SkillMatrixController::class, 'resetRemedi'])->name('reset-remedi');

    // Skills & Target Levels
    Route::get('/skills', [SkillController::class, 'index'])->name('skills.index');
    Route::post('/skills', [SkillController::class, 'store'])->name('skills.store');
    Route::post('/skills/target-level', [SkillController::class, 'storeTargetLevel'])->name('skills.target-level');
    Route::delete('/skills/{id}', [SkillController::class, 'destroy'])->name('skills.destroy');

    // Trainings & Materials
    Route::get('/trainings', [TrainingController::class, 'index'])->name('trainings.index');
    Route::post('/trainings', [TrainingController::class, 'store'])->name('trainings.store');
    Route::get('/trainings/{id}', [TrainingController::class, 'show'])->name('trainings.show');
    Route::post('/trainings/{id}/materials', [TrainingController::class, 'storeMaterial'])->name('trainings.materials.store');
    Route::delete('/materials/{id}', [TrainingController::class, 'destroyMaterial'])->name('materials.destroy');
    Route::delete('/trainings/{id}', [TrainingController::class, 'destroy'])->name('trainings.destroy');

    // Quizzes & Questions
    Route::post('/quizzes', [AdminQuizController::class, 'store'])->name('quizzes.store');
    Route::post('/quizzes/{id}/questions', [AdminQuizController::class, 'storeQuestion'])->name('quizzes.questions.store');
    Route::delete('/questions/{id}', [AdminQuizController::class, 'destroyQuestion'])->name('questions.destroy');
});

// Operator Portal Routes
Route::middleware('auth')->prefix('operator')->name('operator.')->group(function () {
    Route::get('/dashboard', [OperatorDashboardController::class, 'index'])->name('dashboard');
    Route::get('/materials/{trainingId}', [OperatorMaterialController::class, 'show'])->name('materials.show');
    Route::get('/quizzes/{quizId}', [OperatorQuizController::class, 'show'])->name('quizzes.show');
    Route::post('/quizzes/{quizId}/submit', [OperatorQuizController::class, 'submit'])->name('quizzes.submit');
});
