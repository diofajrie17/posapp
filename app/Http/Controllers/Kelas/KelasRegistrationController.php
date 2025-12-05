<?php

namespace App\Http\Controllers\Kelas;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\KelasRegistration;
use App\Models\KelasMember;
use Illuminate\Http\Request;
use Inertia\Inertia;

class KelasRegistrationController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:kelas.registration.manage');
    }

    public function index(Request $request)
    {
        $kelasId = $request->query('kelas_id');
        $q = KelasRegistration::with(['kelas', 'member'])->orderByDesc('created_at');
        if ($kelasId) $q->where('kelas_id', $kelasId);
        return Inertia::render('Kelas/Registrations', [
            'registrations' => $q->get(),
            'kelas' => Kelas::orderBy('name')->get(['id','name']),
            'members' => KelasMember::orderBy('full_name')->get(['id','full_name']),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'kelas_id' => 'required|exists:kelas,id',
            'member_id' => 'required|exists:kelas_members,id',
            'registration_date' => 'nullable|date',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'notes' => 'nullable|string',
            'amount' => 'nullable|numeric|min:0',
            'payment_method' => 'nullable|string|in:Cash,QR,Transfer',
        ]);
        $data['created_by'] = auth()->id();
        $data['status'] = 'registered';
        
        // Auto-calculate end_date if start_date is provided and end_date is not
        if (isset($data['start_date']) && !isset($data['end_date'])) {
            $data['end_date'] = \Carbon\Carbon::parse($data['start_date'])->addDays(30)->toDateString();
        }
        
        $registration = KelasRegistration::create($data);
        
        // Create payment record if amount is provided
        if (isset($data['amount']) && $data['amount'] > 0) {
            \App\Models\KelasPayment::create([
                'kelas_id' => $data['kelas_id'],
                'member_id' => $data['member_id'],
                'registration_id' => $registration->id,
                'payment_date' => $data['registration_date'] ?? now()->toDateString(),
                'amount' => $data['amount'],
                'payment_method' => $data['payment_method'] ?? 'Cash',
                'notes' => $data['notes'] ?? 'Pembayaran registrasi kelas',
                'created_by' => auth()->id(),
            ]);
        }
        
        return back()->with('message', 'Member registered to kelas');
    }

    public function destroy(KelasRegistration $registration)
    {
        $registration->delete();
        return back()->with('message', 'Registration removed');
    }
}


