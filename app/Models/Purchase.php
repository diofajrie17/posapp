<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_number',
        'supplier_name',
        'supplier_phone',
        'supplier_address',
        'purchase_date',
        'total_amount',
        'paid_amount',
        'payment_status',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
    ];

    public function items()
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function batches()
    {
        return $this->hasMany(InventoryBatch::class);
    }

    public function payments()
    {
        return $this->hasMany(PurchasePayment::class);
    }

    /**
     * Get remaining unpaid amount
     */
    public function getRemainingAmountAttribute()
    {
        return $this->total_amount - $this->paid_amount;
    }

    /**
     * Update payment status based on paid amount
     */
    public function updatePaymentStatus()
    {
        $totalPaid = $this->payments()->sum('amount');
        
        $this->paid_amount = $totalPaid;
        
        if ($totalPaid <= 0) {
            $this->payment_status = 'unpaid';
        } elseif ($totalPaid >= $this->total_amount) {
            $this->payment_status = 'paid';
        } else {
            $this->payment_status = 'partial';
        }
        
        $this->saveQuietly(); // Save without triggering events
    }

    /**
     * Auto-generate purchase number: PO-YYYYMMDD-00001
     */
    public static function generatePurchaseNumber()
    {
        $date = now()->format('Ymd');
        $latest = self::where('purchase_number', 'like', "PO-{$date}-%")
            ->orderByDesc('purchase_number')
            ->first();

        if (!$latest) {
            return "PO-{$date}-00001";
        }

        $lastNumber = (int) substr($latest->purchase_number, -5);
        return "PO-{$date}-" . str_pad($lastNumber + 1, 5, '0', STR_PAD_LEFT);
    }
}

