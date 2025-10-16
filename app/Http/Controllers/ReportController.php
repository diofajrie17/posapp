<?php

namespace App\Http\Controllers;

use App\Models\SalesTransaction;
use App\Models\SalesItem;
use App\Models\Expense;
use App\Models\Ad;
use App\Models\Facility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function __construct()
{
    $this->middleware('permission:products.view')->only(['index','show']);
    $this->middleware('permission:products.create')->only(['create','store']);
    $this->middleware('permission:products.update')->only(['edit','update']);
    $this->middleware('permission:products.delete')->only(['destroy']);
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

        // Daftar transaksi (untuk tabel)
        $transactions = SalesTransaction::with('member:id,full_name')
            ->whereDate('date_time', $date)
            ->orderBy('date_time','desc')
            ->get(['id','member_id','is_daily_guest','payment_type','total_amount','date_time']);


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
                SUM(total_amount) as gross_total,
                SUM(subtotal_amount) as subtotal_total,
                SUM(discount_amount) as discount_total
            ')
            ->whereBetween(DB::raw('DATE(date_time)'), [$dateFrom, $dateTo])
            ->first();

        $salesByDate = SalesTransaction::selectRaw('
                DATE(date_time) as date,
                COUNT(*) as trx_count,
                SUM(total_amount) as total
            ')
            ->whereBetween(DB::raw('DATE(date_time)'), [$dateFrom, $dateTo])
            ->groupBy(DB::raw('DATE(date_time)'))
            ->orderBy('date')
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
                SUM(amount) as total
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
                SUM(amount) as total
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
                SUM(amount) as total
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

        // Top Products
        $topProducts = SalesItem::select('product_id',
                DB::raw('SUM(quantity) as qty'),
                DB::raw('SUM(quantity * price_each) as gross'))
            ->whereHas('transaction', fn($q) => $q->whereBetween(DB::raw('DATE(date_time)'), [$dateFrom, $dateTo]))
            ->with('product:id,name,unit')
            ->groupBy('product_id')
            ->orderByDesc('gross')
            ->limit(10)
            ->get();

        // Calculate totals for profit/loss
        $totalIncome = ($salesSummary->gross_total ?? 0) + ($facilitySummary->total ?? 0);
        $totalExpenses = ($expensesSummary->total ?? 0) + ($adsSummary->total ?? 0);
        $netProfit = $totalIncome - $totalExpenses;

        return Inertia::render('Reports/Comprehensive', [
            'filters' => compact('dateFrom', 'dateTo'),
            'sales' => [
                'summary' => $salesSummary,
                'by_date' => $salesByDate,
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
            'top_products' => $topProducts,
            'totals' => [
                'income' => $totalIncome,
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
            fputcsv($out, ['Date', 'Transaction ID', 'Payment Type', 'Amount', 'Member']);
            
            $sales = SalesTransaction::with('member:id,full_name')
                ->whereBetween(DB::raw('DATE(date_time)'), [$dateFrom, $dateTo])
                ->orderBy('date_time')
                ->get();
            
            foreach ($sales as $sale) {
                fputcsv($out, [
                    $sale->date_time->format('Y-m-d'),
                    $sale->id,
                    $sale->payment_type,
                    $sale->total_amount,
                    $sale->member ? $sale->member->full_name : 'Guest'
                ]);
            }
            
            fputcsv($out, []);

            // Expenses
            fputcsv($out, ['=== EXPENSES ===']);
            fputcsv($out, ['Date', 'Description', 'Amount']);
            
            $expenses = Expense::whereBetween('date', [$dateFrom, $dateTo])
                ->orderBy('date')
                ->get();
            
            foreach ($expenses as $expense) {
                fputcsv($out, [
                    $expense->date,
                    $expense->description,
                    $expense->amount
                ]);
            }

            fputcsv($out, []);

            // Ads
            fputcsv($out, ['=== ADVERTISING EXPENSES ===']);
            fputcsv($out, ['Date', 'Type', 'Description', 'Vendor', 'Amount']);
            
            $ads = Ad::whereBetween('date', [$dateFrom, $dateTo])
                ->orderBy('date')
                ->get();
            
            foreach ($ads as $ad) {
                fputcsv($out, [
                    $ad->date,
                    $ad->type,
                    $ad->description,
                    $ad->vendor,
                    $ad->amount
                ]);
            }

            fputcsv($out, []);

            // Facilities
            fputcsv($out, ['=== FACILITY INCOME ===']);
            fputcsv($out, ['Date', 'Type', 'Description', 'Customer', 'Duration', 'Amount']);
            
            $facilities = Facility::whereBetween('date', [$dateFrom, $dateTo])
                ->orderBy('date')
                ->get();
            
            foreach ($facilities as $facility) {
                fputcsv($out, [
                    $facility->date,
                    $facility->type,
                    $facility->description,
                    $facility->customer_name,
                    $facility->duration,
                    $facility->amount
                ]);
            }
            
            fclose($out);
        }, 200, $headers);
    }
}
