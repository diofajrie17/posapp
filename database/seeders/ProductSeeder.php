<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Unit;
use App\Models\Category;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // First, ensure we have units
        $pcs = Unit::firstOrCreate(['name' => 'Pcs', 'symbol' => 'pcs']);
        $bottle = Unit::firstOrCreate(['name' => 'Bottle', 'symbol' => 'btl']);
        $box = Unit::firstOrCreate(['name' => 'Box', 'symbol' => 'box']);
        $kg = Unit::firstOrCreate(['name' => 'Kg', 'symbol' => 'kg']);
        $sack = Unit::firstOrCreate(['name' => 'Sack', 'symbol' => 'sack']);
        $pack = Unit::firstOrCreate(['name' => 'Pack', 'symbol' => 'pack']);
        $liter = Unit::firstOrCreate(['name' => 'Liter', 'symbol' => 'L']);

        // Create categories
        $beverages = Category::firstOrCreate(['name' => 'Beverages']);
        $food = Category::firstOrCreate(['name' => 'Food']);
        $snacks = Category::firstOrCreate(['name' => 'Snacks']);
        $household = Category::firstOrCreate(['name' => 'Household']);

        // Sample products with base unit inventory system
        $products = [
            // Product with derived unit pricing (bulk discount)
            [
                'name' => 'Mineral Water 600ml',
                'category_id' => $beverages->id,
                'unit_id' => $box->id,              // Sold in boxes
                'base_unit_id' => $bottle->id,      // Base unit is bottle
                'unit_quantity' => 24,               // 24 bottles per box
                'stock' => 480,                      // Stock in base units (bottles)
                'price' => 72000,                    // Default price (box price)
                'cost_price' => 60000,              // Cost per box
                'base_unit_price' => 3200,          // Price per bottle (slightly higher than bulk)
                'derived_unit_price' => 72000,      // Special price per box (discount for bulk)
                'min_stock' => 100,
                'max_stock' => 1000,
                'is_active' => true,
            ],
            
            // Single unit product (no derived unit)
            [
                'name' => 'Premium Coffee Beans',
                'category_id' => $beverages->id,
                'unit_id' => $kg->id,
                'base_unit_id' => null,
                'unit_quantity' => 1,
                'stock' => 50,
                'price' => 120000,
                'cost_price' => 90000,
                'base_unit_price' => null,
                'derived_unit_price' => null,
                'min_stock' => 10,
                'max_stock' => 100,
                'is_active' => true,
            ],
            
            // Product with derived unit (no special pricing - calculated)
            [
                'name' => 'Instant Noodles',
                'category_id' => $food->id,
                'unit_id' => $pack->id,              // Pack of 5
                'base_unit_id' => $pcs->id,          // Individual pieces
                'unit_quantity' => 5,
                'stock' => 200,                       // Stock in pieces
                'price' => 15000,                     // Price per pack
                'cost_price' => 12000,
                'base_unit_price' => null,            // Will be calculated (15000/5 = 3000)
                'derived_unit_price' => null,         // Will use default price
                'min_stock' => 50,
                'max_stock' => 500,
                'is_active' => true,
            ],
            
            // Product with special bulk pricing
            [
                'name' => 'Rice Premium',
                'category_id' => $food->id,
                'unit_id' => $sack->id,               // Sack (25kg)
                'base_unit_id' => $kg->id,            // Kg
                'unit_quantity' => 25,
                'stock' => 500,                        // Stock in kg
                'price' => 500000,                     // Price per sack
                'cost_price' => 400000,
                'base_unit_price' => 21000,            // Price per kg (slightly higher)
                'derived_unit_price' => 500000,        // Bulk price per sack (discount)
                'min_stock' => 100,
                'max_stock' => 1000,
                'is_active' => true,
            ],
            
            // Snack with pack pricing
            [
                'name' => 'Potato Chips',
                'category_id' => $snacks->id,
                'unit_id' => $box->id,                 // Box of 12
                'base_unit_id' => $pcs->id,
                'unit_quantity' => 12,
                'stock' => 144,                         // Stock in pieces
                'price' => 120000,                      // Price per box
                'cost_price' => 96000,
                'base_unit_price' => 11000,             // Price per piece
                'derived_unit_price' => 120000,         // Box price (savings when buying box)
                'min_stock' => 24,
                'max_stock' => 300,
                'is_active' => true,
            ],
            
            // Liquid product
            [
                'name' => 'Cooking Oil',
                'category_id' => $household->id,
                'unit_id' => $liter->id,
                'base_unit_id' => null,
                'unit_quantity' => 1,
                'stock' => 85,
                'price' => 18000,
                'cost_price' => 15000,
                'base_unit_price' => null,
                'derived_unit_price' => null,
                'min_stock' => 20,
                'max_stock' => 200,
                'is_active' => true,
            ],
            
            // Low stock product example
            [
                'name' => 'Energy Drink',
                'category_id' => $beverages->id,
                'unit_id' => $box->id,
                'base_unit_id' => $bottle->id,
                'unit_quantity' => 24,
                'stock' => 15,                          // Low stock!
                'price' => 144000,
                'cost_price' => 120000,
                'base_unit_price' => 6500,
                'derived_unit_price' => 144000,
                'min_stock' => 48,                      // Min stock is 2 boxes
                'max_stock' => 480,
                'is_active' => true,
            ],
        ];

        foreach ($products as $productData) {
            Product::create($productData);
        }

        $this->command->info('✓ Products seeded successfully with inventory data!');
    }
}
