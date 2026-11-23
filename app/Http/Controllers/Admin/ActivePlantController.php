<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\PlantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ActivePlantController extends Controller
{
    /**
     * Mengganti plant aktif pengelola (pola yang sama dengan asakai).
     */
    public function update(Request $request): RedirectResponse
    {
        $selectable = array_map('strval', PlantContext::selectableValues($request->user()));

        $data = $request->validate([
            'plant' => ['required', 'string', Rule::in($selectable)],
        ], [
            'plant.in' => 'Anda tidak memiliki akses ke plant tersebut.',
        ]);

        $plant = $data['plant'] === PlantContext::ALL ? PlantContext::ALL : (int) $data['plant'];
        $request->session()->put('active_plant', $plant);

        return back()->with('success', 'Plant aktif diganti ke ' . PlantContext::label($plant) . '.');
    }
}
