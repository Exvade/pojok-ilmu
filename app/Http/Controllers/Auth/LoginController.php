<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\MManpower;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    /**
     * Tampilkan Halaman Login Manual
     */
    public function showLoginForm()
    {
        if (Auth::check()) {
            return redirect($this->redirectTo());
        }

        $manpowers = MManpower::where('status', true)->select('mp_id', 'nama', 'nik')->get();

        return view('auth.login', compact('manpowers'));
    }

    /**
     * Handle Login Manual (Admin/Management dengan Password, Operator dengan NPK)
     */
    public function login(Request $request)
    {
        $request->validate([
            'user_id' => 'required|string',
            'password' => 'nullable|string',
        ]);

        $userId = trim($request->input('user_id'));
        $password = $request->input('password');

        // 1. HARDCODED SUPERADMIN BYPASS (superadmin / 2025)
        if ($userId === 'superadmin' && $password === '2025') {
            $user = User::where('is_superadmin', true)->first();

            if (! $user) {
                $user = User::create([
                    'user_id' => 'SUPERADMIN',
                    'password' => Hash::make('2025'),
                    'role' => 1,
                    'is_superadmin' => true,
                ]);
            }

            Auth::login($user);

            session([
                'user_id' => 'SUPERADMIN',
                'role' => 1,
                'is_superadmin' => true,
                'user_name' => 'SUPER ADMIN',
                'departemen' => 'MANAGEMENT',
            ]);

            return redirect('/admin/dashboard')->with('success', 'Selamat datang Superadmin!');
        }

        // 2. Cari User Terdaftar di Database S2S (berdasarkan user_id / NPK)
        $user = User::where('user_id', $userId)->first();

        // 3. Jika Akun Admin / Management (Role 1 atau 2) -> Wajib Password
        if ($user && ($user->isAdmin() || $user->isManagement())) {
            if (! $password) {
                return back()->with('error', 'Password wajib diisi untuk akun Admin/Management.')->withInput();
            }

            if (! Hash::check($password, $user->password)) {
                return back()->with('error', 'Password yang Anda masukkan salah.')->withInput();
            }

            Auth::login($user);
            $manpower = $user->manpower;

            session([
                'user_id' => $user->user_id,
                'role' => (int) $user->role,
                'user_name' => $user->name,
                'departemen' => $manpower ? $manpower->departemen : 'MANAGEMENT',
            ]);

            return redirect('/admin/dashboard')->with('success', 'Selamat datang, ' . $user->name . '!');
        }

        // 4. Regular Operator Login (berdasarkan NPK / MP_ID di Master Manpower S2S)
        $manpower = MManpower::where('mp_id', $userId)
            ->orWhere('nik', $userId)
            ->first();

        if (! $manpower) {
            return back()->with('error', 'NPK / ID Manpower (' . $userId . ') tidak terdaftar di sistem.')->withInput();
        }

        if (! $manpower->status) {
            return back()->with('error', 'Akun Manpower Anda non-aktif. Silakan hubungi HR.')->withInput();
        }

        // Cari atau buatkan akun User untuk Operator ini di S2S database
        $user = User::firstOrCreate(
            ['user_id' => $manpower->mp_id],
            [
                'password' => null,
                'role' => 0, // Operator
                'is_superadmin' => false,
            ]
        );

        Auth::login($user);

        session([
            'user_id' => $user->user_id,
            'role' => 0,
            'user_name' => $manpower->nama,
            'departemen' => $manpower->departemen,
        ]);

        return redirect('/operator/dashboard')->with('success', 'Selamat datang, ' . $manpower->nama . '!');
    }

    /**
     * Tampilkan Halaman Barcode/QR Scanner Login
     */
    public function showScanner()
    {
        return view('auth.scan-login');
    }

    /**
     * Handle Login via Barcode / QR Scan (AJAX)
     */
    public function loginViaScan(Request $request): JsonResponse
    {
        $request->validate([
            'token' => 'required|string',
        ]);

        $scannedCode = trim($request->input('token'));

        // Cari Manpower berdasarkan mp_id / NPK / QR Code
        $manpower = MManpower::where('mp_id', $scannedCode)
            ->orWhere('nik', $scannedCode)
            ->orWhere('qrcode', $scannedCode)
            ->first();

        if (! $manpower) {
            return response()->json([
                'success' => false,
                'message' => 'NPK / Barcode Operator (' . $scannedCode . ') tidak ditemukan di database.',
            ], 442);
        }

        if (! $manpower->status) {
            return response()->json([
                'success' => false,
                'message' => 'Akun Operator dinonaktifkan. Hubungi HR.',
            ], 403);
        }

        // Buat atau cari user account untuk operator ini
        $user = User::firstOrCreate(
            ['user_id' => $manpower->mp_id],
            [
                'password' => null,
                'role' => 0,
                'is_superadmin' => false,
            ]
        );

        Auth::login($user);

        session([
            'user_id' => $user->user_id,
            'role' => 0,
            'user_name' => $manpower->nama,
            'departemen' => $manpower->departemen,
        ]);

        $redirectUrl = $user->isManagement() ? url('/admin/dashboard') : url('/operator/dashboard');

        return response()->json([
            'success' => true,
            'message' => 'Scan Berhasil! Selamat datang ' . $manpower->nama,
            'redirect' => $redirectUrl,
        ]);
    }

    /**
     * Handle Logout
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login')->with('success', 'Berhasil keluar dari akun.');
    }

    protected function redirectTo(): string
    {
        if (Auth::user()?->isManagement()) {
            return '/admin/dashboard';
        }

        return '/operator/dashboard';
    }
}
