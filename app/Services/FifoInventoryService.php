<?php

namespace App\Services;

use App\Models\InventoryBatch;
use App\Models\BatchAllocation;
use App\Models\SalesItem;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Exception;

class FifoInventoryService
{
    /**
     * Allocate stock for a sale using FIFO method
     * 
     * @param SalesItem $salesItem
     * @param float $quantityInBaseUnit Quantity to allocate in base unit
     * @return array Array of allocations made
     * @throws Exception If insufficient stock
     */
    public function allocateStock(SalesItem $salesItem, float $quantityInBaseUnit): array
    {
        $product = $salesItem->product;
        $remainingToAllocate = $quantityInBaseUnit;
        $allocations = [];

        // Get available batches ordered by FIFO (oldest first)
        $batches = InventoryBatch::where('product_id', $product->id)
            ->available()
            ->fifoOrder()
            ->lockForUpdate() // Lock rows to prevent race conditions
            ->get();

        // Check if we have enough total stock
        $totalAvailable = $batches->sum('quantity_remaining');
        if ($totalAvailable < $quantityInBaseUnit) {
            throw new Exception(
                "Insufficient stock for {$product->name}. " .
                "Required: {$quantityInBaseUnit}, Available: {$totalAvailable}"
            );
        }

        // Allocate from oldest batches first (FIFO)
        foreach ($batches as $batch) {
            if ($remainingToAllocate <= 0) {
                break;
            }

            // Calculate how much to take from this batch
            $quantityToAllocate = min($remainingToAllocate, $batch->quantity_remaining);

            // Create allocation record
            $allocation = BatchAllocation::create([
                'sales_item_id' => $salesItem->id,
                'inventory_batch_id' => $batch->id,
                'quantity_allocated' => $quantityToAllocate,
                'cost_per_unit' => $batch->cost_per_base_unit,
            ]);

            // Deduct from batch
            $batch->deduct($quantityToAllocate);

            $allocations[] = $allocation;
            $remainingToAllocate -= $quantityToAllocate;
        }

        return $allocations;
    }

    /**
     * Add new inventory batch (when purchasing stock)
     * 
     * @param int $productId
     * @param float $quantityInBaseUnit
     * @param float $costPerBaseUnit
     * @param string|null $supplier
     * @param string|null $notes
     * @return InventoryBatch
     */
    public function addBatch(
        int $productId,
        float $quantityInBaseUnit,
        float $costPerBaseUnit,
        ?string $supplier = null,
        ?string $notes = null
    ): InventoryBatch {
        $batch = InventoryBatch::create([
            'product_id' => $productId,
            'batch_code' => InventoryBatch::generateBatchCode($productId),
            'quantity_in_base_unit' => $quantityInBaseUnit,
            'quantity_remaining' => $quantityInBaseUnit,
            'cost_per_base_unit' => $costPerBaseUnit,
            'purchased_at' => now(),
            'supplier' => $supplier,
            'notes' => $notes,
        ]);

        // Update product stock
        $this->updateProductStock($productId);

        return $batch;
    }

    /**
     * Calculate COGS (Cost of Goods Sold) for a sales item
     * 
     * @param SalesItem $salesItem
     * @return float Total COGS
     */
    public function calculateCogs(SalesItem $salesItem): float
    {
        return $salesItem->allocations()
            ->get()
            ->sum(function ($allocation) {
                return $allocation->quantity_allocated * $allocation->cost_per_unit;
            });
    }

    /**
     * Get available stock for a product (sum of all batch remainings)
     * 
     * @param int $productId
     * @return float Total available stock in base unit
     */
    public function getAvailableStock(int $productId): float
    {
        return InventoryBatch::where('product_id', $productId)
            ->available()
            ->sum('quantity_remaining');
    }

    /**
     * Update product's stock field based on batch remainings
     * 
     * @param int $productId
     * @return void
     */
    public function updateProductStock(int $productId): void
    {
        $totalStock = $this->getAvailableStock($productId);
        
        Product::where('id', $productId)->update([
            'stock' => $totalStock
        ]);
    }

    /**
     * Get batch history for a product
     * 
     * @param int $productId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getBatchHistory(int $productId)
    {
        return InventoryBatch::where('product_id', $productId)
            ->with('allocations.salesItem')
            ->orderBy('purchased_at', 'desc')
            ->get();
    }

    /**
     * Deallocate stock (for sale cancellation/return)
     * 
     * @param SalesItem $salesItem
     * @return void
     */
    public function deallocateStock(SalesItem $salesItem): void
    {
        DB::transaction(function () use ($salesItem) {
            // Get all allocations for this sales item
            $allocations = $salesItem->allocations;

            foreach ($allocations as $allocation) {
                $batch = $allocation->inventoryBatch;
                
                // Return stock to batch
                $batch->quantity_remaining += $allocation->quantity_allocated;
                $batch->save();

                // Delete allocation
                $allocation->delete();
            }

            // Update product stock
            $this->updateProductStock($salesItem->product_id);
        });
    }

    /**
     * Get oldest batch for a product (next to be used)
     * 
     * @param int $productId
     * @return InventoryBatch|null
     */
    public function getOldestBatch(int $productId): ?InventoryBatch
    {
        return InventoryBatch::where('product_id', $productId)
            ->available()
            ->fifoOrder()
            ->first();
    }

    /**
     * Get inventory valuation (total value of all batches)
     * 
     * @param int|null $productId If null, calculates for all products
     * @return float Total inventory value
     */
    public function getInventoryValuation(?int $productId = null): float
    {
        $query = InventoryBatch::query();
        
        if ($productId) {
            $query->where('product_id', $productId);
        }

        return $query->available()
            ->get()
            ->sum(function ($batch) {
                return $batch->quantity_remaining * $batch->cost_per_base_unit;
            });
    }
}
