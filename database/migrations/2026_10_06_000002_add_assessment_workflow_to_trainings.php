<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('quizzes', 'tipe')) {
            Schema::table('quizzes', function (Blueprint $table) {
                $table->enum('tipe', ['pre_test', 'post_test', 'assessment'])->default('post_test')->after('training_id');
            });
        }
        if (! Schema::hasColumn('quizzes', 'cooldown_hours')) {
            Schema::table('quizzes', function (Blueprint $table) {
                $table->unsignedSmallInteger('cooldown_hours')->default(24)->after('max_retries');
            });
        }

        // Semua assessment yang sudah dibuat sebelum pemisahan alur tetap aman sebagai post-test.
        DB::table('quizzes')->whereNull('tipe')->update(['tipe' => 'post_test']);

        // A previous development schema already uses pre_test_completed_at and next_attempt_at.
        // Reuse those names to keep its existing data intact.
        if (! Schema::hasColumn('training_enrollments', 'pre_test_completed_at')) {
            Schema::table('training_enrollments', function (Blueprint $table) {
                $table->timestamp('pre_test_completed_at')->nullable()->after('current_retry_count');
            });
        }
        if (! Schema::hasColumn('training_enrollments', 'material_completed_at')) {
            Schema::table('training_enrollments', function (Blueprint $table) {
                $table->timestamp('material_completed_at')->nullable()->after('pre_test_completed_at');
            });
        }
        if (! Schema::hasColumn('training_enrollments', 'next_attempt_at')) {
            Schema::table('training_enrollments', function (Blueprint $table) {
                $table->timestamp('next_attempt_at')->nullable()->after('material_completed_at');
            });
        }
        if (! Schema::hasColumn('training_enrollments', 'active_quiz_id')) {
            Schema::table('training_enrollments', function (Blueprint $table) {
                $table->foreignUuid('active_quiz_id')->nullable()->after('next_attempt_at')->constrained('quizzes')->nullOnDelete();
            });
        }
        if (! Schema::hasColumn('training_enrollments', 'assessment_started_at')) {
            Schema::table('training_enrollments', function (Blueprint $table) {
                $table->timestamp('assessment_started_at')->nullable()->after('active_quiz_id');
            });
        }
        if (! Schema::hasColumn('training_enrollments', 'assessment_expires_at')) {
            Schema::table('training_enrollments', function (Blueprint $table) {
                $table->timestamp('assessment_expires_at')->nullable()->after('assessment_started_at');
            });
        }

        // This temporary spelling could only have been created by an interrupted dev migration.
        // Preserve any value before removing it, so a rerun is safe.
        if (Schema::hasColumn('training_enrollments', 'pretest_completed_at')) {
            DB::table('training_enrollments')->whereNull('pre_test_completed_at')
                ->whereNotNull('pretest_completed_at')
                ->update(['pre_test_completed_at' => DB::raw('pretest_completed_at')]);
            Schema::table('training_enrollments', function (Blueprint $table) {
                $table->dropColumn('pretest_completed_at');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('training_enrollments', 'active_quiz_id')) {
            Schema::table('training_enrollments', function (Blueprint $table) {
                $table->dropForeign(['active_quiz_id']);
                $table->dropColumn(['active_quiz_id', 'assessment_started_at', 'assessment_expires_at', 'material_completed_at']);
            });
        }

        Schema::table('quizzes', function (Blueprint $table) {
            $table->dropColumn(['tipe', 'cooldown_hours']);
        });
    }
};
