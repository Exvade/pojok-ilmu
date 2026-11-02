<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quizzes', function (Blueprint $table): void {
            $table->unsignedSmallInteger('question_count')->default(5)->after('durasi_menit');
        });
        Schema::table('training_enrollments', function (Blueprint $table): void {
            $table->json('active_question_ids')->nullable()->after('active_quiz_id');
        });
    }

    public function down(): void
    {
        Schema::table('training_enrollments', function (Blueprint $table): void {
            $table->dropColumn('active_question_ids');
        });
        Schema::table('quizzes', function (Blueprint $table): void {
            $table->dropColumn('question_count');
        });
    }
};
