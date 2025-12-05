<?php

namespace App\Http\Controllers\Kelas;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use Illuminate\Http\Request;
use Inertia\Inertia;

class KelasController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:kelas.view')->only(['index','show']);
        $this->middleware('permission:kelas.manage')->only(['create','store','edit','update','destroy']);
    }

    public function index(Request $request)
    {
        $kelas = Kelas::orderByDesc('created_at')->get();
        
        // Add income statistics for each class
        $kelasWithIncome = $kelas->map(function($k) {
            $k->total_income = $k->getTotalIncome();
            $k->monthly_income = $k->getMonthlyIncome();
            $k->member_income = $k->getMonthlyMemberIncome();
            $k->direct_income = $k->getMonthlyDirectIncome();
            $k->attendance_count = $k->attendances()->count();
            return $k;
        });
        
        return Inertia::render('Kelas/Index', [
            'list' => $kelasWithIncome,
            'can' => [
                'manage' => auth()->user()->can('kelas.manage'),
            ],
        ]);
    }

    public function create()
    {
        return Inertia::render('Kelas/Form', [ 'mode' => 'create' ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'code' => 'required|string|max:50|unique:kelas,code',
            'name' => 'required|string|max:255',
            'instructor' => 'nullable|string|max:255',
            'capacity' => 'nullable|integer|min:0',
            'monthly_price' => 'required|numeric|min:0',
            'daily_price' => 'required|numeric|min:0',
            'status' => 'required|in:active,inactive,completed',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'description' => 'nullable|string',
        ]);
        $data['created_by'] = auth()->id();
        Kelas::create($data);
        return redirect()->route('kelas.index')->with('message', 'Kelas berhasil dibuat');
    }

    public function show(Kelas $kelas)
    {
        return Inertia::render('Kelas/Form', [
            'mode' => 'show',
            'kelas' => $kelas
        ]);
    }

    public function edit(Kelas $kelas)
    {
        return Inertia::render('Kelas/Form', [
            'mode' => 'edit',
            'kelas' => $kelas
        ]);
    }

    public function update(Request $request, Kelas $kelas)
    {
        $data = $request->validate([
            'code' => 'required|string|max:50|unique:kelas,code,' . $kelas->id,
            'name' => 'required|string|max:255',
            'instructor' => 'nullable|string|max:255',
            'capacity' => 'nullable|integer|min:0',
            'monthly_price' => 'required|numeric|min:0',
            'daily_price' => 'required|numeric|min:0',
            'status' => 'required|in:active,inactive,completed',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'description' => 'nullable|string',
        ]);
        $kelas->update($data);
        return redirect()->route('kelas.index')->with('message', 'Kelas berhasil diperbarui');
    }

    public function destroy(Kelas $kelas)
    {
        $kelas->delete();
        return redirect()->route('kelas.index')->with('message', 'Kelas berhasil dihapus');
    }
}
