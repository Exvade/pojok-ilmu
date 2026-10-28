<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\S2sCompetencySync;
use App\Services\S2sCompetencySyncService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RuntimeException;

class S2sCompetencySyncController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->string('status')->toString();
        $search = trim($request->string('search')->toString());
        $syncs = S2sCompetencySync::query()
            ->with(['enrollment.manpower', 'enrollment.training', 'product'])
            ->when(in_array($status, ['queued', 'sending', 'succeeded', 'failed', 'disabled'], true), fn ($query) => $query->where('status', $status))
            ->when($search !== '', fn ($query) => $query->where(fn ($sync) => $sync
                ->where('nik', 'like', "%{$search}%")
                ->orWhere('kode_part', 'like', "%{$search}%")
                ->orWhere('judul_training', 'like', "%{$search}%")))
            ->latest()
            ->paginate(25)
            ->withQueryString();

        return view('admin.s2s-competency-syncs.index', compact('syncs', 'status', 'search'));
    }

    public function retry(S2sCompetencySync $sync, S2sCompetencySyncService $service): RedirectResponse
    {
        try {
            $service->retry($sync);

            return back()->with('success', 'Kompetensi dimasukkan kembali ke antrean pengiriman S2S.');
        } catch (RuntimeException $exception) {
            return back()->with('error', $exception->getMessage());
        }
    }
}
