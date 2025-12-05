<?php

namespace App\Http\Controllers\Kelas;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\KelasPayment;
use App\Models\KelasRegistration;
use App\Models\KelasMember;
use Illuminate\Http\Request;
use Inertia\Inertia;

class KelasPaymentController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:kelas.payment.manage');
    }

    public function index(Request $request)
    {
        $kelasId = $request->query('kelas_id');
        $q = KelasPayment::with(['kelas','member','registration'])->orderByDesc('payment_date');
        if ($kelasId) $q->where('kelas_id', $kelasId);
        return Inertia::render('Kelas/Payments', [
            'payments' => $q->get(),
            'kelas' => Kelas::orderBy('name')->get(['id','name']),
            'members' => KelasMember::orderBy('full_name')->get(['id','full_name']),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'kelas_id' => 'required|exists:kelas,id',
            'member_id' => 'nullable|exists:kelas_members,id',
            'registration_id' => 'nullable|exists:kelas_registrations,id',
            'payment_date' => 'required|date',
            'amount' => 'required|numeric|min:0',
            'payment_method' => 'nullable|string|max:100',
            'reference_number' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ]);
        $data['created_by'] = auth()->id();
        KelasPayment::create($data);
        return back()->with('message', 'Payment recorded');
    }
}


