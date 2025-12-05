<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kelas extends Model
{
    use HasFactory;

    protected $table = 'kelas';

    protected $fillable = [
        'name',
        'code',
        'description',
        'instructor',
        'capacity',
        'price',
        'monthly_price',
        'daily_price',
        'status',
        'start_date',
        'end_date',
        'created_by',
    ];

    protected $casts = [
        'capacity' => 'integer',
        'price' => 'decimal:2',
        'monthly_price' => 'decimal:2',
        'daily_price' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function registrations()
    {
        return $this->hasMany(KelasRegistration::class);
    }

    public function payments()
    {
        return $this->hasMany(KelasPayment::class);
    }

    public function expenses()
    {
        return $this->hasMany(KelasExpense::class);
    }

    public function attendances()
    {
        return $this->hasMany(KelasAttendance::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Income tracking methods
    public function getTotalIncome($startDate = null, $endDate = null)
    {
        // Income from attendances (daily check-ins and monthly member attendances)
        $attendanceQuery = $this->attendances();
        if ($startDate && $endDate) {
            $attendanceQuery->whereBetween('date', [$startDate, $endDate]);
        }
        $attendanceIncome = $attendanceQuery->sum('amount') ?? 0;
        
        // Income from payments (monthly member registration payments)
        $paymentQuery = $this->payments();
        if ($startDate && $endDate) {
            $paymentQuery->whereBetween('payment_date', [$startDate, $endDate]);
        }
        $paymentIncome = $paymentQuery->sum('amount') ?? 0;
        
        return $attendanceIncome + $paymentIncome;
    }

    public function getMonthlyIncome()
    {
        $attendanceIncome = $this->attendances()
            ->whereMonth('date', now()->month)
            ->whereYear('date', now()->year)
            ->sum('amount') ?? 0;
        
        $paymentIncome = $this->payments()
            ->whereMonth('payment_date', now()->month)
            ->whereYear('payment_date', now()->year)
            ->sum('amount') ?? 0;
        
        return $attendanceIncome + $paymentIncome;
    }

    public function getDailyIncome($date = null)
    {
        $date = $date ?? now()->toDateString();
        return $this->attendances()
            ->whereDate('date', $date)
            ->sum('amount') ?? 0;
    }

    public function getMemberIncome($startDate = null, $endDate = null)
    {
        // Member monthly attendance check-in income
        $attendanceQuery = $this->attendances()->where('type', 'monthly');
        if ($startDate && $endDate) {
            $attendanceQuery->whereBetween('date', [$startDate, $endDate]);
        }
        $attendanceIncome = $attendanceQuery->sum('amount') ?? 0;
        
        // Member registration payment income
        $paymentQuery = $this->payments();
        if ($startDate && $endDate) {
            $paymentQuery->whereBetween('payment_date', [$startDate, $endDate]);
        }
        $paymentIncome = $paymentQuery->sum('amount') ?? 0;
        
        return $attendanceIncome + $paymentIncome;
    }

    public function getDirectIncome($startDate = null, $endDate = null)
    {
        $query = $this->attendances()->where('type', 'daily');
        
        if ($startDate && $endDate) {
            $query->whereBetween('date', [$startDate, $endDate]);
        }
        
        return $query->sum('amount') ?? 0;
    }

    public function getMonthlyMemberIncome()
    {
        $attendanceIncome = $this->attendances()
            ->where('type', 'monthly')
            ->whereMonth('date', now()->month)
            ->whereYear('date', now()->year)
            ->sum('amount') ?? 0;
        
        $paymentIncome = $this->payments()
            ->whereMonth('payment_date', now()->month)
            ->whereYear('payment_date', now()->year)
            ->sum('amount') ?? 0;
        
        return $attendanceIncome + $paymentIncome;
    }

    public function getMonthlyDirectIncome()
    {
        return $this->attendances()
            ->where('type', 'daily')
            ->whereMonth('date', now()->month)
            ->whereYear('date', now()->year)
            ->sum('amount') ?? 0;
    }

    public function getIncomeStats($startDate = null, $endDate = null)
    {
        // Attendance stats
        $attendanceQuery = $this->attendances();
        if ($startDate && $endDate) {
            $attendanceQuery->whereBetween('date', [$startDate, $endDate]);
        }
        
        $attendanceTotal = $attendanceQuery->sum('amount') ?? 0;
        $attendanceMonthlyAmount = (clone $attendanceQuery)->where('type', 'monthly')->sum('amount') ?? 0;
        $attendanceDailyAmount = (clone $attendanceQuery)->where('type', 'daily')->sum('amount') ?? 0;
        $attendanceTotalCount = $attendanceQuery->count();
        $attendanceMonthlyCount = (clone $attendanceQuery)->where('type', 'monthly')->count();
        $attendanceDailyCount = (clone $attendanceQuery)->where('type', 'daily')->count();
        
        // Payment stats (monthly member registrations)
        $paymentQuery = $this->payments();
        if ($startDate && $endDate) {
            $paymentQuery->whereBetween('payment_date', [$startDate, $endDate]);
        }
        
        $paymentAmount = $paymentQuery->sum('amount') ?? 0;
        $paymentCount = $paymentQuery->count();
        
        return [
            'total_amount' => $attendanceTotal + $paymentAmount,
            'monthly_amount' => $attendanceMonthlyAmount + $paymentAmount, // Include payments in monthly
            'daily_amount' => $attendanceDailyAmount,
            'total_count' => $attendanceTotalCount + $paymentCount,
            'monthly_count' => $attendanceMonthlyCount + $paymentCount,
            'daily_count' => $attendanceDailyCount,
        ];
    }
}


