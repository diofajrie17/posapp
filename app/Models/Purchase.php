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
        'notes',
        'created_by',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'total_amount' => 'decimal:2',
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

