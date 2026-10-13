<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MManpower;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class RoleController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->query('search'));
        $manpowers = MManpower::query()
            ->where('status', true)
            ->whereNotNull('haris_synced_at')
            ->with('user')
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('nama', 'like', "%{$search}%")
                        ->orWhere('nik', 'like', "%{$search}%")
                        ->orWhere('departemen', 'like', "%{$search}%");
                });
            })
            ->orderBy('nama')
            ->paginate(20)
            ->withQueryString();

        return view('admin.roles.index', compact('manpowers', 'search'));
    }

    public function edit(MManpower $manpower)
    {
        $manpower->load('user');

        abort_if($manpower->user?->is_superadmin, 403, 'Superadmin tidak dapat diubah melalui halaman ini.');

        return view('admin.roles.edit', compact('manpower'));
    }

    public function update(Request $request, MManpower $manpower)
    {
        $manpower->load('user');
        abort_if($manpower->user?->is_superadmin, 403, 'Superadmin tidak dapat diubah melalui halaman ini.');

        $data = $request->validate([
            'can_manage_competency' => 'nullable|boolean',
            'can_manage_curriculum' => 'nullable|boolean',
            'can_assess_ojt' => 'nullable|boolean',
            'password' => 'nullable|string|min:8|max:255',
        ]);

        $competency = $request->boolean('can_manage_competency');
        $curriculum = $request->boolean('can_manage_curriculum');
        $ojtAssessor = $request->boolean('can_assess_ojt');
        $hasAccess = $competency || $curriculum || $ojtAssessor;
        $user = User::withTrashed()->firstOrNew(['user_id' => $manpower->mp_id]);

        if ($hasAccess && blank($user->password) && blank($data['password'] ?? null)) {
            return back()->withErrors(['password' => 'Kata sandi awal wajib diisi saat pertama kali memberikan akses pengelola.'])->withInput();
        }

        if ($user->exists && $user->trashed()) {
            $user->restore();
        }

        $user->name = $manpower->nama;
        $user->email = $manpower->email;
        $user->role = $hasAccess ? 2 : 0;
        $user->is_superadmin = false;
        $user->can_manage_competency = $competency;
        $user->can_manage_curriculum = $curriculum;
        $user->can_assess_ojt = $ojtAssessor;

        if (filled($data['password'] ?? null)) {
            $user->password = Hash::make($data['password']);
        }

        $user->save();

        return redirect()->route('admin.roles.index')->with('success', $hasAccess
            ? 'Akses pengelola berhasil disimpan.'
            : 'Akses pengelola telah dicabut; akun kembali sebagai karyawan.');
    }
}
