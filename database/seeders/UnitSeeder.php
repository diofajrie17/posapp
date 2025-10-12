<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Unit;

class UnitSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        $units = [
            // Base Units
            [
                'name' => 'pcs',
                'symbol' => 'pcs',
                'is_base_unit' => true,
            ],
            [
                'name' => 'Kg',
                'symbol' => 'kg',
                'is_base_unit' => true,
            ],
            
            // Derived Units
            [
                'name' => 'Box',
                'symbol' => 'box',
                'is_base_unit' => false,
            ],
            [
                'name' => 'Pack',
                'symbol' => 'pack',
                'is_base_unit' => false,
            ],
        ];

        foreach ($units as $unit) {
            Unit::create($unit);
        }
    }
}
