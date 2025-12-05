<?php

namespace App\Http\Controllers\Kelas;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\KelasAttendance;
use App\Models\KelasMember;
use Illuminate\Http\Request;
use Inertia\Inertia;

class KelasCheckinController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:kelas.checkin')->only(['index', 'store', 'checkout']);
        $this->middleware('permission:kelas.attendance.view')->only(['history']);
    }

    public function index(Request $request)
    {
        $kelasId = $request->query('kelas_id');
        $selectedKelas = $kelasId ? Kelas::findOrFail($kelasId) : null;
        
        $todayAttendances = KelasAttendance::with(['kelas', 'member', 'creator'])
            ->where(function($q) use ($kelasId) {
                if ($kelasId) {
                    $q->where('kelas_id', $kelasId);
                }
            })
            ->today()
            ->latest('check_in_time')
            ->get()
            ->map(function($attendance) {
                // Ensure member relationship is loaded for display name
                if ($attendance->type === 'monthly' && $attendance->member_id && !$attendance->relationLoaded('member')) {
                    $attendance->load('member');
                }
                return $attendance;
            });

        // Get all classes for selector
        $kelasList = Kelas::where('status', 'active')
            ->orderBy('name')
            ->get(['id', 'name', 'monthly_price', 'daily_price']);

        return Inertia::render('Kelas/CheckIn', [
            'kelasList' => $kelasList,
            'selectedKelas' => $selectedKelas,
            'todayAttendances' => $todayAttendances,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kelas_id' => 'required|exists:kelas,id',
            'type' => 'required|in:monthly,daily',
            'member_id' => 'required_if:type,monthly|nullable|exists:kelas_members,id',
            'customer_name' => 'required_if:type,daily|nullable|string|max:255',
            'customer_phone' => 'required_if:type,daily|nullable|string|max:255',
            'payment_method' => 'required|in:Cash,QR,Transfer',
            'reference_number' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        // Additional validation for monthly type
        if ($validated['type'] === 'monthly' && !isset($validated['member_id'])) {
            return back()->withErrors(['member_id' => 'Member harus dipilih untuk check-in bulanan'])->withInput();
        }

        $kelas = Kelas::findOrFail($validated['kelas_id']);

        $data = [
            'kelas_id' => $validated['kelas_id'],
            'type' => $validated['type'],
            'check_in_time' => now(),
            'date' => now()->toDateString(),
            'payment_method' => $validated['payment_method'],
            'reference_number' => $validated['reference_number'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'created_by' => auth()->id(),
        ];

        if ($validated['type'] === 'monthly') {
            $data['member_id'] = $validated['member_id'];
            $data['amount'] = 0; // Monthly members don't pay at check-in, they already paid during registration
            
            // Load and validate member
            $member = KelasMember::findOrFail($validated['member_id']);
            
            // Check if member is active
            if (!$member->is_active) {
                return back()->withErrors(['member_id' => 'Member tidak aktif'])->withInput();
            }
            
            // Check if member is expired
            if ($member->isExpired()) {
                return back()->with('warning', "Check-in berhasil. Perhatian: Membership {$member->full_name} telah expired!");
            }
        } else {
            // Daily customer - creates income transaction at check-in
            $data['customer_name'] = $validated['customer_name'];
            $data['customer_phone'] = $validated['customer_phone'];
            $data['amount'] = $kelas->daily_price; // Daily members pay at check-in
        }

        KelasAttendance::create($data);

        return back()->with('message', 'Check-in berhasil dicatat');
    }

    public function checkout(Request $request, KelasAttendance $attendance)
    {
        if ($attendance->check_out_time) {
            return back()->with('error', 'Attendance ini sudah check-out');
        }

        $attendance->update([
            'check_out_time' => now()
        ]);

        return back()->with('message', 'Check-out berhasil dicatat');
    }

    public function history(Request $request)
    {
        $query = KelasAttendance::with(['kelas', 'member', 'creator']);

        // Filter by kelas
        if ($request->kelas_id) {
            $query->where('kelas_id', $request->kelas_id);
        }

        // Filter by date range
        if ($request->start_date && $request->end_date) {
            $query->whereBetween('date', [$request->start_date, $request->end_date]);
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
        $statsQuery = KelasAttendance::where(function($q) use ($request) {
            if ($request->kelas_id) {
                $q->where('kelas_id', $request->kelas_id);
            }
        })
        ->whereBetween('date', [
            $request->start_date ?? now()->startOfMonth(),
            $request->end_date ?? now()->endOfMonth()
        ]);

        $stats = [
            'total' => $statsQuery->count(),
            'monthly' => (clone $statsQuery)->monthly()->count(),
            'daily' => (clone $statsQuery)->daily()->count(),
            'total_amount' => $statsQuery->sum('amount'),
            'monthly_amount' => (clone $statsQuery)->monthly()->sum('amount'),
            'daily_amount' => (clone $statsQuery)->daily()->sum('amount'),
        ];

        // Get classes for filter
        $kelasList = Kelas::orderBy('name')->get(['id', 'name']);

        return Inertia::render('Kelas/AttendanceHistory', [
            'attendances' => $attendances,
            'stats' => $stats,
            'kelasList' => $kelasList,
            'filters' => $request->only(['kelas_id', 'start_date', 'end_date', 'type', 'search'])
        ]);
    }

    public function searchMembers(Request $request)
    {
        $search = $request->get('q', '');
        
        $query = KelasMember::where('membership_type', 'monthly')
            ->where(function($q) {
                $q->where('is_active', true)
                  ->where(function($q2) {
                      $q2->whereNull('membership_end')
                         ->orWhere('membership_end', '>=', now());
                  });
            });
        
        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }
        
        $members = $query->limit(20)
            ->orderBy('full_name')
            ->get(['id', 'full_name', 'phone', 'membership_end', 'is_active']);

        return response()->json($members);
    }
}

