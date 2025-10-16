<?php

namespace App\Http\Controllers;

use App\Models\Ad;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class AdController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:expenses.view')->only(['index', 'show']);
        $this->middleware('permission:expenses.create')->only(['create', 'store']);
        $this->middleware('permission:expenses.update')->only(['edit', 'update']);
        $this->middleware('permission:expenses.delete')->only(['destroy']);
    }

    public function index(Request $request)
    {
        $dateFrom = $request->query('date_from');
        $dateTo = $request->query('date_to');
        $type = $request->query('type');

        $query = Ad::orderByDesc('date')->orderByDesc('id');

        if ($dateFrom) {
            $query->whereDate('date', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->whereDate('date', '<=', $dateTo);
        }
        if ($type) {
            $query->where('type', $type);
        }

        $ads = $query->paginate(20)->withQueryString();

        // Summary statistics
        $totalSpent = Ad::when($dateFrom, fn($q) => $q->whereDate('date', '>=', $dateFrom))
            ->when($dateTo, fn($q) => $q->whereDate('date', '<=', $dateTo))
            ->when($type, fn($q) => $q->where('type', $type))
            ->sum('amount');

        $adTypes = Ad::select('type')
            ->selectRaw('COUNT(*) as count, SUM(amount) as total')
            ->groupBy('type')
            ->orderByDesc('total')
            ->get();

        return Inertia::render('Ads/Index', [
            'ads' => $ads,
            'filters' => compact('dateFrom', 'dateTo', 'type'),
            'summary' => [
                'total_spent' => $totalSpent,
                'ad_types' => $adTypes,
            ],
            'can' => [
                'create' => auth()->user()->can('expenses.create'),
                'update' => auth()->user()->can('expenses.update'),
                'delete' => auth()->user()->can('expenses.delete'),
            ],
        ]);
    }

    public function create()
    {
        return Inertia::render('Ads/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'amount' => 'required|numeric|min:0',
            'description' => 'required|string|max:255',
            'type' => 'required|string|max:100',
            'vendor' => 'nullable|string|max:255',
        ]);

        Ad::create($validated);

        return redirect()->route('ads.index')
            ->with('success', 'Advertising expense added successfully.');
    }

    public function show(Ad $ad)
    {
        return Inertia::render('Ads/Show', [
            'ad' => $ad,
        ]);
    }

    public function edit(Ad $ad)
    {
        return Inertia::render('Ads/Edit', [
            'ad' => $ad,
        ]);
    }

    public function update(Request $request, Ad $ad)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'amount' => 'required|numeric|min:0',
            'description' => 'required|string|max:255',
            'type' => 'required|string|max:100',
            'vendor' => 'nullable|string|max:255',
        ]);

        $ad->update($validated);

        return redirect()->route('ads.index')
            ->with('success', 'Advertising expense updated successfully.');
    }

    public function destroy(Ad $ad)
    {
        $ad->delete();

        return redirect()->route('ads.index')
            ->with('success', 'Advertising expense deleted successfully.');
    }

    public function export(Request $request)
    {
        $dateFrom = $request->query('date_from');
        $dateTo = $request->query('date_to');
        $type = $request->query('type');

        $filename = 'ads_export_' . now()->format('Y-m-d_H-i-s') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename={$filename}",
        ];

        return response()->stream(function () use ($dateFrom, $dateTo, $type) {
            $out = fopen('php://output', 'w');
            
            // Header
            fputcsv($out, ['Advertising Expenses Export']);
            fputcsv($out, ['Generated at: ' . now()->format('Y-m-d H:i:s')]);
            if ($dateFrom) fputcsv($out, ['From Date: ' . $dateFrom]);
            if ($dateTo) fputcsv($out, ['To Date: ' . $dateTo]);
            if ($type) fputcsv($out, ['Type Filter: ' . $type]);
            fputcsv($out, []);
            
            // Table headers
            fputcsv($out, ['Date', 'Type', 'Description', 'Vendor', 'Amount']);
            
            // Query data
            $query = Ad::orderBy('date', 'desc');
            
            if ($dateFrom) {
                $query->whereDate('date', '>=', $dateFrom);
            }
            if ($dateTo) {
                $query->whereDate('date', '<=', $dateTo);
            }
            if ($type) {
                $query->where('type', $type);
            }
            
            $ads = $query->get();
            
            foreach ($ads as $ad) {
                fputcsv($out, [
                    $ad->date,
                    $ad->type,
                    $ad->description,
                    $ad->vendor,
                    $ad->amount
                ]);
            }
            
            // Summary
            fputcsv($out, []);
            fputcsv($out, ['Total Records', count($ads)]);
            fputcsv($out, ['Total Amount', $ads->sum('amount')]);
            
            fclose($out);
        }, 200, $headers);
    }
}