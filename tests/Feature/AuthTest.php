<?php

use App\Models\MManpower;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

test('guest is redirected to login page when accessing root', function () {
    $response = $this->get('/');
    $response->assertRedirect('/login');
});

test('admin can login with correct password', function () {
    $adminMp = MManpower::create([
        'mp_id' => 'ADMIN01',
        'nik' => '990001',
        'nama' => 'Admin Test',
        'departemen' => 'IT',
        'bagian' => 'System',
        'status' => true,
    ]);

    $user = User::create([
        'user_id' => 'ADMIN01',
        'password' => Hash::make('password123'),
        'role' => 1,
        'is_superadmin' => true,
    ]);

    $response = $this->post('/login', [
        'user_id' => 'ADMIN01',
        'password' => 'password123',
    ]);

    $response->assertRedirect('/admin/dashboard');
    $this->assertAuthenticatedAs($user);
});

test('operator can login via barcode scan without password', function () {
    $operator = MManpower::create([
        'mp_id' => 'OP999',
        'nik' => '100999',
        'nama' => 'Budi Scan Test',
        'departemen' => 'PRODUKSI PLASTIK',
        'bagian' => 'ASSEMBLY',
        'status' => true,
    ]);

    $response = $this->postJson('/login/scan', [
        'token' => 'OP999',
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
        ]);

    $this->assertDatabaseHas('users', [
        'user_id' => 'OP999',
        'role' => 0,
    ]);
});
