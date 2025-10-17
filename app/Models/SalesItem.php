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
        'unit_cogs',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'price_each' => 'decimal:2',
        'unit_cogs' => 'decimal:2',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function transaction()
    {
        return $this->belongsTo(SalesTransaction::class, 'transaction_id');
    }
}
