<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KelasPayment extends Model
{
    use HasFactory;

    protected $table = 'kelas_payments';

    protected $fillable = [
        'kelas_id',
        'member_id',
        'registration_id',
        'payment_date',
        'amount',
        'payment_method',
        'reference_number',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function kelas()
    {
        return $this->belongsTo(Kelas::class);
    }

    public function member()
    {
        return $this->belongsTo(KelasMember::class, 'member_id');
    }

    public function registration()
    {
        return $this->belongsTo(KelasRegistration::class, 'registration_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}


