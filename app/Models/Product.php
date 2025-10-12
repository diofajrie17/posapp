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
        'category_id'
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

    private function formatNumber($value)
    {
        $num = floatval($value);
        return $num == intval($num) ? intval($num) : rtrim(rtrim(number_format($num, 4), '0'), '.');
    }
}
