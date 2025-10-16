<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Facility extends Model
{
    use HasFactory;

    protected $fillable = [
        'date',
        'amount',
        'description',
        'type', // e.g., 'parking', 'equipment_rental', 'space_rental', etc.
        'customer_name',
        'duration', // for rentals (in hours/days)
        'notes',
    ];

    protected $casts = [
        'date' => 'date',
        'amount' => 'decimal:2',
        'duration' => 'decimal:2',
    ];

    public function getFormattedAmountAttribute()
    {
        return 'Rp ' . number_format($this->amount, 0, ',', '.');
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }

    public function scopeByMonth($query, $year, $month)
    {
        return $query->whereYear('date', $year)->whereMonth('date', $month);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }
}