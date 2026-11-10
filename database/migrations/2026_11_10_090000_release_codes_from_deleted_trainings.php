<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('trainings')
            ->whereNotNull('deleted_at')
            ->orderBy('id')
            ->get(['id', 'kode_training'])
            ->each(function (object $training): void {
                $suffix = '--DIHAPUS--' . $training->id;
                $originalCode = strtoupper(trim((string) $training->kode_training));
                $archivedCode = Str::substr($originalCode, 0, 255 - Str::length($suffix)) . $suffix;

                if ($training->kode_training !== $archivedCode) {
                    DB::table('trainings')
                        ->where('id', $training->id)
                        ->update(['kode_training' => $archivedCode]);
                }
            });
    }

    public function down(): void
    {
        // Kode asal tidak dipulihkan agar tidak mengunci training aktif yang dibuat setelah penghapusan.
    }
};
