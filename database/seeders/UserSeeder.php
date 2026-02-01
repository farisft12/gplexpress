<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Admin
        User::create([
            'name' => 'Admin GPL Expres',
            'email' => 'admin@gplexpres.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'status' => 'active',
        ]);

        // Create Kurir
        User::create([
            'name' => 'Kurir Test',
            'email' => 'kurir@gplexpres.com',
            'password' => Hash::make('password'),
            'role' => 'kurir',
            'status' => 'active',
        ]);
    }
}







