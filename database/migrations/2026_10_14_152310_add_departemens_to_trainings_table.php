<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('trainings', function (Blueprint $table): void {
            $table->json('departemens')->nullable()->after('departemen');
        });

        DB::table('trainings')
            ->whereNotNull('departemen')
            ->orderBy('id')
            ->each(function (object $training): void {
                DB::table('trainings')
                    ->where('id', $training->id)
                    ->update(['departemens' => json_encode([$training->departemen])]);
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('trainings', function (Blueprint $table): void {
            $table->dropColumn('departemens');
        });
    }
};
