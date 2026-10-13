<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'can_assess_ojt')) {
            Schema::table('users', function (Blueprint $table) {
                $table->boolean('can_assess_ojt')->default(false)->after('can_manage_curriculum');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('users', 'can_assess_ojt')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('can_assess_ojt');
            });
        }
    }
};
