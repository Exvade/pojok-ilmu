<?php

namespace App\Jobs;

use App\Models\S2sCompetencySync;
use App\Services\S2sCompetencySyncService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class SendS2sCompetency implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    /** @var array<int, int> */
    public array $backoff = [60, 300, 900];

    public function __construct(public readonly string $syncId) {}

    public function handle(S2sCompetencySyncService $service): void
    {
        $service->send($this->syncId);
    }

    public function failed(Throwable $exception): void
    {
        S2sCompetencySync::query()->whereKey($this->syncId)->update([
            'status' => 'failed',
            'last_error' => str($exception->getMessage())->limit(1000),
            'updated_at' => now(),
        ]);
    }
}
