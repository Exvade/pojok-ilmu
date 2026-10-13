<?php

use App\Http\Controllers\Admin\ImportController;
use App\Http\Controllers\Admin\OjtController;
use App\Http\Controllers\Admin\ProductAssignmentController;
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
    Route::get('/imports/assignments', [ImportController::class, 'createAssignments'])->middleware('management-permission:competency')->name('assignments.import');
    Route::get('/imports/assignments/template', [ImportController::class, 'assignmentTemplate'])->middleware('management-permission:competency')->name('assignments.template');
    Route::post('/imports/assignments', [ImportController::class, 'storeAssignments'])->middleware('management-permission:competency')->name('assignments.import.store');
    Route::get('/imports/{type}', [ImportController::class, 'create'])->middleware('management-permission:curriculum')->name('imports.create');
    Route::get('/imports/{type}/template', [ImportController::class, 'template'])->middleware('management-permission:curriculum')->name('imports.template');
    Route::post('/imports/{type}', [ImportController::class, 'store'])->middleware('management-permission:curriculum')->name('imports.store');
    Route::get('/product-assignments', [ProductAssignmentController::class, 'index'])->middleware('management-permission:competency')->name('product-assignments.index');
    Route::post('/product-assignments', [ProductAssignmentController::class, 'store'])->middleware('management-permission:competency')->name('product-assignments.store');
    Route::get('/ojt', [OjtController::class, 'index'])->middleware('management-permission:ojt')->name('ojt.index');
    Route::get('/ojt/{enrollment}', [OjtController::class, 'show'])->middleware('management-permission:ojt')->name('ojt.show');
    Route::post('/ojt/{enrollment}', [OjtController::class, 'store'])->middleware('management-permission:ojt')->name('ojt.store');

    // Skills & Target Levels
    Route::get('/skills', [SkillController::class, 'index'])->middleware('management-permission:competency')->name('skills.index');
    Route::post('/skills', [SkillController::class, 'store'])->middleware('management-permission:competency')->name('skills.store');
    Route::post('/skills/target-level', [SkillController::class, 'storeTargetLevel'])->middleware('management-permission:competency')->name('skills.target-level');
    Route::delete('/skills/{id}', [SkillController::class, 'destroy'])->middleware('management-permission:competency')->name('skills.destroy');

    // Trainings & Materials
    Route::get('/trainings', [TrainingController::class, 'index'])->middleware('management-permission:curriculum')->name('trainings.index');
    Route::post('/trainings', [TrainingController::class, 'store'])->middleware('management-permission:curriculum')->name('trainings.store');
    Route::put('/trainings/{id}', [TrainingController::class, 'update'])->middleware('management-permission:curriculum')->name('trainings.update');
    Route::get('/trainings/{id}', [TrainingController::class, 'show'])->middleware('management-permission:curriculum')->name('trainings.show');
    Route::post('/trainings/{id}/materials', [TrainingController::class, 'storeMaterial'])->middleware('management-permission:curriculum')->name('trainings.materials.store');
    Route::delete('/materials/{id}', [TrainingController::class, 'destroyMaterial'])->middleware('management-permission:curriculum')->name('materials.destroy');
    Route::delete('/trainings/{id}', [TrainingController::class, 'destroy'])->middleware('management-permission:curriculum')->name('trainings.destroy');

    // Quizzes & Questions
    Route::post('/quizzes', [AdminQuizController::class, 'store'])->middleware('management-permission:curriculum')->name('quizzes.store');
    Route::post('/trainings/{trainingId}/evaluation-set', [AdminQuizController::class, 'prepareEvaluationSet'])->middleware('management-permission:curriculum')->name('trainings.evaluation-set.prepare');
    Route::post('/quizzes/{id}/questions', [AdminQuizController::class, 'storeQuestion'])->middleware('management-permission:curriculum')->name('quizzes.questions.store');
    Route::post('/questions', [AdminQuizController::class, 'storeQuestionGeneric'])->middleware('management-permission:curriculum')->name('questions.store');
    Route::delete('/questions/{id}', [AdminQuizController::class, 'destroy'])->middleware('management-permission:curriculum')->name('questions.destroy');

    Route::get('/roles', [RoleController::class, 'index'])->middleware('management-permission:superadmin')->name('roles.index');
    Route::get('/roles/{manpower}/edit', [RoleController::class, 'edit'])->middleware('management-permission:superadmin')->name('roles.edit');
    Route::put('/roles/{manpower}', [RoleController::class, 'update'])->middleware('management-permission:superadmin')->name('roles.update');
});

// Operator Portal Routes
Route::middleware(['auth', 'active-operator'])->prefix('operator')->name('operator.')->group(function () {
    Route::get('/dashboard', [OperatorDashboardController::class, 'index'])->name('dashboard');
    Route::get('/materials/{trainingId}', [OperatorMaterialController::class, 'show'])->name('materials.show');
    Route::post('/materials/{trainingId}/{materialId}/complete', [OperatorMaterialController::class, 'completeMaterial'])->name('materials.item.complete');
    Route::post('/materials/{trainingId}/complete', [OperatorMaterialController::class, 'complete'])->name('materials.complete');
    Route::get('/quizzes/{quizId}', [OperatorQuizController::class, 'show'])->name('quizzes.show');
    Route::post('/quizzes/{quizId}/submit', [OperatorQuizController::class, 'submit'])->name('quizzes.submit');
});
