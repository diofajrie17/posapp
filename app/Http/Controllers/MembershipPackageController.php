<?php

namespace App\Http\Controllers;

use App\Models\MembershipPackage;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MembershipPackageController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:packages.view')->only(['index']);
        $this->middleware('permission:packages.create')->only(['create', 'store']);
        $this->middleware('permission:packages.update')->only(['edit', 'update']);
        $this->middleware('permission:packages.delete')->only(['destroy']);
    }

    public function index()
    {
        $packages = MembershipPackage::latest()->get();

        return Inertia::render('MembershipPackages/Index', [
            'packages' => $packages
        ]);
    }

    public function create()
    {
        return Inertia::render('MembershipPackages/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'duration_days' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        MembershipPackage::create($validated);

        return redirect()->route('packages.index')->with('message', 'Paket membership berhasil ditambahkan');
    }

    public function edit(MembershipPackage $package)
    {
        return Inertia::render('MembershipPackages/Edit', [
            'package' => $package
        ]);
    }

    public function update(Request $request, MembershipPackage $package)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'duration_days' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $package->update($validated);

        return redirect()->route('packages.index')->with('message', 'Paket membership berhasil diupdate');
    }

    public function destroy(MembershipPackage $package)
    {
        // Check if package has members
        if ($package->members()->count() > 0) {
            return back()->with('error', 'Tidak dapat menghapus paket yang masih digunakan oleh member');
        }

        $package->delete();

        return redirect()->route('packages.index')->with('message', 'Paket membership berhasil dihapus');
    }
}

