<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BatchAllocation extends Model
{
    use HasFactory;

    protected $fillable = [
        'sales_item_id',
        'inventory_batch_id',
        'quantity_allocated',
        'cost_per_unit',
    ];

    protected $casts = [
        'quantity_allocated' => 'decimal:4',
        'cost_per_unit' => 'decimal:2',
    ];

    /**
     * Get the sales item this allocation belongs to
     */
    public function salesItem(): BelongsTo
    {
        return $this->belongsTo(SalesItem::class);
    }

    /**
     * Get the inventory batch this allocation uses
     */
    public function inventoryBatch(): BelongsTo
    {
        return $this->belongsTo(InventoryBatch::class);
    }

    /**
     * Get total cost for this allocation
     */
    public function getTotalCostAttribute(): float
    {
        return $this->quantity_allocated * $this->cost_per_unit;
    }
}
