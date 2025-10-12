<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use Illuminate\Http\Request;
use Inertia\Inertia;

class UnitController extends Controller
{
    public function index()
    {
        $units = Unit::withCount('products')
            ->orderBy('name')
            ->get();

        return Inertia::render('Units/Index', [
            'units' => $units
        ]);
    }

    public function create()
    {
        $baseUnits = Unit::where('is_base_unit', true)->get();
        
        return Inertia::render('Units/Create', [
            'baseUnits' => $baseUnits
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:units,name',
            'symbol' => 'nullable|string|max:10',
            'is_base_unit' => 'required|boolean'
        ]);

        Unit::create($validated);

        return redirect()->route('units.index')
            ->with('success', 'Unit berhasil ditambahkan!');
    }

    public function edit(Unit $unit)
    {
        $baseUnits = Unit::where('is_base_unit', true)
            ->where('id', '!=', $unit->id)
            ->get();
            
        return Inertia::render('Units/Edit', [
            'unit' => $unit,
            'baseUnits' => $baseUnits
        ]);
    }

    public function update(Request $request, Unit $unit)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:units,name,' . $unit->id,
            'symbol' => 'nullable|string|max:10',
            'is_base_unit' => 'required|boolean'
        ]);

        $unit->update($validated);

        return redirect()->route('units.index')
            ->with('success', 'Unit berhasil diperbarui!');
    }

    public function destroy(Unit $unit)
    {
        // Check if unit is being used by products
        if ($unit->products()->exists() || $unit->baseUnitProducts()->exists()) {
            return back()->with('error', 'Unit tidak dapat dihapus karena masih digunakan oleh produk!');
        }

        $unit->delete();

        return redirect()->route('units.index')->with('success', 'Unit berhasil dihapus!');
    }

    // API endpoint for getting units for dropdowns
    public function getUnitsForSelect()
    {
        $units = Unit::active()
            ->orderBy('name')
            ->get()
            ->map(function ($unit) {
                return [
                    'id' => $unit->id,
                    'name' => $unit->name,
                    'symbol' => $unit->symbol,
                ];
            });

        return response()->json($units);
    }
}
