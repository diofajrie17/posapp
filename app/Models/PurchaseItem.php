<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_id',
        'product_id',
        'unit_id',
        'unit_name',         // NEW: Custom unit name
        'unit_conversion',   // NEW: Custom conversion factor
        'quantity',
        'quantity_in_base_unit', // NEW: Converted quantity
        'unit_cost',
        'base_quantity',
        'base_unit_cost',
        'subtotal',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_conversion' => 'decimal:4',  // NEW
        'quantity_in_base_unit' => 'decimal:2',  // NEW
        'unit_cost' => 'decimal:2',
        'base_quantity' => 'decimal:2',
        'base_unit_cost' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }
}

