<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('trainings', 'requires_ojt')) {
            Schema::table('trainings', function (Blueprint $table) {
                $table->boolean('requires_ojt')->default(false)->after('passing_grade');
            });
        }
        if (! Schema::hasColumn('training_enrollments', 'post_test_completed_at')) {
            Schema::table('training_enrollments', function (Blueprint $table) {
                $table->timestamp('post_test_completed_at')->nullable()->after('pre_test_completed_at');
            });
        }
        if (! Schema::hasColumn('training_enrollments', 'ojt_status')) {
            Schema::table('training_enrollments', function (Blueprint $table) {
                $table->string('ojt_status')->default('not_started')->after('post_test_completed_at');
                $table->text('ojt_note')->nullable()->after('ojt_status');
                $table->foreignUuid('ojt_assessed_by_user_id')->nullable()->after('ojt_note')->constrained('users')->nullOnDelete();
                $table->timestamp('ojt_assessed_at')->nullable()->after('ojt_assessed_by_user_id');
            });
        }
    }

    public function down(): void
    {
        Schema::table('training_enrollments', function (Blueprint $table) {
            $table->dropForeign(['ojt_assessed_by_user_id']);
            $table->dropColumn(['post_test_completed_at', 'ojt_status', 'ojt_note', 'ojt_assessed_by_user_id', 'ojt_assessed_at']);
        });
        Schema::table('trainings', function (Blueprint $table) {
            $table->dropColumn('requires_ojt');
        });
    }
};
