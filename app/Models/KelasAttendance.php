<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KelasAttendance extends Model
{
    use HasFactory;

    protected $table = 'kelas_attendances';

    protected $fillable = [
        'kelas_id',
        'member_id',
        'type',
        'customer_name',
        'customer_phone',
        'amount',
        'payment_method',
        'reference_number',
        'check_in_time',
        'check_out_time',
        'date',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'check_in_time' => 'datetime',
        'check_out_time' => 'datetime',
        'date' => 'date',
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

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeMonthly($query)
    {
        return $query->where('type', 'monthly');
    }

    public function scopeDaily($query)
    {
        return $query->where('type', 'daily');
    }

    public function scopeToday($query)
    {
        return $query->whereDate('date', now());
    }

    public function scopeDateRange($query, $start, $end)
    {
        return $query->whereBetween('date', [$start, $end]);
    }

    public function getDisplayNameAttribute()
    {
        if ($this->type === 'monthly' && $this->member) {
            return $this->member->full_name;
        }
        return $this->customer_name ?? '-';
    }

    public function getDisplayPhoneAttribute()
    {
        if ($this->type === 'monthly' && $this->member) {
            return $this->member->phone;
        }
        return $this->customer_phone ?? '-';
    }
}

