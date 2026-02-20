<?php

namespace Database\Seeders;

use App\Models\Expedition;
use Illuminate\Database\Seeder;

class ExpeditionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $expeditions = [
            ['name' => 'JNE', 'code' => 'JNE'],
            ['name' => 'J&T Express', 'code' => 'JT'],
            ['name' => 'Pos Indonesia', 'code' => 'POS'],
            ['name' => 'Sicepat', 'code' => 'SICEPAT'],
            ['name' => 'Ninja Express', 'code' => 'NINJA'],
            ['name' => 'TIKI', 'code' => 'TIKI'],
            ['name' => 'Wahana', 'code' => 'WAHANA'],
            ['name' => 'ID Express', 'code' => 'IDEX'],
            ['name' => 'Lion Parcel', 'code' => 'LION'],
            ['name' => 'J&T Cargo', 'code' => 'JTC'],
        ];

        foreach ($expeditions as $exp) {
            Expedition::firstOrCreate(
                ['name' => $exp['name']],
                ['code' => $exp['code'], 'status' => 'active']
            );
        }
    }
}
