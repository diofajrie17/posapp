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
    $this->middleware('permission:products.update')->only(['edit','update','renew','processRenewal']);
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
        // Exclude daily plans (duration_days = 1) from member registration
        $packages = \App\Models\MembershipPackage::active()
            ->where('duration_days', '>', 1)
            ->get();
        
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
            'membership_start' => 'nullable|date',
            'membership_end' => 'nullable|date|after_or_equal:membership_start',
            'is_active' => 'boolean',
            'notes' => 'nullable|string',
            'payment_type' => 'required_with:membership_package_id|nullable|in:Cash,QR,Transfer',
        ]);

        // Auto-calculate membership_end if package is selected and start date is provided
        if ($validated['membership_package_id'] && $validated['membership_start']) {
            $package = \App\Models\MembershipPackage::find($validated['membership_package_id']);
            $validated['membership_end'] = \Carbon\Carbon::parse($validated['membership_start'])
                ->addDays($package->duration_days)
                ->format('Y-m-d');
        }

        $member = Member::create($validated);

        // Create membership payment record if package is selected
        if ($validated['membership_package_id']) {
            $package = \App\Models\MembershipPackage::find($validated['membership_package_id']);
            
            \App\Models\MembershipPayment::create([
                'member_id' => $member->id,
                'membership_package_id' => $validated['membership_package_id'],
                'type' => 'registration',
                'payment_type' => $validated['payment_type'] ?? 'Cash',
                'amount' => $package->price,
                'date' => $validated['membership_start'] ?? now()->toDateString(),
                'notes' => 'Pembayaran registrasi member',
            ]);
        }

        return redirect()->route('members.index')->with('message', 'Member berhasil ditambahkan');
    }

    public function edit(Member $member)
    {
        // Exclude daily plans (duration_days = 1) from member registration
        $packages = \App\Models\MembershipPackage::active()
            ->where('duration_days', '>', 1)
            ->get();
        
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

    public function renew(Member $member)
    {
        // Exclude daily plans (duration_days = 1) from member renewal
        $packages = \App\Models\MembershipPackage::active()
            ->where('duration_days', '>', 1)
            ->get();
        
        return Inertia::render('Members/Renew', [
            'member' => $member->load('membershipPackage'),
            'packages' => $packages
        ]);
    }

    public function processRenewal(Request $request, Member $member)
    {
        $validated = $request->validate([
            'membership_package_id' => 'required|exists:membership_packages,id',
            'payment_type' => 'required|in:Cash,QR,Transfer',
            'renewal_start_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        $package = \App\Models\MembershipPackage::find($validated['membership_package_id']);

        // Determine renewal start date
        // If provided, use it; otherwise, extend from current membership_end or today if expired
        $renewalStartDate = $validated['renewal_start_date'] 
            ? \Carbon\Carbon::parse($validated['renewal_start_date'])
            : null;

        // If no start date provided, calculate from current membership_end or today
        if (!$renewalStartDate) {
            if ($member->membership_end && $member->membership_end->isFuture()) {
                // Extend from current membership_end
                $renewalStartDate = $member->membership_end->copy()->addDay();
            } else {
                // Start from today (member is expired or has no end date)
                $renewalStartDate = now();
            }
        }

        // Calculate new membership_end date
        $newMembershipEnd = $renewalStartDate->copy()->addDays($package->duration_days);

        // Update member's membership information
        $member->update([
            'membership_package_id' => $validated['membership_package_id'],
            'membership_start' => $renewalStartDate->toDateString(),
            'membership_end' => $newMembershipEnd->toDateString(),
            'is_active' => true,
        ]);

        // Create membership payment record for renewal
        \App\Models\MembershipPayment::create([
            'member_id' => $member->id,
            'membership_package_id' => $validated['membership_package_id'],
            'customer_name' => $member->full_name,
            'customer_phone' => $member->phone,
            'type' => 'renewal',
            'payment_type' => $validated['payment_type'],
            'amount' => $package->price,
            'date' => $renewalStartDate->toDateString(),
            'notes' => $validated['notes'] ?? 'Pembayaran perpanjangan membership',
        ]);

        return redirect()->route('members.index')->with('message', 'Membership berhasil diperpanjang');
    }

    public function destroy(Member $member)
    {
        $member->delete();

        return redirect()->route('members.index')->with('message', 'Member berhasil dihapus');
    }
}
