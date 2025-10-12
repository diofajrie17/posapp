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
}
