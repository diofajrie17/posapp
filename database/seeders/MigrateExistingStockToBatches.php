<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\InventoryBatch;
use Illuminate\Database\Seeder;

class MigrateExistingStockToBatches extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * This seeder migrates existing product stock into inventory batches
     * so the FIFO system can start tracking stock properly.
     */
    public function run(): void
    {
        $this->command->info('Starting stock migration to batches...');

        $products = Product::where('stock', '>', 0)->get();
        $batchesCreated = 0;

        foreach ($products as $product) {
            // Create initial batch with current stock
            $batch = InventoryBatch::create([
                'product_id' => $product->id,
                'batch_code' => InventoryBatch::generateBatchCode($product->id),
                'quantity_in_base_unit' => $product->stock,
                'quantity_remaining' => $product->stock,
                'cost_per_base_unit' => $product->cost_price ?? 0, // Use current cost or 0
                'purchased_at' => now()->subDay(), // Set as yesterday
                'supplier' => 'Initial Stock',
                'notes' => 'Migrated from existing stock during FIFO implementation',
            ]);

            $batchesCreated++;

            $this->command->info(
                "✓ {$product->name}: {$product->stock} units → Batch {$batch->batch_code}"
            );
        }

        $this->command->newLine();
        $this->command->info("✅ Migration complete! Created {$batchesCreated} batches.");
        $this->command->info("⚠️  Please verify batch cost prices are correct in the database.");
    }
}
