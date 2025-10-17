<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'symbol',
        'type',
        'is_base_unit'
    ];

    protected $casts = [
        'is_base_unit' => 'boolean',
        'is_active' => 'boolean'
    ];

    // Relationship to products
    public function products()
    {
        return $this->hasMany(Product::class, 'unit_id');
    }

    // Relationship to products using this as base unit
    public function baseUnitProducts()
    {
        return $this->hasMany(Product::class, 'base_unit_id');
    }

    // Scope for active units
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Scope for filtering by type
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    // Parent unit relationship (for derived units)
    public function parentUnit()
    {
        return $this->belongsTo(Unit::class, 'parent_unit_id');
    }

    // Child units (derived from this base unit)
    public function derivedUnits()
    {
        return $this->hasMany(Unit::class, 'parent_unit_id');
    }

    /**
     * Get conversion factor for this unit
     * Base units return 1, derived units return their conversion_factor
     */
    public function getConversionFactorAttribute()
    {
        return $this->attributes['conversion_factor'] ?? 1;
    }

    /**
     * Convert quantity from this unit to base unit
     */
    public function toBaseUnit($quantity)
    {
        return $quantity * $this->conversion_factor;
    }
}
