<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoAccountSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Akun Demo Admin
        User::updateOrCreate(
            ['email' => 'admin@kampuslms.test'],
            [
                'name' => 'Administrator Demo',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'nim_nip' => null,
                'email_verified_at' => now(),
            ]
        );

        // 2. Akun Demo Dosen
        User::updateOrCreate(
            ['email' => 'dosen@kampuslms.test'],
            [
                'name' => 'Dosen Demo',
                'password' => Hash::make('password'),
                'role' => 'dosen',
                'nim_nip' => '198801012022011001',
                'email_verified_at' => now(),
            ]
        );

        // 3. Akun Demo Mahasiswa
        User::updateOrCreate(
            ['email' => 'mahasiswa@kampuslms.test'],
            [
                'name' => 'Mahasiswa Demo', 
                'password' => Hash::make('password'),
                'role' => 'mahasiswa',
                'nim_nip' => '10241001',
                'email_verified_at' => now(),
            ]
        );
    }
}