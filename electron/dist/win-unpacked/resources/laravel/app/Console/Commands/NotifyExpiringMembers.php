<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\{Member, Notification};
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;

class NotifyExpiringMembers extends Command
{
    protected $signature = 'notify:expiring-members {--send : langsung tandai terkirim (simulasi kirim)}';
    protected $description = 'Buat notifikasi untuk member yang akan expired H-5 dan (opsional) kirim';

    public function handle(): int
    {
        $today     = Carbon::today();
        $target    = $today->copy()->addDays(5); // H-5
        $sendFlag  = $this->option('send');

        // ambil member yang akan habis tepat di H-5
        $members = Member::query()
            ->whereDate('membership_end', $target->toDateString())
            ->where('is_active', true)
            ->get();

        $this->info("Members expiring on {$target->toDateString()}: ".$members->count());

        foreach ($members as $m) {
            // hindari duplikasi notifikasi (member + send_date + channel)
            $exists = Notification::where('member_id', $m->id)
                ->whereDate('send_date', $today->toDateString())
                ->where('channel', 'whatsapp')
                ->exists();

            if ($exists) continue;

            $msg = "Halo {$m->full_name}, keanggotaan gym Anda akan berakhir pada ".
                   $m->membership_end->format('d/m/Y').
                   ". Silakan perpanjang agar akses tetap aktif. Terima kasih!";

            $notif = Notification::create([
                'member_id' => $m->id,
                'channel'   => 'whatsapp',
                'message'   => $msg,
                'send_date' => $today->toDateString(),
                'status'    => 'pending',
            ]);

            $this->line("Queued notif for {$m->full_name} (ID: {$notif->id})");

            // opsi: langsung tandai terkirim (simulasi kirim WA)
            if ($sendFlag) {
                \Log::info("Queue WA notif for member_id={$m->id}, notif_id={$notif->id}");
                SendWhatsAppNotification::dispatch($notif->id);
            }
        }

        return Command::SUCCESS;
    }

    // simulasi pengiriman via WA: tandai sent + log
    protected function sendWhatsapp(?string $phone, string $message, Notification $notif): void
    {
        if (!$phone) {
            $notif->update(['status'=>'failed', 'meta'=>'{"reason":"no_phone"}']);
            return;
        }
        // produksi: integrasikan WhatsApp Business API / provider gateway
        Log::info('SEND_WA', ['to'=>$phone,'message'=>$message]);

        $notif->update([
            'status'  => 'sent',
            'sent_at' => now(),
        ]);
    }
}
