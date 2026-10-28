<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\S2sProduct;
use App\Services\S2sProductSyncService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RuntimeException;

class S2sProductController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim($request->string('search')->toString());
        $status = $request->string('status')->toString();

        $products = S2sProduct::query()
            ->withCount('trainings')
            ->when($search !== '', fn ($query) => $query->where(fn ($product) => $product
                ->where('kode_part', 'like', "%{$search}%")
                ->orWhere('nama_part', 'like', "%{$search}%")))
            ->when(in_array($status, ['aktif', 'nonaktif'], true), fn ($query) => $query->where('status', $status))
            ->orderBy('status')
            ->orderBy('kode_part')
            ->paginate(25)
            ->withQueryString();

        $lastSyncedAt = S2sProduct::query()->max('synced_at');

        return view('admin.s2s-products.index', compact('products', 'search', 'status', 'lastSyncedAt'));
    }

    public function sync(S2sProductSyncService $syncService): RedirectResponse
    {
        try {
            return to_route('admin.s2s-products.index')->with('success', $syncService->sync()['message']);
        } catch (RuntimeException $exception) {
            return to_route('admin.s2s-products.index')->with('error', $exception->getMessage());
        }
    }
}
