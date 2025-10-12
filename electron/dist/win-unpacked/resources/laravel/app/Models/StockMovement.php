<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model {
    protected $fillable=['product_id','direction','quantity','source','source_id','note','moved_at'];
    public function product(){ return $this->belongsTo(Product::class); }
}
