<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Illuminate\Support\Carbon;
use App\Models\Member;
use App\Models\SalesTransaction;
use App\Models\Expense;
use App\Models\Attendance;


class DashboardController extends Controller
{
    public function __construct()
{
    $this->middleware('permission:products.view')->only(['index','show']);
    $this->middleware('permission:products.create')->only(['create','store']);
    $this->middleware('permission:products.update')->only(['edit','update']);
    $this->middleware('permission:products.delete')->only(['destroy']);
}

    public function index()
    {
        // Sesuaikan timezone jika perlu di config/app.php
        $today = Carbon::today();                 // hari ini 00:00
        $tomorrow = (clone $today)->addDay();     // besok 00:00 (batas atas)

        // --- Ringkasan Harian (gross omzet & jumlah transaksi) ---
        $dailySummary = SalesTransaction::selectRaw('
                COUNT(*) as trx_count,
                COALESCE(SUM(total_amount),0) as gross_total
            ')
            ->whereBetween('date_time', [$today, $tomorrow])
            ->first();

        // --- Pengeluaran harian ---
        $dailyExpense = (float) Expense::whereDate('date', $today)->sum('amount');

        // --- Laba bersih (gross - expense) ---
        $netProfit = (float) $dailySummary->gross_total - $dailyExpense;

        // --- Member aktif (membership_end >= today & is_active = true) ---
        $activeMembers = Member::where('is_active', true)
            ->whereDate('membership_end', '>=', $today)
            ->count();

        // --- Member akan expired (dalam 7 hari ke depan) ---
        $expiringMembers = Member::where('is_active', true)
            ->where('membership_end', '>=', $today)
            ->where('membership_end', '<=', $today->copy()->addDays(7))
            ->orderBy('membership_end')
            ->get(['id', 'full_name', 'phone', 'membership_end']);

        // --- Member aktif yang akan expired (untuk count) ---
        $expiringSoonCount = $expiringMembers->count();

        // --- Member yang check-in hari ini ---
        $todayCheckIns = Attendance::whereDate('date', $today)
            ->where('type', 'member')
            ->with('member:id,full_name,phone')
            ->orderByDesc('check_in_time')
            ->limit(20)
            ->get(['id', 'member_id', 'customer_name', 'check_in_time', 'check_out_time', 'date']);

        return Inertia::render('Dashboard', [
            'today'             => $today->toDateString(),
            'summary'           => [
                'trx_count'   => (int) $dailySummary->trx_count,
                'gross_total' => (float) $dailySummary->gross_total,
                'expense'     => (float) $dailyExpense,
                'net_profit'  => (float) $netProfit,
            ],
            'members'           => [
                'active'      => (int) $activeMembers,
                'expiring_count' => (int) $expiringSoonCount,
                'expiring_list' => $expiringMembers,
            ],
            'checkIns'          => $todayCheckIns,
        ]);
    }
}
