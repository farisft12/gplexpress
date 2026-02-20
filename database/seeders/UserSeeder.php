<?php

namespace Database\Seeders;

use App\Models\User;
<<<<<<< HEAD
use App\Models\Branch;
=======
>>>>>>> 8415c2504e0943d7af6fcb75f06c3f500ecde573
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
<<<<<<< HEAD
        // Create Owner first (no branch_id needed)
        User::updateOrCreate(
            ['email' => 'owner@gmail.com'],
            [
                'name' => 'faris',
                'email' => 'owner@gmail.com',
                'password' => Hash::make('Farisft123@@'),
                'role' => 'owner',
                'status' => 'active',
                'branch_id' => null, // Owner tidak punya branch_id
            ]
        );

        // Create or get Surabaya Branch (without manager_id first)
        $surabayaBranch = Branch::firstOrCreate(
            ['code' => 'SBY'],
            [
                'name' => 'Surabaya',
                'code' => 'SBY',
                'address' => 'Surabaya, Jawa Timur',
                'status' => 'active',
                'manager_id' => null, // Will be set after manager is created
            ]
        );

        // Create Manager Surabaya
        $managerSurabaya = User::updateOrCreate(
            ['email' => 'surabaya@gmail.com'],
            [
                'name' => 'Manager Surabaya',
                'email' => 'surabaya@gmail.com',
                'password' => Hash::make('Devil123@@'),
                'role' => 'manager',
                'status' => 'active',
                'branch_id' => $surabayaBranch->id,
            ]
        );

        // Update branch with manager_id
        $surabayaBranch->update(['manager_id' => $managerSurabaya->id]);

        // Create Admin Surabaya
        User::updateOrCreate(
            ['email' => 'adminsby@gmail.com'],
            [
                'name' => 'Admin Surabaya',
                'email' => 'adminsby@gmail.com',
                'password' => Hash::make('Devil123@@'),
                'role' => 'admin',
                'status' => 'active',
                'branch_id' => $surabayaBranch->id,
            ]
        );

        // Create Kurir Surabaya
        User::updateOrCreate(
            ['email' => 'kurirsby@gmail.com'],
            [
                'name' => 'Kurir Surabaya',
                'email' => 'kurirsby@gmail.com',
                'password' => Hash::make('Devil12@@'),
                'role' => 'kurir',
                'status' => 'active',
                'branch_id' => $surabayaBranch->id,
            ]
        );
=======
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
>>>>>>> 8415c2504e0943d7af6fcb75f06c3f500ecde573
    }
}







<<<<<<< HEAD





=======
>>>>>>> 8415c2504e0943d7af6fcb75f06c3f500ecde573
