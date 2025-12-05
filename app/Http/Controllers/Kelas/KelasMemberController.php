<?php

namespace App\Http\Controllers\Kelas;

use App\Http\Controllers\Controller;
use App\Models\KelasMember;
use Illuminate\Http\Request;
use Inertia\Inertia;

class KelasMemberController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:kelas.registration.manage');
    }

    public function index(Request $request)
    {
        $query = KelasMember::query();

        // Search
        if ($request->search) {
            $query->search($request->search);
        }

        // Filter by membership type
        if ($request->type) {
            $query->where('membership_type', $request->type);
        }

        // Filter by status
        if ($request->status === 'active') {
            $query->active();
        } elseif ($request->status === 'expired') {
            $query->expired();
        }

        $members = $query->orderBy('full_name')->get();

        // Statistics
        $stats = [
            'total' => KelasMember::count(),
            'active' => KelasMember::active()->count(),
            'monthly' => KelasMember::monthly()->count(),
            'daily' => KelasMember::daily()->count(),
        ];

        return Inertia::render('Kelas/Members/Index', [
            'members' => $members,
            'stats' => $stats,
            'filters' => $request->only(['search', 'type', 'status']) ?? []
        ]);
    }

    public function create()
    {
        return Inertia::render('Kelas/Members/Form', [
            'mode' => 'create',
            'kelas' => \App\Models\Kelas::orderBy('name')->get(['id','name','monthly_price'])
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'full_name' => 'required|string|max:255',
            'phone' => 'required|string|max:50',
            'membership_type' => 'required|in:monthly',
            'membership_start' => 'required|date',
            'membership_end' => 'nullable|date|after_or_equal:membership_start',
            'is_active' => 'nullable|boolean',
            'notes' => 'nullable|string',
            'kelas_id' => 'nullable|exists:kelas,id',
            'amount' => 'nullable|numeric|min:0',
            'payment_method' => 'nullable|string|in:Cash,QR,Transfer',
        ]);

        // Auto-calculate end_date if start_date is provided and end_date is not
        if (isset($data['membership_start']) && !isset($data['membership_end'])) {
            $data['membership_end'] = \Carbon\Carbon::parse($data['membership_start'])->addDays(30)->toDateString();
        }

        $data['is_active'] = $data['is_active'] ?? true;
        $member = KelasMember::create($data);

        // Auto-create payment record for monthly members to track in income reports
        // This ensures member monthly registrations appear in income reports
        if (isset($data['kelas_id'])) {
            \App\Models\KelasPayment::create([
                'kelas_id' => $data['kelas_id'],
                'member_id' => $member->id,
                'payment_date' => $data['membership_start'],
                'amount' => $data['amount'] ?? 0,
                'payment_method' => $data['payment_method'] ?? 'Cash',
                'notes' => 'Pembayaran registrasi member bulanan - ' . $member->full_name,
                'created_by' => auth()->id(),
            ]);
        }

        return redirect()->route('kelas.members.index')->with('message', 'Member created successfully');
    }

    public function show(KelasMember $member)
    {
        $member->load(['registrations.kelas', 'kelas']);
        return Inertia::render('Kelas/Members/Form', [
            'mode' => 'show',
            'member' => $member,
            'kelas' => \App\Models\Kelas::orderBy('name')->get(['id','name','monthly_price'])
        ]);
    }

    public function edit(KelasMember $member)
    {
        $member->load('kelas');
        return Inertia::render('Kelas/Members/Form', [
            'mode' => 'edit',
            'member' => $member,
            'kelas' => \App\Models\Kelas::orderBy('name')->get(['id','name','monthly_price'])
        ]);
    }

    public function update(Request $request, KelasMember $member)
    {
        $data = $request->validate([
            'full_name' => 'required|string|max:255',
            'phone' => 'required|string|max:50',
            'membership_type' => 'required|in:monthly',
            'membership_start' => 'required|date',
            'membership_end' => 'nullable|date|after_or_equal:membership_start',
            'is_active' => 'nullable|boolean',
            'notes' => 'nullable|string',
            'kelas_id' => 'nullable|exists:kelas,id',
            'amount' => 'nullable|numeric|min:0',
            'payment_method' => 'nullable|string|in:Cash,QR,Transfer',
        ]);

        // Auto-calculate end_date if start_date is provided and end_date is not
        if (isset($data['membership_start']) && !isset($data['membership_end'])) {
            $data['membership_end'] = \Carbon\Carbon::parse($data['membership_start'])->addDays(30)->toDateString();
        }

        $member->update($data);

        // Update payment record if kelas_id or amount changed
        if (isset($data['kelas_id'])) {
            $payment = \App\Models\KelasPayment::where('member_id', $member->id)
                ->where('registration_id', null)
                ->first();

            if ($payment) {
                $payment->update([
                    'kelas_id' => $data['kelas_id'],
                    'amount' => $data['amount'] ?? 0,
                    'payment_method' => $data['payment_method'] ?? 'Cash',
                    'notes' => 'Pembayaran registrasi member bulanan - ' . $data['full_name'],
                ]);
            } else {
                // Create new payment record if doesn't exist
                \App\Models\KelasPayment::create([
                    'kelas_id' => $data['kelas_id'],
                    'member_id' => $member->id,
                    'payment_date' => $data['membership_start'],
                    'amount' => $data['amount'] ?? 0,
                    'payment_method' => $data['payment_method'] ?? 'Cash',
                    'notes' => 'Pembayaran registrasi member bulanan - ' . $data['full_name'],
                    'created_by' => auth()->id(),
                ]);
            }
        }

        return redirect()->route('kelas.members.index')->with('message', 'Member updated successfully');
    }

    public function destroy(KelasMember $member)
    {
        $member->delete();
        return redirect()->route('kelas.members.index')->with('message', 'Member deleted successfully');
    }
}

