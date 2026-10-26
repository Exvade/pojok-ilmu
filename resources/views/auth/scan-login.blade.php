@extends('layouts.app')
@section('title', 'Pindai kartu · Pojok Ilmu')
@section('content')
<x-auth-shell title="Masuk dengan kartu karyawan" description="Pindai kode QR dengan kamera, gunakan alat pemindai, atau masukkan NIK.">
    <div x-data="scanLogin(@js(route('login.scan.post')))">
        <div class="mb-6 flex items-center gap-4 rounded-lg border border-green-200 bg-green-50 p-4 text-brand-dark dark:border-green-900 dark:bg-green-950 dark:text-green-200">
            <x-icon name="scan" class="h-9 w-9" /><div><p class="text-sm font-semibold">Pindai kartu karyawan</p><p class="mt-1 text-xs leading-5">Gunakan kamera untuk QR atau klik kolom NIK sebelum memakai alat pemindai.</p></div>
        </div>
        <div class="mb-6 space-y-3">
            <x-button variant="secondary" class="w-full" x-show="! cameraActive && ! cameraStarting" @click="startCamera()" ::disabled="loading"><x-icon name="scan" class="h-4 w-4" />Pindai QR dengan kamera</x-button>
            <div x-show="cameraActive" x-cloak class="overflow-hidden rounded-lg border border-gray-200 bg-gray-950 dark:border-gray-700">
                <video x-ref="cameraVideo" autoplay muted playsinline class="aspect-video w-full object-cover" :class="{ '-scale-x-100': cameraMirrored }" aria-label="Tampilan kamera untuk membaca kode QR"></video>
                <p class="px-4 py-3 text-center text-xs text-gray-100">Arahkan kode QR ke kamera. Kamera berhenti setelah kode terbaca.</p>
            </div>
            <x-button variant="outline" class="w-full" x-show="cameraActive || cameraStarting" x-cloak @click="stopCamera()"><x-icon name="close" class="h-4 w-4" /><span x-text="cameraStarting ? 'Batalkan permintaan kamera' : 'Tutup kamera'"></span></x-button>
            <p x-show="cameraStarting" x-cloak class="text-xs text-gray-500" role="status">Menyiapkan kamera. Izinkan akses jika diminta browser.</p>
        </div>
        <form @submit.prevent="submitScan()" class="space-y-5" :aria-busy="loading">
            <div><label for="barcode_input" class="mb-2 block text-sm font-medium">Barcode / NIK karyawan</label><x-input name="token" id="barcode_input" x-ref="barcodeInput" x-model="token" type="text" required autocomplete="off" placeholder="Pindai atau masukkan NIK" ::disabled="loading" /></div>
            <p x-show="error" x-cloak x-text="error" role="alert" class="rounded-md border border-red-200 bg-red-50 p-3 text-sm text-red-700 dark:border-red-800 dark:bg-red-950 dark:text-red-300"></p>
            <x-button type="submit" class="w-full" ::disabled="loading"><span x-text="loading ? 'Memproses…' : 'Masuk dengan NIK'"></span><x-icon name="arrow" class="h-4 w-4" /></x-button>
        </form>
    </div>
</x-auth-shell>
@endsection
