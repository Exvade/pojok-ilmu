<?php

use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\DepartmentAliasController;
use App\Http\Controllers\Admin\HarisDataQualityController;
use App\Http\Controllers\Admin\HarisSyncController;
use App\Http\Controllers\Admin\ImportController;
use App\Http\Controllers\Admin\OjtController;
use App\Http\Controllers\Admin\ProductAssignmentController;
use App\Http\Controllers\Admin\QuizController as AdminQuizController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\S2sCompetencySyncController;
use App\Http\Controllers\Admin\S2sProductController;
use App\Http\Controllers\Admin\S2sProductMappingAuditController;
use App\Http\Controllers\Admin\SkillController;
use App\Http\Controllers\Admin\SkillMatrixController;
use App\Http\Controllers\Admin\TrainingAuditController;
use App\Http\Controllers\Admin\TrainingController;
use App\Http\Controllers\Admin\TrainingFollowUpController;
use App\Http\Controllers\Admin\TrainingReportController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Operator\DashboardController as OperatorDashboardController;
use App\Http\Controllers\Operator\MaterialController as OperatorMaterialController;
use App\Http\Controllers\Operator\OjtPdfController;
use App\Http\Controllers\Operator\ProfileController as OperatorProfileController;
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
    Route::get('/department-aliases', [DepartmentAliasController::class, 'index'])->middleware('management-permission:curriculum')->name('department-aliases.index');
    Route::post('/department-aliases', [DepartmentAliasController::class, 'store'])->middleware('management-permission:curriculum')->name('department-aliases.store');
    Route::delete('/department-aliases/{departmentAlias}', [DepartmentAliasController::class, 'destroy'])->middleware('management-permission:curriculum')->name('department-aliases.destroy');
    Route::get('/dashboard', [SkillMatrixController::class, 'index'])->name('dashboard');
    Route::get('/manpower/{id}/training-profile', [SkillMatrixController::class, 'showManpower'])->middleware('management-permission:competency')->name('manpower.training-profile');
    Route::get('/imports/assignments', [ImportController::class, 'createAssignments'])->middleware('management-permission:competency')->name('assignments.import');
    Route::get('/imports/assignments/template', [ImportController::class, 'assignmentTemplate'])->middleware('management-permission:competency')->name('assignments.template');
    Route::post('/imports/assignments', [ImportController::class, 'storeAssignments'])->middleware('management-permission:competency')->name('assignments.import.store');
    Route::post('/imports/assignments/confirm', [ImportController::class, 'confirmAssignments'])->middleware('management-permission:competency')->name('assignments.import.confirm');
    Route::get('/imports/{type}', [ImportController::class, 'create'])->middleware('management-permission:curriculum')->name('imports.create');
    Route::get('/imports/{type}/template', [ImportController::class, 'template'])->middleware('management-permission:curriculum')->name('imports.template');
    Route::post('/imports/{type}', [ImportController::class, 'store'])->middleware('management-permission:curriculum')->name('imports.store');
    Route::post('/imports/{type}/confirm', [ImportController::class, 'confirm'])->middleware('management-permission:curriculum')->name('imports.confirm');
    Route::get('/product-assignments', [ProductAssignmentController::class, 'index'])->middleware('management-permission:competency')->name('product-assignments.index');
    Route::get('/product-assignments/{training}/history', [ProductAssignmentController::class, 'history'])->middleware('management-permission:competency')->name('product-assignments.history');
    Route::post('/product-assignments', [ProductAssignmentController::class, 'store'])->middleware('management-permission:competency')->name('product-assignments.store');
    Route::get('/training-reports', [TrainingReportController::class, 'index'])->middleware('management-permission:competency')->name('training-reports.index');
    Route::get('/training-follow-ups', [TrainingFollowUpController::class, 'index'])->middleware('management-permission:competency')->name('training-follow-ups.index');
    Route::get('/training-reports/export', [TrainingReportController::class, 'export'])->middleware('management-permission:competency')->name('training-reports.export');
    Route::get('/ojt', [OjtController::class, 'index'])->middleware('management-permission:ojt')->name('ojt.index');
    Route::get('/ojt/{enrollment}/pdf', [OjtPdfController::class, 'downloadForManagement'])->middleware('management-permission:ojt')->name('ojt.pdf');
    Route::get('/ojt/{enrollment}', [OjtController::class, 'show'])->middleware('management-permission:ojt')->name('ojt.show');
    Route::post('/ojt/{enrollment}', [OjtController::class, 'store'])->middleware('management-permission:ojt')->name('ojt.store');

    // Skills & Target Levels
    Route::get('/skills', [SkillController::class, 'index'])->middleware('management-permission:competency')->name('skills.index');
    Route::post('/skills', [SkillController::class, 'store'])->middleware('management-permission:competency')->name('skills.store');
    Route::post('/skills/target-level', [SkillController::class, 'storeTargetLevel'])->middleware('management-permission:competency')->name('skills.target-level');
    Route::delete('/skills/{id}', [SkillController::class, 'destroy'])->middleware('management-permission:competency')->name('skills.destroy');

    // Trainings & Materials
    Route::get('/trainings', [TrainingController::class, 'index'])->middleware('management-permission:curriculum')->name('trainings.index');
    Route::get('/s2s-products', [S2sProductController::class, 'index'])->middleware('management-permission:curriculum')->name('s2s-products.index');
    Route::get('/s2s-product-mapping-audit', [S2sProductMappingAuditController::class, 'index'])->middleware('management-permission:curriculum')->name('s2s-product-mapping-audit.index');
    Route::post('/s2s-products/sync', [S2sProductController::class, 'sync'])->middleware('management-permission:superadmin')->name('s2s-products.sync');
    Route::get('/s2s-competency-syncs', [S2sCompetencySyncController::class, 'index'])->middleware('management-permission:competency')->name('s2s-competency-syncs.index');
    Route::post('/s2s-competency-syncs/{sync}/retry', [S2sCompetencySyncController::class, 'retry'])->middleware('management-permission:competency')->name('s2s-competency-syncs.retry');
    Route::get('/training-audit', [TrainingAuditController::class, 'index'])->middleware('management-permission:curriculum')->name('training-audit.index');
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
    Route::delete('/questions/{id}', [AdminQuizController::class, 'destroyQuestion'])->middleware('management-permission:curriculum')->name('questions.destroy');

    Route::get('/roles', [RoleController::class, 'index'])->middleware('management-permission:superadmin')->name('roles.index');
    Route::get('/activity-logs', [ActivityLogController::class, 'index'])->middleware('management-permission:superadmin')->name('activity-logs.index');
    Route::get('/roles/{manpower}/edit', [RoleController::class, 'edit'])->middleware('management-permission:superadmin')->name('roles.edit');
    Route::put('/roles/{manpower}', [RoleController::class, 'update'])->middleware('management-permission:superadmin')->name('roles.update');
    Route::get('/haris-sync', [HarisSyncController::class, 'index'])->middleware('management-permission:superadmin')->name('haris-sync.index');
    Route::post('/haris-sync', [HarisSyncController::class, 'store'])->middleware('management-permission:superadmin')->name('haris-sync.store');
    Route::get('/haris-data-quality', [HarisDataQualityController::class, 'index'])->middleware('management-permission:superadmin')->name('haris-data-quality.index');
});

// Operator Portal Routes
Route::middleware(['auth', 'active-operator'])->prefix('operator')->name('operator.')->group(function () {
    Route::get('/dashboard', [OperatorDashboardController::class, 'index'])->name('dashboard');
    Route::get('/profile', [OperatorProfileController::class, 'show'])->name('profile');
    Route::get('/training/{enrollment}/ojt-result', [OperatorProfileController::class, 'ojtResult'])->name('ojt-result');
    Route::get('/training/{enrollment}/ojt-result/pdf', [OjtPdfController::class, 'downloadForOperator'])->name('ojt-result.pdf');
    Route::get('/materials/{trainingId}', [OperatorMaterialController::class, 'show'])->name('materials.show');
    Route::post('/materials/{trainingId}/{materialId}/complete', [OperatorMaterialController::class, 'completeMaterial'])->name('materials.item.complete');
    Route::post('/materials/{trainingId}/complete', [OperatorMaterialController::class, 'complete'])->name('materials.complete');
    Route::get('/quizzes/{quizId}', [OperatorQuizController::class, 'show'])->name('quizzes.show');
    Route::post('/quizzes/{quizId}/submit', [OperatorQuizController::class, 'submit'])->name('quizzes.submit');
});
