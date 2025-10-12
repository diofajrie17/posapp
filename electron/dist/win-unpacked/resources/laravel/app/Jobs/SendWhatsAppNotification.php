<?php

namespace App\Jobs;

use App\Models\Notification;
use App\Services\WhatsAppService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendWhatsAppNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 30;

    public function __construct(public int $notificationId) {}

    public function handle(WhatsAppService $wa): void
    {
        $notif = Notification::with('member:id,full_name,phone')->find($this->notificationId);
        if (!$notif || !$notif->member || !$notif->member->phone) return;

        // Pilih kirim template atau teks biasa
        $template = config('whatsapp.default_template');
        if ($template) {
            // contoh parameter: [Nama, Tanggal Expired]
            $params = [$notif->member->full_name];
            $resp = $wa->sendTemplate($notif->member->phone, $template, $params);
        } else {
            $resp = $wa->sendText($notif->member->phone, $notif->message);
        }

        if ($resp['ok']) {
            $messageId = $resp['body']['messages'][0]['id'] ?? null;
            $notif->update([
                'status'  => 'sent',
                'sent_at' => now(),
                'meta'    => $messageId ? json_encode(['message_id' => $messageId]) : null,
            ]);
        } else {
            $notif->update([
                'status'  => 'failed',
                'meta'    => json_encode(['status' => $resp['status'], 'body' => $resp['body']]),
            ]);
        }
    }
}
