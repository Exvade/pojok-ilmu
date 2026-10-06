<?php

namespace App\Console\Commands;

use App\Services\HarisEmployeeSyncService;
use Illuminate\Console\Command;
use Throwable;

class SyncHarisManpower extends Command
{
    protected $signature = 'haris:sync-manpower';

    protected $description = 'Sinkronisasi master karyawan dari HaRis';

    public function handle(HarisEmployeeSyncService $harisEmployeeSyncService): int
    {
        try {
            $result = $harisEmployeeSyncService->sync();
            $this->info($result['message']);

            return self::SUCCESS;
        } catch (Throwable $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }
    }
}
