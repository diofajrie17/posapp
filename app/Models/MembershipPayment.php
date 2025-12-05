<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MembershipPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'member_id',
        'membership_package_id',
        'customer_name',
        'customer_phone',
        'type',
        'payment_type',
        'amount',
        'date',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'date' => 'date',
    ];

    /**
     * Get the member that owns the payment.
     */
    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    /**
     * Get the membership package.
     */
    public function membershipPackage()
    {
        return $this->belongsTo(MembershipPackage::class);
    }

    /**
     * Scope a query to only include payments within a date range.
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }

    /**
     * Scope a query to only include registration payments.
     */
    public function scopeRegistration($query)
    {
        return $query->where('type', 'registration');
    }

    /**
     * Scope a query to only include daily payments.
     */
    public function scopeDaily($query)
    {
        return $query->where('type', 'daily');
    }

    /**
     * Scope a query to only include renewal payments.
     */
    public function scopeRenewal($query)
    {
        return $query->where('type', 'renewal');
    }
}

