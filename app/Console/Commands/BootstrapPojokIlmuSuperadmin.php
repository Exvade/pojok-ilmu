<?php

namespace App\Console\Commands;

use App\Models\MManpower;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class BootstrapPojokIlmuSuperadmin extends Command
{
    protected $signature = 'pojok-ilmu:bootstrap-superadmin
                            {nik=4067.260721.CKR : NIK superadmin dari data HaRis}
                            {--password= : Kata sandi awal superadmin}
                            {--purge-dummy : Hapus akun dan manpower lokal yang bukan berasal dari HaRis}';

    protected $description = 'Menetapkan superadmin dari karyawan HaRis dan membersihkan akun dummy lokal.';

    public function handle(): int
    {
        $nik = (string) $this->argument('nik');
        $manpower = MManpower::query()->where('nik', $nik)->whereNotNull('haris_synced_at')->first();

        if (! $manpower) {
            $this->error('NIK tidak ditemukan pada data HaRis tersinkron. Jalankan haris:sync-manpower terlebih dahulu.');

            return self::FAILURE;
        }

        $user = User::withTrashed()->firstOrNew(['user_id' => $manpower->mp_id]);
        $password = (string) $this->option('password');

        if (blank($user->password) && blank($password)) {
            $this->error('Akun belum memiliki kata sandi. Isi opsi --password untuk menetapkan kata sandi awal.');

            return self::FAILURE;
        }

        if ($user->exists && $user->trashed()) {
            $user->restore();
        }

        $user->name = $manpower->nama;
        $user->email = $manpower->email;
        $user->role = 1;
        $user->is_superadmin = true;
        $user->can_manage_competency = true;
        $user->can_manage_curriculum = true;
        if (filled($password)) {
            $user->password = Hash::make($password);
        }
        $user->save();

        if ($this->option('purge-dummy')) {
            $dummyIds = MManpower::withTrashed()
                ->whereNull('haris_synced_at')
                ->where('mp_id', '!=', $manpower->mp_id)
                ->pluck('mp_id');

            User::withTrashed()->whereIn('user_id', $dummyIds)->forceDelete();
            MManpower::withTrashed()->whereIn('mp_id', $dummyIds)->forceDelete();
            User::withTrashed()->where('user_id', '!=', $manpower->mp_id)->whereDoesntHave('manpower', fn ($query) => $query->whereNotNull('haris_synced_at'))->forceDelete();
        }

        $this->info("Superadmin ditetapkan: {$manpower->nama} ({$manpower->nik}).");

        return self::SUCCESS;
    }
}
