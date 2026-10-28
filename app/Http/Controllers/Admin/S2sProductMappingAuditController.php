<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Training;
use Illuminate\Http\Request;
use Illuminate\View\View;

class S2sProductMappingAuditController extends Controller
{
    public function index(Request $request): View
    {
        $filter = $request->string('status')->toString();
        $search = trim($request->string('search')->toString());
        $trainings = Training::query()
            ->with('s2sProducts')
            ->when($search !== '', fn ($query) => $query->where(fn ($training) => $training->where('kode_training', 'like', "%{$search}%")->orWhere('judul', 'like', "%{$search}%")))
            ->orderBy('level')
            ->orderBy('judul')
            ->get()
            ->map(function (Training $training): Training {
                $status = $training->s2sProducts->isEmpty()
                    ? 'unmapped'
                    : ($training->s2sProducts->contains('status', 'nonaktif') ? 'inactive' : 'mapped');
                $training->setAttribute('s2s_mapping_status', $status);

                return $training;
            });

        $summary = [
            'total' => $trainings->count(),
            'mapped' => $trainings->where('s2s_mapping_status', 'mapped')->count(),
            'unmapped' => $trainings->where('s2s_mapping_status', 'unmapped')->count(),
            'inactive' => $trainings->where('s2s_mapping_status', 'inactive')->count(),
        ];

        if (in_array($filter, ['mapped', 'unmapped', 'inactive'], true)) {
            $trainings = $trainings->where('s2s_mapping_status', $filter)->values();
        }

        return view('admin.s2s-product-mapping-audit.index', compact('trainings', 'summary', 'filter', 'search'));
    }
}
