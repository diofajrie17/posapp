<?php

namespace App\Http\Controllers;

use App\Models\SalesTransaction;
use App\Models\SalesItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\StreamedResponse;

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
}
