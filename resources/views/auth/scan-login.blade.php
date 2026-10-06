@extends('layouts.app')
@section('title', 'Pindai kartu · Pojok Ilmu')
@section('content')
<x-auth-shell title="Masuk dengan kartu karyawan" description="Gunakan alat pemindai barcode atau ketik NIK, lalu tekan Enter.">
    <div x-data="scanLogin(@js(route('login.scan.post')))">
        <div class="mb-6 flex items-center gap-4 rounded-lg border border-green-200 bg-green-50 p-4 text-brand-dark dark:border-green-900 dark:bg-green-950 dark:text-green-200">
            <x-icon name="scan" class="h-9 w-9" /><div><p class="text-sm font-semibold">Siap menerima hasil pindai</p><p class="mt-1 text-xs leading-5">Klik kolom NIK sebelum memindai kartu.</p></div>
        </div>
        <form @submit.prevent="submitScan()" class="space-y-5" :aria-busy="loading">
            <div><label for="barcode_input" class="mb-2 block text-sm font-medium">Barcode / NIK karyawan</label><x-input name="token" id="barcode_input" x-ref="barcodeInput" x-model="token" type="text" required autocomplete="off" placeholder="Pindai atau masukkan NIK" ::disabled="loading" /></div>
            <p x-show="error" x-cloak x-text="error" role="alert" class="rounded-md border border-red-200 bg-red-50 p-3 text-sm text-red-700 dark:border-red-800 dark:bg-red-950 dark:text-red-300"></p>
            <x-button type="submit" class="w-full" ::disabled="loading"><span x-text="loading ? 'Memproses…' : 'Masuk dengan NIK'"></span><x-icon name="arrow" class="h-4 w-4" /></x-button>
        </form>
    </div>
</x-auth-shell>
@endsection
