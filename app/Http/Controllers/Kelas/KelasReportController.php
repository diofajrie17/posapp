<?php

namespace App\Http\Controllers\Kelas;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\KelasExpense;
use App\Models\KelasPayment;
use Illuminate\Http\Request;
use Inertia\Inertia;

class KelasReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:kelas.report.view');
    }

    public function index(Request $request)
    {
        $dateFrom = $request->query('date_from');
        $dateTo = $request->query('date_to');
        $kelasId = $request->query('kelas_id');

        // Build queries for payments and expenses
        $payments = \App\Models\KelasPayment::query();
        $expenses = \App\Models\KelasExpense::query();
        $attendances = \App\Models\KelasAttendance::query();

        if ($kelasId) {
            $payments->where('kelas_id', $kelasId);
            $expenses->where('kelas_id', $kelasId);
            $attendances->where('kelas_id', $kelasId);
        }
        if ($dateFrom) {
            $payments->whereDate('payment_date', '>=', $dateFrom);
            $expenses->whereDate('expense_date', '>=', $dateFrom);
            $attendances->whereDate('date', '>=', $dateFrom);
        }
        if ($dateTo) {
            $payments->whereDate('payment_date', '<=', $dateTo);
            $expenses->whereDate('expense_date', '<=', $dateTo);
            $attendances->whereDate('date', '<=', $dateTo);
        }

        // Calculate income from all sources
        $paymentIncome = (float) $payments->sum('amount');
        $attendanceIncome = (float) $attendances->sum('amount');
        $totalIncome = $paymentIncome + $attendanceIncome;
        
        $totalExpense = (float) $expenses->sum('amount');
        $net = $totalIncome - $totalExpense;

        // Get breakdowns
        // Only monthly members who registered create payment income
        $paymentMonthly = (clone $payments)->sum('amount');
        
        // Only daily check-ins create income, monthly check-ins are just attendance tracking
        $attendanceDaily = (clone $attendances)->where('type', 'daily')->sum('amount');
        $attendanceMonthly = 0; // Monthly members don't generate income at check-in, only at registration

        // Get detailed lists
        $paymentsList = $payments->with(['kelas:id,name', 'member:id,full_name'])->orderBy('payment_date', 'desc')->get();
        $attendancesList = $attendances->with(['kelas:id,name', 'member:id,full_name'])->orderBy('check_in_time', 'desc')->get();
        $expensesList = $expenses->with('kelas:id,name')->orderBy('expense_date', 'desc')->get();

        return Inertia::render('Kelas/Report', [
            'filters' => [ 'date_from' => $dateFrom, 'date_to' => $dateTo, 'kelas_id' => $kelasId ],
            'summary' => [
                'income' => $totalIncome,
                'payment_income' => $paymentIncome,
                'attendance_income' => $attendanceIncome,
                'monthly_income' => $paymentMonthly, // Only member registrations, not check-ins
                'daily_income' => $attendanceDaily, // Only daily check-ins
                'expense' => $totalExpense,
                'net' => $net,
            ],
            'payments' => $paymentsList,
            'attendances' => $attendancesList,
            'expenses' => $expensesList,
            'kelas' => Kelas::orderBy('name')->get(['id','name'])
        ]);
    }
}


