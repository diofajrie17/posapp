<?php

namespace App\Http\Controllers;

use App\Models\Member;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MemberController extends Controller
{
    public function __construct()
{
    $this->middleware('permission:products.view')->only(['index','show']);
    $this->middleware('permission:products.create')->only(['create','store']);
    $this->middleware('permission:products.update')->only(['edit','update']);
    $this->middleware('permission:products.delete')->only(['destroy']);
}

    public function index(Request $request)
    {
        try {
            $query = Member::with('membershipPackage');

            // Search
            if ($request->search) {
                $query->search($request->search);
            }

            // Filter by status
            if ($request->status === 'active') {
                $query->active();
            } elseif ($request->status === 'expired') {
                $query->expired();
            } elseif ($request->status === 'expiring_soon') {
                $query->expiringSoon();
            }

            $members = $query->latest()->get();

            // Statistics
            $stats = [
                'total' => Member::count(),
                'active' => Member::active()->count(),
                'expired' => Member::expired()->count(),
                'expiring_soon' => Member::expiringSoon()->count(),
            ];

            return Inertia::render('Members/Index', [
                'members' => $members,
                'stats' => $stats,
                'filters' => $request->only(['search', 'status']) ?? []
            ]);
        } catch (\Exception $e) {
            \Log::error('Error loading members: ' . $e->getMessage());
            
            return Inertia::render('Members/Index', [
                'members' => [],
                'stats' => [
                    'total' => 0,
                    'active' => 0,
                    'expired' => 0,
                    'expiring_soon' => 0,
                ],
                'filters' => []
            ])->with('error', 'Terjadi kesalahan saat memuat data member.');
        }
    }

    public function create()
    {
        $packages = \App\Models\MembershipPackage::active()->get();
        
        return Inertia::render('Members/Create', [
            'packages' => $packages
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'phone' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'gender' => 'nullable|in:male,female',
            'membership_package_id' => 'nullable|exists:membership_packages,id',
            'membership_type' => 'required|in:bulanan,tahunan,harian',
            'membership_start' => 'nullable|date',
            'membership_end' => 'nullable|date|after_or_equal:membership_start',
            'is_active' => 'boolean',
            'notes' => 'nullable|string',
        ]);

        // Auto-calculate membership_end if package is selected and start date is provided
        if ($validated['membership_package_id'] && $validated['membership_start']) {
            $package = \App\Models\MembershipPackage::find($validated['membership_package_id']);
            $validated['membership_end'] = \Carbon\Carbon::parse($validated['membership_start'])
                ->addDays($package->duration_days)
                ->format('Y-m-d');
        }

        Member::create($validated);

        return redirect()->route('members.index')->with('message', 'Member berhasil ditambahkan');
    }

    public function edit(Member $member)
    {
        $packages = \App\Models\MembershipPackage::active()->get();
        
        return Inertia::render('Members/Edit', [
            'member' => $member->load('membershipPackage'),
            'packages' => $packages
        ]);
    }

    public function update(Request $request, Member $member)
    {
        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'phone' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'gender' => 'nullable|in:male,female',
            'membership_package_id' => 'nullable|exists:membership_packages,id',
            'membership_type' => 'required|in:bulanan,tahunan,harian',
            'membership_start' => 'nullable|date',
            'membership_end' => 'nullable|date|after_or_equal:membership_start',
            'is_active' => 'boolean',
            'notes' => 'nullable|string',
        ]);

        // Auto-calculate membership_end if package is selected and start date is provided
        if ($validated['membership_package_id'] && $validated['membership_start']) {
            $package = \App\Models\MembershipPackage::find($validated['membership_package_id']);
            $validated['membership_end'] = \Carbon\Carbon::parse($validated['membership_start'])
                ->addDays($package->duration_days)
                ->format('Y-m-d');
        }

        $member->update($validated);

        return redirect()->route('members.index')->with('message', 'Member berhasil diupdate');
    }

    public function destroy(Member $member)
    {
        $member->delete();

        return redirect()->route('members.index')->with('message', 'Member berhasil dihapus');
    }
}
