<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\HarisDataQualityService;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;
use RuntimeException;

class HarisDataQualityController extends Controller
{
    public function index(HarisDataQualityService $harisDataQualityService): View
    {
        try {
            $report = Cache::remember('haris-data-quality-report', now()->addMinutes(10), fn (): array => $harisDataQualityService->inspect());
            $error = null;
        } catch (RuntimeException) {
            $report = ['total' => 0, 'issues' => collect()];
            $error = 'Data kualitas HaRis belum dapat dimuat. Periksa konfigurasi dan koneksi API, lalu coba lagi.';
        }

        $summary = [
            'plant' => $report['issues']->filter(fn (array $issue): bool => in_array('Plant belum lengkap', $issue['problems'], true))->count(),
            'department' => $report['issues']->filter(fn (array $issue): bool => in_array('Departemen belum lengkap', $issue['problems'], true))->count(),
            'jobTitle' => $report['issues']->filter(fn (array $issue): bool => in_array('Jabatan belum diisi', $issue['problems'], true))->count(),
        ];

        return view('admin.haris-data-quality.index', compact('report', 'summary', 'error'));
    }
}
