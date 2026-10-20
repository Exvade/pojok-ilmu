<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HarisSyncLog;
use App\Services\HarisEmployeeSyncService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use RuntimeException;

class HarisSyncController extends Controller
{
    public function index(): View
    {
        $logs = HarisSyncLog::query()->latest('started_at')->limit(10)->get();

        return view('admin.haris-sync.index', compact('logs'));
    }

    public function store(HarisEmployeeSyncService $harisEmployeeSyncService): RedirectResponse
    {
        try {
            $result = $harisEmployeeSyncService->sync();

            return to_route('admin.haris-sync.index')->with('success', $result['message']);
        } catch (RuntimeException $exception) {
            return to_route('admin.haris-sync.index')->with('error', $exception->getMessage());
        }
    }
}
