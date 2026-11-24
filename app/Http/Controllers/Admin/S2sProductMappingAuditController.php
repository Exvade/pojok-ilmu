<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\S2sProduct;
use App\Models\Training;
use App\Services\S2sProductSuggestionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class S2sProductMappingAuditController extends Controller
{
    public function index(Request $request, S2sProductSuggestionService $suggestions): View
    {
        $filter = $request->string('status')->toString();
        $search = trim($request->string('search')->toString());

        $trainings = Training::query()
            ->with('s2sProducts')
            ->when($search !== '', fn ($query) => $query->where(fn ($training) => $training->where('kode_training', 'like', "%{$search}%")->orWhere('judul', 'like', "%{$search}%")))
            ->orderBy('kode_training')
            ->get()
            ->map(function (Training $training): Training {
                $status = match (true) {
                    $training->is_general => 'general',
                    $training->s2sProducts->isEmpty() => 'unmapped',
                    $training->s2sProducts->contains('status', 'nonaktif') => 'inactive',
                    default => 'mapped',
                };
                $training->setAttribute('s2s_mapping_status', $status);

                return $training;
            });

        $summary = [
            'total' => $trainings->count(),
            'mapped' => $trainings->where('s2s_mapping_status', 'mapped')->count(),
            'unmapped' => $trainings->where('s2s_mapping_status', 'unmapped')->count(),
            'inactive' => $trainings->where('s2s_mapping_status', 'inactive')->count(),
            'general' => $trainings->where('s2s_mapping_status', 'general')->count(),
        ];

        if (in_array($filter, ['mapped', 'unmapped', 'inactive', 'general'], true)) {
            $trainings = $trainings->where('s2s_mapping_status', $filter)->values();
        }

        $activeProducts = $trainings->contains('s2s_mapping_status', 'unmapped')
            ? S2sProduct::query()->where('status', 'aktif')->get(['id', 'kode_part', 'nama_part'])
            : collect();

        $trainings->each(fn (Training $training) => $training->setAttribute(
            'suggested_products',
            $training->s2s_mapping_status === 'unmapped' ? $suggestions->suggest($training, $activeProducts) : collect(),
        ));

        return view('admin.s2s-product-mapping-audit.index', compact('trainings', 'summary', 'filter', 'search'));
    }

    /**
     * Hubungkan produk S2S yang dipilih dari saran langsung ke training.
     */
    public function attach(Request $request, Training $training): RedirectResponse
    {
        $data = $request->validate([
            'product_ids' => ['required', 'array', 'min:1'],
            'product_ids.*' => ['uuid', 'exists:s2s_products,id'],
        ], [
            'product_ids.required' => 'Pilih minimal satu produk S2S.',
        ]);

        $training->s2sProducts()->syncWithoutDetaching($data['product_ids']);
        $training->update(['is_general' => false]);

        return back()->with('success', "Produk S2S berhasil dihubungkan ke training {$training->judul}.");
    }

    /**
     * Tandai atau batalkan penanda training umum (tanpa produk S2S).
     */
    public function general(Request $request, Training $training): RedirectResponse
    {
        $isGeneral = $request->boolean('is_general');

        if ($isGeneral) {
            $training->s2sProducts()->detach();
        }

        $training->update(['is_general' => $isGeneral]);

        return back()->with('success', $isGeneral
            ? "Training {$training->judul} ditandai sebagai training umum."
            : "Penanda training umum pada {$training->judul} dibatalkan.");
    }
}
