<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalesTransaction extends Model 
{
    use HasFactory;
    
    protected $fillable = [
        'member_id',
        'is_daily_guest',
        'payment_type',
        'subtotal_amount',
        'discount_type',
        'discount_value',
        'discount_amount',
        'total_amount',
        'cogs_amount',
        'paid_amount',
        'change_amount',
        'notes',
        'date_time',
        'created_by'
    ];
    
    protected $casts = [
        'date_time' => 'datetime',
        'subtotal_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'cogs_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'change_amount' => 'decimal:2',
    ];
    
    public function items()
    {
        return $this->hasMany(SalesItem::class, 'transaction_id');
    }

    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
