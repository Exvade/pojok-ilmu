<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    public function index(Request $request): View
    {
        $category = $request->string('category')->toString();
        $search = trim($request->string('search')->toString());
        $logs = ActivityLog::query()
            ->with('actor')
            ->when($category !== '', fn ($query) => $query->where('category', $category))
            ->when($search !== '', function ($query) use ($search): void {
                $query->whereHas('actor', fn ($actor) => $actor->where('name', 'like', "%{$search}%"));
            })
            ->latest()
            ->paginate(25)
            ->withQueryString();

        return view('admin.activity-logs.index', compact('logs', 'category', 'search'));
    }
}
