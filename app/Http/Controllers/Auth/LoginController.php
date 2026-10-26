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
     * Handle Login Manual (Admin/Management dengan Password, Operator dengan NIK)
     */
    public function login(Request $request)
    {
        $request->validate([
            'user_id' => 'required|string',
            'password' => 'nullable|string',
        ]);

        $userId = trim($request->input('user_id'));
        $password = $request->input('password');

        // 1. Cari akun yang sudah terdaftar berdasarkan user_id atau NIK
        $user = User::where('user_id', $userId)->first();

        // 2. Jika Akun Admin / Management (Role 1 atau 2) -> Wajib Password
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

        // 3. Login operator berdasarkan NIK pada master karyawan HaRis lokal.
        $manpower = MManpower::where('nik', $userId)
            ->orWhere('mp_id', $userId)
            ->first();

        if (! $manpower) {
            return back()->with('error', 'NIK (' . $userId . ') tidak terdaftar di sistem.')->withInput();
        }

        if (! $manpower->status) {
            return back()->with('error', 'Akun Manpower Anda non-aktif. Silakan hubungi HR.')->withInput();
        }

        // Cari atau buatkan akun lokal untuk operator ini
        $user = User::firstOrCreate(
            ['user_id' => $manpower->mp_id],
            [
                'name' => $manpower->nama,
                'password' => null,
                'role' => 0, // Operator
                'is_superadmin' => false,
            ]
        );

        if ((int) $user->role !== 0 || $user->isManagement()) {
            return back()->with('error', 'Akun pengelola harus masuk menggunakan ID akun dan kata sandi.')->withInput();
        }

        Auth::login($user);

        session([
            'user_id' => $user->user_id,
            'role' => 0,
            'user_name' => $manpower->nama,
            'departemen' => $manpower->departemen,
        ]);

        return redirect('/operator/dashboard')
            ->with('success', 'Selamat datang, ' . $manpower->nama . '!')
            ->with('play_welcome_speech', true);
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

        // Cari manpower berdasarkan NIK, ID internal lama, atau kode QR.
        $manpower = MManpower::where('nik', $scannedCode)
            ->orWhere('mp_id', $scannedCode)
            ->orWhere('qrcode', $scannedCode)
            ->first();

        if (! $manpower) {
            return response()->json([
                'success' => false,
                'message' => 'NIK / Barcode operator (' . $scannedCode . ') tidak ditemukan di database.',
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
                'name' => $manpower->nama,
                'password' => null,
                'role' => 0,
                'is_superadmin' => false,
            ]
        );

        if ((int) $user->role !== 0 || $user->isManagement()) {
            return response()->json([
                'success' => false,
                'message' => 'Akun pengelola harus masuk menggunakan NIK atau ID akun dan kata sandi.',
            ], 403);
        }

        Auth::login($user);

        session([
            'user_id' => $user->user_id,
            'role' => 0,
            'user_name' => $manpower->nama,
            'departemen' => $manpower->departemen,
        ]);
        session()->flash('play_welcome_speech', true);

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
