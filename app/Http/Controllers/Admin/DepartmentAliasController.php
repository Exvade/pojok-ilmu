<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DepartmentAlias;
use App\Models\MManpower;
use App\Services\DepartmentAliasResolver;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DepartmentAliasController extends Controller
{
    public function __construct(private readonly DepartmentAliasResolver $resolver) {}

    public function index(): View
    {
        $departments = MManpower::query()
            ->where('status', true)
            ->whereNotNull('haris_synced_at')
            ->whereNotNull('departemen')
            ->distinct()
            ->orderBy('departemen')
            ->pluck('departemen');
        $aliases = DepartmentAlias::query()->orderBy('departemen')->orderBy('alias')->get();

        return view('admin.department-aliases.index', compact('departments', 'aliases'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'departemen' => ['required', 'string', 'exists:m_manpower,departemen'],
            'alias' => ['required', 'string', 'max:255'],
        ]);
        $aliasKey = $this->resolver->key($data['alias']);

        if ($aliasKey === '') {
            return back()->withErrors(['alias' => 'Alias departemen wajib diisi.'])->withInput();
        }

        if ($this->resolver->resolve($data['alias']) !== null) {
            return back()->withErrors(['alias' => 'Alias sudah digunakan sebagai nama departemen atau alias lain.'])->withInput();
        }

        DepartmentAlias::create([
            'departemen' => $data['departemen'],
            'alias' => trim($data['alias']),
            'alias_key' => $aliasKey,
        ]);

        return back()->with('success', 'Alias departemen berhasil ditambahkan.');
    }

    public function destroy(DepartmentAlias $departmentAlias): RedirectResponse
    {
        $departmentAlias->delete();

        return back()->with('success', 'Alias departemen berhasil dihapus.');
    }
}
