<?php

namespace Database\Seeders;

use App\Models\SlaDefinition;
use Illuminate\Database\Seeder;

class SlaDefinitionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $definitions = [
            [
                'code' => 'same-day',
                'name' => 'Same Day',
                'deadline_hours' => 8,
                'description' => 'Paket harus sampai dalam 8 jam dari pickup',
                'is_active' => true,
            ],
            [
                'code' => 'next-day',
                'name' => 'Next Day',
                'deadline_hours' => 24,
                'description' => 'Paket harus sampai dalam 24 jam dari pickup',
                'is_active' => true,
            ],
            [
                'code' => 'regular',
                'name' => 'Regular',
                'deadline_hours' => 48,
                'description' => 'Paket harus sampai dalam 48 jam dari pickup',
                'is_active' => true,
            ],
        ];

        foreach ($definitions as $definition) {
            SlaDefinition::updateOrCreate(
                ['code' => $definition['code']],
                $definition
            );
        }
    }
}
