@extends('layouts.app')
@section('title', 'Masuk · Pojok Ilmu')
@section('content')
<x-auth-shell title="Selamat datang kembali" description="Masuk untuk melanjutkan pembelajaran atau mengelola pelatihan karyawan.">
    <form action="{{ route('login.post') }}" method="POST" class="space-y-5">
        @csrf
        <div>
            <label for="user_id" class="mb-2 block text-sm font-medium">NIK karyawan / ID akun</label>
            <x-input type="text" name="user_id" placeholder="Masukkan NIK sesuai data HaRis" autocomplete="username" required autofocus aria-describedby="login-id-help" />
            <p id="login-id-help" class="mt-2 text-xs text-gray-500">Karyawan menggunakan NIK. Pengelola menggunakan ID akun terdaftar.</p>
        </div>
        <div>
            <label for="password" class="mb-2 block text-sm font-medium">Kata sandi <span class="font-normal text-gray-500">(khusus pengelola)</span></label>
            <x-password placeholder="Masukkan kata sandi pengelola" aria-describedby="password-help" />
            <p id="password-help" class="mt-2 text-xs text-gray-500">Kosongkan kolom ini untuk masuk sebagai karyawan.</p>
        </div>
        <x-button type="submit" class="w-full">Masuk ke Pojok Ilmu <x-icon name="arrow" class="h-4 w-4" /></x-button>
    </form>
</x-auth-shell>
@endsection
