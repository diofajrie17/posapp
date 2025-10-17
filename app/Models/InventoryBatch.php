<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InventoryBatch extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'batch_code',
        'quantity_in_base_unit',
        'quantity_remaining',
        'cost_per_base_unit',
        'purchased_at',
        'supplier',
        'notes',
    ];

    protected $casts = [
        'quantity_in_base_unit' => 'decimal:4',
        'quantity_remaining' => 'decimal:4',
        'cost_per_base_unit' => 'decimal:2',
        'purchased_at' => 'datetime',
    ];

    /**
     * Get the product that owns this batch
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get all allocations from this batch
     */
    public function allocations(): HasMany
    {
        return $this->hasMany(BatchAllocation::class);
    }

    /**
     * Check if batch has remaining stock
     */
    public function hasStock(): bool
    {
        return $this->quantity_remaining > 0;
    }

    /**
     * Get the quantity that has been allocated (sold)
     */
    public function getQuantityAllocatedAttribute(): float
    {
        return $this->quantity_in_base_unit - $this->quantity_remaining;
    }

    /**
     * Deduct quantity from this batch
     */
    public function deduct(float $quantity): void
    {
        $this->quantity_remaining = max(0, $this->quantity_remaining - $quantity);
        $this->save();
    }

    /**
     * Generate unique batch code
     */
    public static function generateBatchCode(int $productId): string
    {
        $prefix = 'B' . str_pad($productId, 4, '0', STR_PAD_LEFT);
        $timestamp = now()->format('ymdHis');
        $random = strtoupper(substr(md5(uniqid()), 0, 4));
        
        return "{$prefix}-{$timestamp}-{$random}";
    }

    /**
     * Scope: Get batches with available stock for FIFO
     */
    public function scopeAvailable($query)
    {
        return $query->where('quantity_remaining', '>', 0);
    }

    /**
     * Scope: Order by purchase date (oldest first for FIFO)
     */
    public function scopeFifoOrder($query)
    {
        return $query->orderBy('purchased_at', 'asc')->orderBy('id', 'asc');
    }
}
