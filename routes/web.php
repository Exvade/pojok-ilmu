<?php

use App\Http\Controllers\Admin\QuizController as AdminQuizController;
use App\Http\Controllers\Admin\RoleController;
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
    Route::post('/reset-remedi/{enrollmentId}', [SkillMatrixController::class, 'resetRemedi'])->middleware('management-permission:competency')->name('reset-remedi');

    // Skills & Target Levels
    Route::get('/skills', [SkillController::class, 'index'])->middleware('management-permission:competency')->name('skills.index');
    Route::post('/skills', [SkillController::class, 'store'])->middleware('management-permission:competency')->name('skills.store');
    Route::post('/skills/target-level', [SkillController::class, 'storeTargetLevel'])->middleware('management-permission:competency')->name('skills.target-level');
    Route::delete('/skills/{id}', [SkillController::class, 'destroy'])->middleware('management-permission:competency')->name('skills.destroy');

    // Trainings & Materials
    Route::get('/trainings', [TrainingController::class, 'index'])->middleware('management-permission:curriculum')->name('trainings.index');
    Route::post('/trainings', [TrainingController::class, 'store'])->middleware('management-permission:curriculum')->name('trainings.store');
    Route::get('/trainings/{id}', [TrainingController::class, 'show'])->middleware('management-permission:curriculum')->name('trainings.show');
    Route::post('/trainings/{id}/materials', [TrainingController::class, 'storeMaterial'])->middleware('management-permission:curriculum')->name('trainings.materials.store');
    Route::delete('/materials/{id}', [TrainingController::class, 'destroyMaterial'])->middleware('management-permission:curriculum')->name('materials.destroy');
    Route::delete('/trainings/{id}', [TrainingController::class, 'destroy'])->middleware('management-permission:curriculum')->name('trainings.destroy');

    // Quizzes & Questions
    Route::post('/quizzes', [AdminQuizController::class, 'store'])->middleware('management-permission:curriculum')->name('quizzes.store');
    Route::post('/quizzes/{id}/questions', [AdminQuizController::class, 'storeQuestion'])->middleware('management-permission:curriculum')->name('quizzes.questions.store');
    Route::delete('/questions/{id}', [AdminQuizController::class, 'destroy'])->middleware('management-permission:curriculum')->name('questions.destroy');

    Route::get('/roles', [RoleController::class, 'index'])->middleware('management-permission:superadmin')->name('roles.index');
    Route::get('/roles/{manpower}/edit', [RoleController::class, 'edit'])->middleware('management-permission:superadmin')->name('roles.edit');
    Route::put('/roles/{manpower}', [RoleController::class, 'update'])->middleware('management-permission:superadmin')->name('roles.update');
});

// Operator Portal Routes
Route::middleware(['auth', 'active-operator'])->prefix('operator')->name('operator.')->group(function () {
    Route::get('/dashboard', [OperatorDashboardController::class, 'index'])->name('dashboard');
    Route::get('/materials/{trainingId}', [OperatorMaterialController::class, 'show'])->name('materials.show');
    Route::get('/quizzes/{quizId}', [OperatorQuizController::class, 'show'])->name('quizzes.show');
    Route::post('/quizzes/{quizId}/submit', [OperatorQuizController::class, 'submit'])->name('quizzes.submit');
});
