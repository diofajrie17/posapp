<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryBatch extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'purchase_id',
        'quantity_remaining',
        'unit_cost',
        'purchased_at',
    ];

    protected $casts = [
        'quantity_remaining' => 'decimal:2',
        'unit_cost' => 'decimal:2',
        'purchased_at' => 'datetime',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }

    /**
     * Get oldest batches for a product (FIFO)
     */
    public static function getOldestBatches($productId, $quantityNeeded)
    {
        return self::where('product_id', $productId)
            ->where('quantity_remaining', '>', 0)
            ->orderBy('purchased_at', 'asc')
            ->orderBy('id', 'asc')
            ->get();
    }
}

