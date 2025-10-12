<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalesTransaction extends Model {
    protected $fillable = ['member_id','is_daily_guest','payment_type','total_amount','date_time'];
    public function items(){ return $this->hasMany(SalesItem::class, 'transaction_id'); }
    public function member(){ return $this->belongsTo(Member::class); }
}
