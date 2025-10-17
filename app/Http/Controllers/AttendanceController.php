<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Member;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:attendance.checkin')->only(['checkin', 'store', 'checkout']);
        $this->middleware('permission:attendance.view')->only(['index', 'history']);
    }

    public function checkin()
    {
        // Get today's attendances
        $todayAttendances = Attendance::with('member')
            ->today()
            ->latest('check_in_time')
            ->get();

        return Inertia::render('Attendance/CheckIn', [
            'todayAttendances' => $todayAttendances
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:member,daily',
            'member_id' => 'required_if:type,member|nullable|exists:members,id',
            'customer_name' => 'required_if:type,daily|nullable|string|max:255',
            'customer_phone' => 'required_if:type,daily|nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $data = [
            'type' => $validated['type'],
            'check_in_time' => now(),
            'date' => now()->toDateString(),
            'notes' => $validated['notes'] ?? null,
        ];

        if ($validated['type'] === 'member') {
            $data['member_id'] = $validated['member_id'];
            
            // Load member to check expiration
            $member = Member::find($validated['member_id']);
            if ($member && $member->isExpired()) {
                return back()->with('warning', "Check-in berhasil. Perhatian: Membership {$member->full_name} telah expired!");
            }
        } else {
            $data['customer_name'] = $validated['customer_name'];
            $data['customer_phone'] = $validated['customer_phone'];
        }

        Attendance::create($data);

        return back()->with('message', 'Check-in berhasil dicatat');
    }

    public function checkout(Request $request, Attendance $attendance)
    {
        if ($attendance->check_out_time) {
            return back()->with('error', 'Attendance ini sudah check-out');
        }

        $attendance->update([
            'check_out_time' => now()
        ]);

        return back()->with('message', 'Check-out berhasil dicatat');
    }

    public function index(Request $request)
    {
        $query = Attendance::with('member');

        // Filter by date range
        if ($request->start_date && $request->end_date) {
            $query->dateRange($request->start_date, $request->end_date);
        } else {
            // Default to current month
            $query->whereMonth('date', now()->month)
                  ->whereYear('date', now()->year);
        }

        // Filter by type
        if ($request->type) {
            $query->where('type', $request->type);
        }

        // Search by name/phone
        if ($request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('customer_name', 'like', "%{$search}%")
                  ->orWhere('customer_phone', 'like', "%{$search}%")
                  ->orWhereHas('member', function($mq) use ($search) {
                      $mq->where('full_name', 'like', "%{$search}%")
                         ->orWhere('phone', 'like', "%{$search}%");
                  });
            });
        }

        $attendances = $query->latest('check_in_time')->paginate(50);

        // Statistics
        $stats = [
            'total' => Attendance::dateRange(
                $request->start_date ?? now()->startOfMonth(),
                $request->end_date ?? now()->endOfMonth()
            )->count(),
            'members' => Attendance::dateRange(
                $request->start_date ?? now()->startOfMonth(),
                $request->end_date ?? now()->endOfMonth()
            )->memberType()->count(),
            'daily' => Attendance::dateRange(
                $request->start_date ?? now()->startOfMonth(),
                $request->end_date ?? now()->endOfMonth()
            )->dailyType()->count(),
        ];

        return Inertia::render('Attendance/Index', [
            'attendances' => $attendances,
            'stats' => $stats,
            'filters' => $request->only(['start_date', 'end_date', 'type', 'search'])
        ]);
    }

    public function searchMembers(Request $request)
    {
        $search = $request->get('q', '');
        
        $members = Member::where('is_active', true)
            ->search($search)
            ->limit(10)
            ->get(['id', 'full_name', 'phone', 'membership_end']);

        return response()->json($members);
    }
}

