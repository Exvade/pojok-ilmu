<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->adjustLegacyTimestamps(7);
    }

    public function down(): void
    {
        $this->adjustLegacyTimestamps(-7);
    }

    private function adjustLegacyTimestamps(int $hours): void
    {
        $timestampColumns = [
            'training_enrollments' => [
                'pre_test_completed_at',
                'material_completed_at',
                'next_attempt_at',
                'assessment_started_at',
                'assessment_expires_at',
                'post_test_completed_at',
                'assessment_completed_at',
                'ojt_assessed_at',
            ],
            'quiz_attempts' => ['submitted_at'],
            'training_material_progresses' => ['completed_at'],
        ];

        foreach ($timestampColumns as $table => $columns) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            foreach ($columns as $column) {
                if (! Schema::hasColumn($table, $column)) {
                    continue;
                }

                DB::table($table)
                    ->whereNotNull($column)
                    ->update([$column => DB::raw($this->timestampExpression($column, $hours))]);
            }
        }
    }

    private function timestampExpression(string $column, int $hours): string
    {
        $modifier = $hours > 0 ? '+' : '-';

        return match (DB::getDriverName()) {
            'sqlite' => "datetime(\"{$column}\", '{$modifier} " . abs($hours) . " hours')",
            'pgsql' => "\"{$column}\" {$modifier} INTERVAL '" . abs($hours) . " hours'",
            default => "DATE_ADD(`{$column}`, INTERVAL {$hours} HOUR)",
        };
    }
};
