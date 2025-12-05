<?php

namespace App\Http\Controllers\Kelas;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\KelasExpense;
use Illuminate\Http\Request;
use Inertia\Inertia;

class KelasExpenseController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:kelas.expense.manage');
    }

    public function index(Request $request)
    {
        $kelasId = $request->query('kelas_id');
        $q = KelasExpense::with('kelas')->orderByDesc('expense_date');
        if ($kelasId) $q->where('kelas_id', $kelasId);
        return Inertia::render('Kelas/Expenses', [
            'expenses' => $q->get(),
            'kelas' => Kelas::orderBy('name')->get(['id','name']),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'kelas_id' => 'required|exists:kelas,id',
            'expense_date' => 'required|date',
            'category' => 'nullable|string|max:100',
            'description' => 'nullable|string|max:255',
            'amount' => 'required|numeric|min:0',
        ]);
        $data['created_by'] = auth()->id();
        KelasExpense::create($data);
        return back()->with('message', 'Expense recorded');
    }
}


