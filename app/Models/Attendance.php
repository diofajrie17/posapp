<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'member_id',
        'customer_name',
        'customer_phone',
        'check_in_time',
        'check_out_time',
        'type',
        'date',
        'notes',
    ];

    protected $casts = [
        'check_in_time' => 'datetime',
        'check_out_time' => 'datetime',
        'date' => 'date',
    ];

    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    public function scopeMemberType($query)
    {
        return $query->where('type', 'member');
    }

    public function scopeDailyType($query)
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
        if ($this->type === 'member' && $this->member) {
            return $this->member->full_name;
        }
        return $this->customer_name ?? '-';
    }

    public function getDisplayPhoneAttribute()
    {
        if ($this->type === 'member' && $this->member) {
            return $this->member->phone;
        }
        return $this->customer_phone ?? '-';
    }
}

