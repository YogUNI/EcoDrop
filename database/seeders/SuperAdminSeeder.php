<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name'        => 'Super Admin',
            'email'       => 'superadmin@ecodrop.com', // ← ganti sesuai keinginan
            'password'    => Hash::make('password123'), // ← ganti password yang kuat!
            'role'        => 'super_admin',
            'points'      => 0,
            'is_verified' => true, // super admin langsung verified
        ]);
    }
}