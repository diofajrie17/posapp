<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        $categories = [
            'Makanan',
            'Minuman',
            'Snack',
            'Peralatan Olahraga',
            'Supplement',
            'Aksesoris',
            'Lainnya'
        ];

        foreach ($categories as $category) {
            Category::create(['name' => $category]);
        }
    }
}
