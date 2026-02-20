<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
<<<<<<< HEAD
            ExpeditionSeeder::class,
=======
>>>>>>> 8415c2504e0943d7af6fcb75f06c3f500ecde573
        ]);
    }
}
