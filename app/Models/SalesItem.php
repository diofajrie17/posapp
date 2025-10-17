<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalesItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_id',
        'product_id',
        'quantity',
        'price_each',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function transaction()
    {
        return $this->belongsTo(SalesTransaction::class, 'transaction_id');
    }

    /**
     * Get all batch allocations for this sales item
     */
    public function allocations()
    {
        return $this->hasMany(BatchAllocation::class);
    }

    /**
     * Get total COGS (Cost of Goods Sold) for this item
     */
    public function getCogs(): float
    {
        return $this->allocations()
            ->get()
            ->sum(function ($allocation) {
                return $allocation->quantity_allocated * $allocation->cost_per_unit;
            });
    }

    /**
     * Get gross profit (Revenue - COGS)
     */
    public function getGrossProfit(): float
    {
        $revenue = $this->quantity * $this->price_each;
        return $revenue - $this->getCogs();
    }
}
