<?php

namespace App\Http\Middleware;

use App\Services\PlantContext;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

/**
 * Memastikan pengelola selalu memiliki plant aktif yang sah di sesi.
 */
class EnsureActivePlant
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->isManagement()) {
            $selectable = PlantContext::selectableValues($user);
            $current = $request->session()->get('active_plant');
            $normalized = is_numeric($current) ? (int) $current : $current;

            if (! in_array($normalized, $selectable, true)) {
                $request->session()->put('active_plant', PlantContext::initialValue($user));
            }

            View::share('plantSwitcher', [
                'visible' => PlantContext::isMultiPlant() && count($selectable) > 1,
                'current' => $request->session()->get('active_plant'),
                'options' => collect($selectable)->mapWithKeys(fn (int|string $plant): array => [$plant => PlantContext::label($plant)])->all(),
            ]);
        }

        return $next($request);
    }
}
