<?php

namespace App\Console\Commands;

use App\Models\MManpower;
use App\Services\TrainingNeedAnalysisService;
use Illuminate\Console\Command;

class GenerateTrainingNeedAnalysis extends Command
{
    protected $signature = 'pojok-ilmu:generate-tna {--nik= : Batasi ke satu NIK karyawan}';

    protected $description = 'Bangun atau perbarui Skill Matrix dan penugasan training berdasarkan target kompetensi';

    public function handle(TrainingNeedAnalysisService $tna): int
    {
        $query = MManpower::query()->where('status', true)->whereNotNull('haris_synced_at');

        if (filled($nik = $this->option('nik'))) {
            $query->where('nik', $nik);
        }

        $processed = 0;
        $assigned = 0;

        $query->orderBy('id')->chunkById(100, function ($manpowers) use ($tna, &$processed, &$assigned): void {
            foreach ($manpowers as $manpower) {
                $assigned += $tna->synchronizeFor($manpower);
                $processed++;
            }
        });

        $this->info("TNA selesai: {$processed} karyawan diproses, {$assigned} penugasan baru dibuat.");

        return self::SUCCESS;
    }
}
