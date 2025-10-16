<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ExpenseController extends Controller
{
    public function index()
    {
        $expenses = Expense::latest()->get();
        return Inertia::render('Expenses/Index', [
            'expenses' => $expenses,
        ]);
    }

    public function create()
    {
        return Inertia::render('Expenses/Create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric',
            'date' => 'required|date',
        ]);

        Expense::create($request->all());

        return redirect()->route('expenses.index')->with('success', 'Expense berhasil ditambahkan');
    }

    public function edit(Expense $expense)
    {
        return Inertia::render('Expenses/Edit', [
            'expense' => $expense
        ]);
    }

    public function update(Request $request, Expense $expense)
    {
        $request->validate([
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric',
            'date' => 'required|date',
        ]);

        $expense->update($request->all());

        return redirect()->route('expenses.index')->with('success', 'Expense berhasil diupdate');
    }

    public function destroy(Expense $expense)
    {
        $expense->delete();
        return redirect()->route('expenses.index')->with('success', 'Expense berhasil dihapus');
    }

    public function export(Request $request)
    {
        $dateFrom = $request->query('date_from');
        $dateTo = $request->query('date_to');

        $filename = 'expenses_export_' . now()->format('Y-m-d_H-i-s') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename={$filename}",
        ];

        return response()->stream(function () use ($dateFrom, $dateTo) {
            $out = fopen('php://output', 'w');
            
            // Header
            fputcsv($out, ['Regular Expenses Export']);
            fputcsv($out, ['Generated at: ' . now()->format('Y-m-d H:i:s')]);
            if ($dateFrom) fputcsv($out, ['From Date: ' . $dateFrom]);
            if ($dateTo) fputcsv($out, ['To Date: ' . $dateTo]);
            fputcsv($out, []);
            
            // Table headers
            fputcsv($out, ['Date', 'Description', 'Amount']);
            
            // Query data
            $query = Expense::orderBy('date', 'desc');
            
            if ($dateFrom) {
                $query->whereDate('date', '>=', $dateFrom);
            }
            if ($dateTo) {
                $query->whereDate('date', '<=', $dateTo);
            }
            
            $expenses = $query->get();
            
            foreach ($expenses as $expense) {
                fputcsv($out, [
                    $expense->date,
                    $expense->description,
                    $expense->amount
                ]);
            }
            
            // Summary
            fputcsv($out, []);
            fputcsv($out, ['Total Records', count($expenses)]);
            fputcsv($out, ['Total Amount', $expenses->sum('amount')]);
            
            fclose($out);
        }, 200, $headers);
    }
}
