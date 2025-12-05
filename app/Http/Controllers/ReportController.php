<?php

namespace App\Http\Controllers;

use App\Models\SalesTransaction;
use App\Models\SalesItem;
use App\Models\Expense;
use App\Models\Ad;
use App\Models\Facility;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function __construct()
{
    $this->middleware('permission:reports.view');
}

    public function dailySales(Request $request)
    {
        $date = $request->query('date', now()->toDateString());

        // Ringkasan omzet dan jumlah trx
        $summary = SalesTransaction::selectRaw('
                DATE(date_time) as d,
                COUNT(*) as trx_count,
                SUM(total_amount) as gross_total
            ')
            ->whereDate('date_time', $date)
            ->groupBy('d')
            ->first();

        // Breakdown metode pembayaran
        $byPayment = SalesTransaction::select('payment_type',
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(total_amount) as total'))
            ->whereDate('date_time', $date)
            ->groupBy('payment_type')
            ->orderBy('total','desc')
            ->get();

        // 10 produk terlaris (jumlah & omzet)
        $topProducts = SalesItem::select('product_id',
                DB::raw('SUM(quantity) as qty'),
                DB::raw('SUM(quantity * price_each) as gross'))
            ->whereHas('transaction', fn($q) => $q->whereDate('date_time', $date))
            ->with('product:id,name,unit')
            ->groupBy('product_id')
            ->orderByDesc('gross')
            ->limit(10)
            ->get();

        // Daftar transaksi (untuk tabel) dengan items detail
        $transactions = SalesTransaction::with([
                'member:id,full_name',
                'items.product:id,name,unit'
            ])
            ->whereDate('date_time', $date)
            ->orderBy('date_time','desc')
            ->get(['id','member_id','is_daily_guest','payment_type','subtotal_amount','discount_amount','total_amount','cogs_amount','date_time']);


        $sumSubtotal = \App\Models\SalesTransaction::whereDate('date_time', $date)
    ->sum('subtotal_amount');

        $sumDiscount = \App\Models\SalesTransaction::whereDate('date_time', $date)
    ->sum('discount_amount');

        return Inertia::render('Reports/DailySales', [
            'date'         => $date,
            'summary'      => [
                'trx_count'   => $summary->trx_count ?? 0,
                'gross_total' => (float)($summary->gross_total ?? 0),
            ],
            'byPayment'    => $byPayment,
            'topProducts'  => $topProducts,
            'transactions' => $transactions,
            'sumSubtotal'  => $sumSubtotal,
            'sumDiscount'  => $sumDiscount,
        ]);
    }

    public function exportDailySalesCsv(Request $request): StreamedResponse
    {
        $date = $request->query('date', now()->toDateString());
        $filename = "daily_sales_{$date}.csv";

        $rows = SalesTransaction::whereDate('date_time', $date)
            ->orderBy('date_time')
            ->get(['id','date_time','payment_type','total_amount','member_id','is_daily_guest']);

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename={$filename}",
        ];

        return response()->stream(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['ID', 'Tanggal', 'Metode', 'Total', 'MemberID', 'DailyGuest']);
            foreach ($rows as $r) {
                fputcsv($out, [
                    $r->id,
                    $r->date_time,
                    $r->payment_type,
                    $r->total_amount,
                    $r->member_id,
                    $r->is_daily_guest ? 'yes' : 'no',
                ]);
            }
            fclose($out);
        }, 200, $headers);
    }

    public function comprehensive(Request $request)
    {
        $dateFrom = $request->query('date_from', now()->startOfMonth()->toDateString());
        $dateTo = $request->query('date_to', now()->toDateString());

        // Sales Transactions (Income)
        $salesSummary = SalesTransaction::selectRaw('
                COUNT(*) as trx_count,
                COALESCE(SUM(total_amount), 0) as gross_total,
                COALESCE(SUM(subtotal_amount), 0) as subtotal_total,
                COALESCE(SUM(discount_amount), 0) as discount_total,
                COALESCE(SUM(cogs_amount), 0) as total_cogs
            ')
            ->whereBetween(DB::raw('DATE(date_time)'), [$dateFrom, $dateTo])
            ->first();

        // Get sales by date with product details
        $salesByDate = SalesTransaction::selectRaw('
                DATE(date_time) as date,
                COUNT(*) as trx_count,
                SUM(total_amount) as total,
                SUM(cogs_amount) as total_cogs
            ')
            ->whereBetween(DB::raw('DATE(date_time)'), [$dateFrom, $dateTo])
            ->groupBy(DB::raw('DATE(date_time)'))
            ->orderBy('date')
            ->get();

        // Get product sales per day with details (include product category)
        $productSalesByDate = SalesItem::selectRaw('
                DATE(sales_transactions.date_time) as date,
                sales_items.product_id,
                SUM(sales_items.quantity) as total_qty,
                SUM(sales_items.quantity * sales_items.price_each) as total_revenue,
                SUM(sales_items.quantity * sales_items.unit_cogs) as total_cogs
            ')
            ->join('sales_transactions', 'sales_items.transaction_id', '=', 'sales_transactions.id')
            ->whereBetween(DB::raw('DATE(sales_transactions.date_time)'), [$dateFrom, $dateTo])
            ->groupBy('date', 'sales_items.product_id')
            ->with(['product:id,name,unit,category_id', 'product.category:id,name'])
            ->orderBy('date')
            ->orderByDesc('total_revenue')
            ->get();

        $salesByPayment = SalesTransaction::select('payment_type',
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(total_amount) as total'))
            ->whereBetween(DB::raw('DATE(date_time)'), [$dateFrom, $dateTo])
            ->groupBy('payment_type')
            ->orderByDesc('total')
            ->get();

        // Regular Expenses (Pengeluaran)
        $expensesSummary = Expense::selectRaw('
                COUNT(*) as count,
                COALESCE(SUM(amount), 0) as total
            ')
            ->whereBetween('date', [$dateFrom, $dateTo])
            ->first();

        $expensesByDate = Expense::selectRaw('
                date,
                COUNT(*) as count,
                SUM(amount) as total
            ')
            ->whereBetween('date', [$dateFrom, $dateTo])
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Ads Expenses (Separate Expense Category)
        $adsSummary = Ad::selectRaw('
                COUNT(*) as count,
                COALESCE(SUM(amount), 0) as total
            ')
            ->whereBetween('date', [$dateFrom, $dateTo])
            ->first();

        $adsByType = Ad::select('type',
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(amount) as total'))
            ->whereBetween('date', [$dateFrom, $dateTo])
            ->groupBy('type')
            ->orderByDesc('total')
            ->get();

        // Facility Income (Separate Income Category)
        $facilitySummary = Facility::selectRaw('
                COUNT(*) as count,
                COALESCE(SUM(amount), 0) as total
            ')
            ->whereBetween('date', [$dateFrom, $dateTo])
            ->first();

        $facilityByType = Facility::select('type',
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(amount) as total'))
            ->whereBetween('date', [$dateFrom, $dateTo])
            ->groupBy('type')
            ->orderByDesc('total')
            ->get();

        // Membership Payments (Income from member registrations and daily plans)
        $membershipSummary = \App\Models\MembershipPayment::selectRaw('
                COUNT(*) as count,
                COALESCE(SUM(amount), 0) as total
            ')
            ->whereBetween('date', [$dateFrom, $dateTo])
            ->first();

        $membershipByType = \App\Models\MembershipPayment::select('type',
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(amount) as total'))
            ->whereBetween('date', [$dateFrom, $dateTo])
            ->groupBy('type')
            ->orderByDesc('total')
            ->get();

        $membershipByPayment = \App\Models\MembershipPayment::select('payment_type',
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(amount) as total'))
            ->whereBetween('date', [$dateFrom, $dateTo])
            ->groupBy('payment_type')
            ->orderByDesc('total')
            ->get();

        // Top Products with HPP calculation
        $topProducts = SalesItem::select('product_id',
                DB::raw('SUM(quantity) as qty'),
                DB::raw('SUM(quantity * price_each) as gross'),
                DB::raw('SUM(quantity * unit_cogs) as total_cogs'))
            ->whereHas('transaction', fn($q) => $q->whereBetween(DB::raw('DATE(date_time)'), [$dateFrom, $dateTo]))
            ->with('product:id,name,unit')
            ->groupBy('product_id')
            ->orderByDesc('gross')
            ->limit(10)
            ->get();

        // Get detailed sales for CSV format view
        $detailedSales = SalesTransaction::with('member:id,full_name')
            ->whereBetween(DB::raw('DATE(date_time)'), [$dateFrom, $dateTo])
            ->orderBy('date_time')
            ->get();

        // Get detailed expenses for CSV format view
        $detailedExpenses = Expense::whereBetween('date', [$dateFrom, $dateTo])
            ->orderBy('date')
            ->get();

        // Get detailed ads for CSV format view
        $detailedAds = Ad::whereBetween('date', [$dateFrom, $dateTo])
            ->orderBy('date')
            ->get();

        // Get detailed facilities for CSV format view
        $detailedFacilities = Facility::whereBetween('date', [$dateFrom, $dateTo])
            ->orderBy('date')
            ->get();

        // Get detailed memberships for CSV format view
        $detailedMemberships = \App\Models\MembershipPayment::whereBetween('date', [$dateFrom, $dateTo])
            ->orderBy('date')
            ->get();

        // Calculate totals for profit/loss
        $salesTotal = $salesSummary ? (float)($salesSummary->gross_total ?? 0) : 0;
        $facilityTotal = $facilitySummary ? (float)($facilitySummary->total ?? 0) : 0;
        $membershipTotal = $membershipSummary ? (float)($membershipSummary->total ?? 0) : 0;
        
        $totalRevenue = $salesTotal + $facilityTotal + $membershipTotal;
        $totalCOGS = $salesSummary ? (float)($salesSummary->total_cogs ?? 0) : 0;
        $grossProfit = $totalRevenue - $totalCOGS;
        
        $expensesTotal = $expensesSummary ? (float)($expensesSummary->total ?? 0) : 0;
        $adsTotal = $adsSummary ? (float)($adsSummary->total ?? 0) : 0;
        $totalExpenses = $expensesTotal + $adsTotal;
        
        $netProfit = $grossProfit - $totalExpenses;

        return Inertia::render('Reports/Comprehensive', [
            'filters' => [
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
            ],
            'sales' => [
                'summary' => $salesSummary,
                'by_date' => $salesByDate,
                'product_by_date' => $productSalesByDate,
                'by_payment' => $salesByPayment,
            ],
            'expenses' => [
                'summary' => $expensesSummary,
                'by_date' => $expensesByDate,
            ],
            'ads' => [
                'summary' => $adsSummary,
                'by_type' => $adsByType,
            ],
            'facilities' => [
                'summary' => $facilitySummary,
                'by_type' => $facilityByType,
            ],
            'memberships' => [
                'summary' => $membershipSummary,
                'by_type' => $membershipByType,
                'by_payment' => $membershipByPayment,
            ],
            'top_products' => $topProducts,
            'detailed_data' => [
                'sales' => $detailedSales,
                'expenses' => $detailedExpenses,
                'ads' => $detailedAds,
                'facilities' => $detailedFacilities,
                'memberships' => $detailedMemberships,
            ],
            'totals' => [
                'revenue' => $totalRevenue,
                'cogs' => $totalCOGS,
                'gross_profit' => $grossProfit,
                'expenses' => $totalExpenses,
                'net_profit' => $netProfit,
            ],
        ]);
    }

    public function exportComprehensiveCsv(Request $request): StreamedResponse
    {
        $dateFrom = $request->query('date_from', now()->startOfMonth()->toDateString());
        $dateTo = $request->query('date_to', now()->toDateString());
        $filename = "comprehensive_report_{$dateFrom}_to_{$dateTo}.csv";

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename={$filename}",
        ];

        return response()->stream(function () use ($dateFrom, $dateTo) {
            $out = fopen('php://output', 'w');
            
            // Header
            fputcsv($out, ['Comprehensive Report', "From: {$dateFrom}", "To: {$dateTo}"]);
            fputcsv($out, []);

            // Sales Transactions
            fputcsv($out, ['=== SALES TRANSACTIONS ===']);
            fputcsv($out, ['Description', 'Amount', 'Percentage']);
            
            $sales = SalesTransaction::with('member:id,full_name')
                ->whereBetween(DB::raw('DATE(date_time)'), [$dateFrom, $dateTo])
                ->get();
            
            $totalSales = $sales->sum('total_amount');
            
            foreach ($sales as $sale) {
                $percentage = $totalSales > 0 ? ($sale->total_amount / $totalSales * 100) : 0;
                $description = $sale->date_time->format('Y-m-d') . ' - ' . $sale->payment_type . ' - ' . ($sale->member ? $sale->member->full_name : 'Guest');
                fputcsv($out, [
                    $description,
                    number_format($sale->total_amount, 0, ',', '.'),
                    number_format($percentage, 2, '.', '') . '%'
                ]);
            }
            
            fputcsv($out, ['Total Sales', number_format($totalSales, 0, ',', '.'), '100%']);
            fputcsv($out, []);

            // Expenses
            fputcsv($out, ['=== EXPENSES ===']);
            fputcsv($out, ['Description', 'Amount', 'Percentage']);
            
            $expenses = Expense::whereBetween('date', [$dateFrom, $dateTo])
                ->orderBy('date')
                ->get();
            
            $totalExpenses = $expenses->sum('amount');
            
            foreach ($expenses as $expense) {
                $percentage = $totalExpenses > 0 ? ($expense->amount / $totalExpenses * 100) : 0;
                $dateFormatted = $expense->date instanceof \Carbon\Carbon ? $expense->date->format('Y-m-d') : $expense->date;
                $description = $dateFormatted . ' - ' . $expense->description;
                fputcsv($out, [
                    $description,
                    number_format($expense->amount, 0, ',', '.'),
                    number_format($percentage, 2, '.', '') . '%'
                ]);
            }
            
            if ($totalExpenses > 0) {
                fputcsv($out, ['Total Expenses', number_format($totalExpenses, 0, ',', '.'), '100%']);
            }
            fputcsv($out, []);

            // Ads
            fputcsv($out, ['=== ADVERTISING EXPENSES ===']);
            fputcsv($out, ['Description', 'Amount', 'Percentage']);
            
            $ads = Ad::whereBetween('date', [$dateFrom, $dateTo])
                ->orderBy('date')
                ->get();
            
            $totalAds = $ads->sum('amount');
            
            foreach ($ads as $ad) {
                $percentage = $totalAds > 0 ? ($ad->amount / $totalAds * 100) : 0;
                $dateFormatted = $ad->date instanceof \Carbon\Carbon ? $ad->date->format('Y-m-d') : $ad->date;
                $description = $dateFormatted . ' - ' . $ad->type . ' - ' . $ad->description . ' (' . ($ad->vendor ?? 'N/A') . ')';
                fputcsv($out, [
                    $description,
                    number_format($ad->amount, 0, ',', '.'),
                    number_format($percentage, 2, '.', '') . '%'
                ]);
            }
            
            if ($totalAds > 0) {
                fputcsv($out, ['Total Advertising', number_format($totalAds, 0, ',', '.'), '100%']);
            }
            fputcsv($out, []);

            // Facilities
            fputcsv($out, ['=== FACILITY INCOME ===']);
            fputcsv($out, ['Description', 'Amount', 'Percentage']);
            
            $facilities = Facility::whereBetween('date', [$dateFrom, $dateTo])
                ->orderBy('date')
                ->get();
            
            $totalFacilities = $facilities->sum('amount');
            
            foreach ($facilities as $facility) {
                $percentage = $totalFacilities > 0 ? ($facility->amount / $totalFacilities * 100) : 0;
                $dateFormatted = $facility->date instanceof \Carbon\Carbon ? $facility->date->format('Y-m-d') : $facility->date;
                $description = $dateFormatted . ' - ' . $facility->type . ' - ' . $facility->description . ' (' . ($facility->customer_name ?? 'N/A') . ')';
                fputcsv($out, [
                    $description,
                    number_format($facility->amount, 0, ',', '.'),
                    number_format($percentage, 2, '.', '') . '%'
                ]);
            }
            
            if ($totalFacilities > 0) {
                fputcsv($out, ['Total Facility Income', number_format($totalFacilities, 0, ',', '.'), '100%']);
            }
            fputcsv($out, []);
            
            // Summary
            fputcsv($out, ['=== SUMMARY ===']);
            fputcsv($out, ['Category', 'Amount', 'Percentage']);
            
            $grandTotal = $totalSales + $totalFacilities - $totalExpenses - $totalAds;
            $totalIncome = $totalSales + $totalFacilities;
            $totalOutcome = $totalExpenses + $totalAds;
            
            fputcsv($out, ['Total Income (Sales + Facilities)', number_format($totalIncome, 0, ',', '.'), '']);
            fputcsv($out, ['Total Expenses (Operational + Ads)', number_format($totalOutcome, 0, ',', '.'), '']);
            fputcsv($out, ['Net Profit/Loss', number_format($grandTotal, 0, ',', '.'), '']);
            
            fclose($out);
        }, 200, $headers);
    }

    public function attendance(Request $request)
    {
        $startDate = $request->query('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->query('end_date', now()->toDateString());

        // Statistics
        $totalAttendances = Attendance::dateRange($startDate, $endDate)->count();
        $memberAttendances = Attendance::dateRange($startDate, $endDate)->memberType()->count();
        $dailyAttendances = Attendance::dateRange($startDate, $endDate)->dailyType()->count();
        
        $days = Carbon::parse($startDate)->diffInDays(Carbon::parse($endDate)) + 1;
        $averagePerDay = $days > 0 ? round($totalAttendances / $days, 1) : 0;

        // Daily breakdown
        $dailyData = Attendance::selectRaw('
                date,
                COUNT(*) as count
            ')
            ->dateRange($startDate, $endDate)
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Top members by visit count
        $topMembers = Attendance::select('member_id',
                DB::raw('COUNT(*) as visit_count'))
            ->memberType()
            ->whereNotNull('member_id')
            ->dateRange($startDate, $endDate)
            ->with('member:id,full_name')
            ->groupBy('member_id')
            ->orderByDesc('visit_count')
            ->limit(10)
            ->get()
            ->map(function ($item) {
                return [
                    'member_id' => $item->member_id,
                    'member_name' => $item->member->full_name ?? '-',
                    'visit_count' => $item->visit_count,
                ];
            });

        return Inertia::render('Reports/Attendance', [
            'stats' => [
                'total' => $totalAttendances,
                'members' => $memberAttendances,
                'daily' => $dailyAttendances,
                'average_per_day' => $averagePerDay,
            ],
            'dailyData' => $dailyData,
            'topMembers' => $topMembers,
            'filters' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ]
        ]);
    }
}
