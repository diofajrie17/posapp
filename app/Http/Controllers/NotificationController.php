<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use Inertia\Inertia;

class NotificationController extends Controller
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
        $date = $request->query('date');
        $status = $request->query('status');

        $q = Notification::with('member:id,full_name,phone')
            ->orderBy('send_date','desc')->orderBy('id','desc');

        if ($date)   $q->whereDate('send_date', $date);
        if ($status) $q->where('status', $status);

        $notifications = $q->paginate(20)->withQueryString();

        return Inertia::render('Notifications/Index', [
            'notifications' => $notifications,
            'filters' => ['date'=>$date,'status'=>$status],
        ]);
    }

    public function send(Notification $notification)
{
    // validasi telepon
    if (!$notification->member || !$notification->member->phone) {
        $notification->update(['status'=>'failed','meta'=>'{"reason":"no_phone"}']);
        return back()->with('message','Nomor telepon kosong. Gagal kirim.');
    }

    // hindari kirim ulang yang sudah sent
    if ($notification->status === 'sent') {
        return back()->with('message','Notifikasi sudah terkirim.');
    }

    SendWhatsAppNotification::dispatch($notification->id);
    return back()->with('message','Dijadwalkan untuk dikirim (queue).');
}
}
