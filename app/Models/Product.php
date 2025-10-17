<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'stock',
        'price',
        'cost_price',
        'unit',
        'unit_id',
        'unit_quantity',
        'base_unit_id',
        'category_id',
        'base_unit_price',
        'derived_unit_price',
        'min_stock',
        'max_stock',
        'is_active'
    ];

    protected $casts = [
        'stock' => 'decimal:2',
        'price' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'base_unit_price' => 'decimal:2',
        'derived_unit_price' => 'decimal:2',
        'min_stock' => 'decimal:2',
        'max_stock' => 'decimal:2',
        'unit_quantity' => 'decimal:4',
        'is_active' => 'boolean',
    ];

    protected $appends = [
        'calculated_base_unit_price',
        'calculated_derived_unit_price',
        'is_low_stock',
        'has_derived_unit'
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function baseUnit()
    {
        return $this->belongsTo(Unit::class, 'base_unit_id');
    }

    /**
     * Get all inventory batches for this product
     */
    public function batches()
    {
        return $this->hasMany(InventoryBatch::class);
    }

    /**
     * Get available inventory batches (with stock)
     */
    public function availableBatches()
    {
        return $this->hasMany(InventoryBatch::class)->available();
    }

    // Get unit display name with quantity info
    public function getUnitDisplayAttribute()
    {
        if ($this->unit_id && $this->unit) {
            if ($this->base_unit_id && $this->baseUnit && $this->unit_quantity > 1) {
                return $this->unit->name . ' (' . $this->formatNumber($this->unit_quantity) . ' ' . $this->baseUnit->name . ')';
            }
            return $this->unit->name;
        }
        return $this->unit; // fallback to string unit field
    }

    // Check if product has a derived unit configuration
    public function getHasDerivedUnitAttribute()
    {
        return $this->base_unit_id && $this->unit_quantity > 1;
    }

    // Calculate base unit price (with fallback to stored value or calculated)
    public function getCalculatedBaseUnitPriceAttribute()
    {
        // If explicit base unit price is set, use it
        if ($this->base_unit_price !== null) {
            return $this->base_unit_price;
        }

        // Calculate from derived unit price if available
        if ($this->has_derived_unit) {
            if ($this->derived_unit_price !== null) {
                return $this->derived_unit_price / $this->unit_quantity;
            }
            // Fallback to price field
            return $this->price / $this->unit_quantity;
        }

        // Single unit product
        return $this->price;
    }

    // Calculate derived unit price (with fallback to stored value or calculated)
    public function getCalculatedDerivedUnitPriceAttribute()
    {
        // If explicit derived unit price is set, use it
        if ($this->derived_unit_price !== null) {
            return $this->derived_unit_price;
        }

        // Calculate from base unit price if available
        if ($this->has_derived_unit) {
            if ($this->base_unit_price !== null) {
                return $this->base_unit_price * $this->unit_quantity;
            }
            // Fallback to price field (assuming price is for derived unit)
            return $this->price;
        }

        return null;
    }

    // Calculate base unit cost price (backward compatible)
    public function getBaseUnitCostPriceAttribute()
    {
        if ($this->has_derived_unit) {
            return $this->cost_price / $this->unit_quantity;
        }
        return null;
    }

    // Check if stock is below minimum
    public function getIsLowStockAttribute()
    {
        return $this->stock <= $this->min_stock;
    }

    // Check if product has sufficient stock (in base units)
    public function hasSufficientStock($quantityInBaseUnits)
    {
        return $this->stock >= $quantityInBaseUnits;
    }

    // Convert quantity to base units
    public function convertToBaseUnits($quantity, $sellInBaseUnit = false)
    {
        if ($this->has_derived_unit && !$sellInBaseUnit) {
            // Selling in derived units, multiply by conversion factor
            return $quantity * $this->unit_quantity;
        }
        // Already in base units
        return $quantity;
    }

    // Deduct stock (always in base units)
    public function deductStock($quantityInBaseUnits)
    {
        if (!$this->hasSufficientStock($quantityInBaseUnits)) {
            throw new \Exception("Insufficient stock for product: {$this->name}. Available: {$this->stock}, Required: {$quantityInBaseUnits}");
        }

        $this->stock -= $quantityInBaseUnits;
        $this->save();

        return $this;
    }

    // Add stock (always in base units)
    public function addStock($quantityInBaseUnits)
    {
        $this->stock += $quantityInBaseUnits;
        $this->save();

        return $this;
    }

    // Get price for specific unit type
    public function getPriceForUnitType($sellInBaseUnit = false)
    {
        if ($this->has_derived_unit) {
            return $sellInBaseUnit 
                ? $this->calculated_base_unit_price 
                : $this->calculated_derived_unit_price;
        }
        return $this->price;
    }

    private function formatNumber($value)
    {
        $num = floatval($value);
        return $num == intval($num) ? intval($num) : rtrim(rtrim(number_format($num, 4), '0'), '.');
    }
}
