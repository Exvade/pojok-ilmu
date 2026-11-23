<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Menyiapkan fondasi multi-plant: akses plant pengelola dan plant pemilik training.
     * Seluruh data lama dimasukkan ke plant bawaan agar perilaku saat ini tidak berubah.
     */
    public function up(): void
    {
        $defaultPlant = (int) config('app.default_plant', 3);

        Schema::create('user_plants', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedTinyInteger('plant')->index();
            $table->mwtTimestamps();
            $table->unique(['user_id', 'plant']);
        });

        Schema::table('trainings', function (Blueprint $table): void {
            $table->unsignedTinyInteger('plant')->nullable()->after('level')->index();
        });

        DB::table('trainings')->update(['plant' => $defaultPlant]);

        Schema::table('trainings', function (Blueprint $table): void {
            $table->unsignedTinyInteger('plant')->nullable(false)->change();
        });

        $now = now();

        DB::table('users')
            ->whereNull('deleted_at')
            ->where('is_superadmin', false)
            ->where(function ($query): void {
                $query->where('can_manage_competency', true)
                    ->orWhere('can_manage_curriculum', true)
                    ->orWhere('can_assess_ojt', true);
            })
            ->orderBy('id')
            ->pluck('id')
            ->each(function (string $userId) use ($defaultPlant, $now): void {
                DB::table('user_plants')->insert([
                    'id' => (string) Str::uuid(),
                    'user_id' => $userId,
                    'plant' => $defaultPlant,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            });
    }

    public function down(): void
    {
        Schema::table('trainings', function (Blueprint $table): void {
            $table->dropIndex(['plant']);
            $table->dropColumn('plant');
        });

        Schema::dropIfExists('user_plants');
    }
};
