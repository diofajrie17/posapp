<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Log;

class PurchasePayment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'purchase_id',
        'amount',
        'payment_date',
        'payment_type',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'amount' => 'decimal:2',
    ];

    /**
     * Boot method to add model event listeners
     */
    protected static function boot()
    {
        parent::boot();

        static::created(function ($payment) {
            Log::info('Purchase Payment Created', [
                'payment_id' => $payment->id,
                'purchase_id' => $payment->purchase_id,
                'amount' => $payment->amount,
                'payment_type' => $payment->payment_type,
                'created_by' => $payment->created_by,
                'user' => auth()->user()?->name,
            ]);

            // Update purchase payment status
            $payment->purchase->updatePaymentStatus();
        });

        static::deleted(function ($payment) {
            Log::warning('Purchase Payment Deleted', [
                'payment_id' => $payment->id,
                'purchase_id' => $payment->purchase_id,
                'amount' => $payment->amount,
                'deleted_by' => auth()->id(),
                'user' => auth()->user()?->name,
            ]);

            // Update purchase payment status
            $payment->purchase->updatePaymentStatus();
        });

        static::restored(function ($payment) {
            Log::info('Purchase Payment Restored', [
                'payment_id' => $payment->id,
                'purchase_id' => $payment->purchase_id,
                'amount' => $payment->amount,
                'restored_by' => auth()->id(),
                'user' => auth()->user()?->name,
            ]);

            // Update purchase payment status
            $payment->purchase->updatePaymentStatus();
        });
    }

    /**
     * Relationship to Purchase
     */
    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }

    /**
     * Relationship to User (creator)
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}

