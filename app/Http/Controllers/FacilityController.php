<?php

namespace App\Http\Controllers;

use App\Models\Facility;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class FacilityController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:products.view')->only(['index', 'show']);
        $this->middleware('permission:products.create')->only(['create', 'store']);
        $this->middleware('permission:products.update')->only(['edit', 'update']);
        $this->middleware('permission:products.delete')->only(['destroy']);
    }

    public function index(Request $request)
    {
        $dateFrom = $request->query('date_from');
        $dateTo = $request->query('date_to');
        $type = $request->query('type');

        $query = Facility::orderByDesc('date')->orderByDesc('id');

        if ($dateFrom) {
            $query->whereDate('date', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->whereDate('date', '<=', $dateTo);
        }
        if ($type) {
            $query->where('type', $type);
        }

        $facilities = $query->paginate(20)->withQueryString();

        // Summary statistics
        $totalIncome = Facility::when($dateFrom, fn($q) => $q->whereDate('date', '>=', $dateFrom))
            ->when($dateTo, fn($q) => $q->whereDate('date', '<=', $dateTo))
            ->when($type, fn($q) => $q->where('type', $type))
            ->sum('amount');

        $facilityTypes = Facility::select('type')
            ->selectRaw('COUNT(*) as count, SUM(amount) as total')
            ->groupBy('type')
            ->orderByDesc('total')
            ->get();

        return Inertia::render('Facilities/Index', [
            'facilities' => $facilities,
            'filters' => compact('dateFrom', 'dateTo', 'type'),
            'summary' => [
                'total_income' => $totalIncome,
                'facility_types' => $facilityTypes,
            ],
            'can' => [
                'create' => auth()->user()->can('products.create'),
                'update' => auth()->user()->can('products.update'),
                'delete' => auth()->user()->can('products.delete'),
            ],
        ]);
    }

    public function create()
    {
        return Inertia::render('Facilities/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'amount' => 'required|numeric|min:0',
            'description' => 'required|string|max:255',
            'type' => 'required|string|max:100',
            'customer_name' => 'nullable|string|max:255',
            'duration' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:500',
        ]);

        Facility::create($validated);

        return redirect()->route('facilities.index')
            ->with('success', 'Facility income added successfully.');
    }

    public function show(Facility $facility)
    {
        return Inertia::render('Facilities/Show', [
            'facility' => $facility,
        ]);
    }

    public function edit(Facility $facility)
    {
        return Inertia::render('Facilities/Edit', [
            'facility' => $facility,
        ]);
    }

    public function update(Request $request, Facility $facility)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'amount' => 'required|numeric|min:0',
            'description' => 'required|string|max:255',
            'type' => 'required|string|max:100',
            'customer_name' => 'nullable|string|max:255',
            'duration' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:500',
        ]);

        $facility->update($validated);

        return redirect()->route('facilities.index')
            ->with('success', 'Facility income updated successfully.');
    }

    public function destroy(Facility $facility)
    {
        $facility->delete();

        return redirect()->route('facilities.index')
            ->with('success', 'Facility income deleted successfully.');
    }

    public function export(Request $request)
    {
        $dateFrom = $request->query('date_from');
        $dateTo = $request->query('date_to');
        $type = $request->query('type');

        $filename = 'facilities_export_' . now()->format('Y-m-d_H-i-s') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename={$filename}",
        ];

        return response()->stream(function () use ($dateFrom, $dateTo, $type) {
            $out = fopen('php://output', 'w');
            
            // Header
            fputcsv($out, ['Facility Income Export']);
            fputcsv($out, ['Generated at: ' . now()->format('Y-m-d H:i:s')]);
            if ($dateFrom) fputcsv($out, ['From Date: ' . $dateFrom]);
            if ($dateTo) fputcsv($out, ['To Date: ' . $dateTo]);
            if ($type) fputcsv($out, ['Type Filter: ' . $type]);
            fputcsv($out, []);
            
            // Table headers
            fputcsv($out, ['Date', 'Type', 'Description', 'Customer Name', 'Duration (hours)', 'Amount', 'Notes']);
            
            // Query data
            $query = Facility::orderBy('date', 'desc');
            
            if ($dateFrom) {
                $query->whereDate('date', '>=', $dateFrom);
            }
            if ($dateTo) {
                $query->whereDate('date', '<=', $dateTo);
            }
            if ($type) {
                $query->where('type', $type);
            }
            
            $facilities = $query->get();
            
            foreach ($facilities as $facility) {
                fputcsv($out, [
                    $facility->date,
                    $facility->type,
                    $facility->description,
                    $facility->customer_name,
                    $facility->duration,
                    $facility->amount,
                    $facility->notes
                ]);
            }
            
            // Summary
            fputcsv($out, []);
            fputcsv($out, ['Total Records', count($facilities)]);
            fputcsv($out, ['Total Amount', $facilities->sum('amount')]);
            
            fclose($out);
        }, 200, $headers);
    }
}