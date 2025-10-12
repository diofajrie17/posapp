<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use App\Models\Member;
use App\Models\Product;
use App\Models\SalesItem;
use App\Models\SalesTransaction;
use App\Models\Expense;


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

        // --- Member akan expired H-5 ---
        $expiringSoon = Member::where('is_active', true)
            ->whereDate('membership_end', $today->copy()->addDays(5))
            ->count();

        // --- 5 Produk Terlaris (hari ini, berdasarkan omzet) ---
        $topProducts = SalesItem::select('product_id',
                DB::raw('SUM(quantity) as qty'),
                DB::raw('SUM(quantity * price_each) as gross'))
            ->whereHas('transaction', fn($q) => $q->whereBetween('date_time', [$today, $tomorrow]))
            ->with('product:id,name,unit')
            ->groupBy('product_id')
            ->orderByDesc('gross')
            ->limit(5)
            ->get();

        // --- 10 Transaksi Terakhir (hari ini) ---
        $recentTransactions = SalesTransaction::with('member:id,full_name')
            ->whereBetween('date_time', [$today, $tomorrow])
            ->orderByDesc('date_time')
            ->limit(10)
            ->get(['id','member_id','is_daily_guest','payment_type','total_amount','date_time']);

        // --- Breakdown metode pembayaran (hari ini) ---
        $paymentBreakdown = SalesTransaction::select('payment_type',
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(total_amount) as total'))
            ->whereBetween('date_time', [$today, $tomorrow])
            ->groupBy('payment_type')
            ->orderByDesc('total')
            ->get();

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
                'expiringH5'  => (int) $expiringSoon,
            ],
            'topProducts'       => $topProducts,
            'recentTransactions'=> $recentTransactions,
            'paymentBreakdown'  => $paymentBreakdown,
        ]);
    }
}
