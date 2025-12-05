<?php

namespace App\Console\Commands;

use App\Jobs\SendWhatsAppNotification;
use App\Models\Member;
use App\Models\Notification;
use Illuminate\Console\Command;

class SendMembershipExpirationNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'members:send-expiration-notifications';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send WhatsApp notifications to members whose membership is expiring soon';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Check if notifications are enabled
        if (!config('whatsapp.notifications_enabled')) {
            $this->info('WhatsApp notifications are disabled.');
            return 0;
        }

        // Get notification days from config (default: 7,3,1)
        $noticeDays = explode(',', config('whatsapp.expiration_notice_days', '7,3,1'));
        $noticeDays = array_map('intval', $noticeDays);

        $this->info('Checking for members with expiring memberships...');

        $totalSent = 0;

        foreach ($noticeDays as $days) {
            $targetDate = now()->addDays($days)->toDateString();
            
            // Find members expiring on the target date
            $members = Member::where('is_active', true)
                ->whereDate('membership_end', $targetDate)
                ->whereHas('membershipPackage') // Only members with packages
                ->with('membershipPackage')
                ->get();

            $this->info("Found {$members->count()} member(s) expiring in {$days} days");

            foreach ($members as $member) {
                // Check if we already sent notification for this date
                $alreadySent = Notification::where('member_id', $member->id)
                    ->where('channel', 'whatsapp')
                    ->whereDate('send_date', now()->toDateString())
                    ->where('meta', 'like', "%expiring_{$days}_days%")
                    ->exists();

                if ($alreadySent) {
                    $this->info("  - Skipping {$member->full_name} (already sent)");
                    continue;
                }

                if (!$member->phone) {
                    $this->warn("  - Skipping {$member->full_name} (no phone number)");
                    continue;
                }

                // Create notification message
                $message = $this->buildMessage($member, $days);

                // Create notification record
                $notification = Notification::create([
                    'member_id' => $member->id,
                    'channel' => 'whatsapp',
                    'message' => $message,
                    'send_date' => now()->toDateString(),
                    'status' => 'pending',
                    'meta' => json_encode(['expiring_' . $days . '_days' => true]),
                ]);

                // Dispatch job to send WhatsApp message
                SendWhatsAppNotification::dispatch($notification->id);

                $this->info("  ✓ Queued notification for {$member->full_name}");
                $totalSent++;
            }
        }

        $this->info("Total notifications queued: {$totalSent}");
        return 0;
    }

    /**
     * Build notification message
     */
    private function buildMessage(Member $member, int $days): string
    {
        $expirationDate = $member->membership_end->format('d M Y');
        $packageName = $member->membershipPackage->name ?? 'membership';

        if ($days === 1) {
            return "Halo {$member->full_name}, membership {$packageName} Anda akan berakhir besok ({$expirationDate}). Silakan perpanjang untuk tetap menikmati fasilitas gym kami. Terima kasih!";
        } elseif ($days <= 7) {
            return "Halo {$member->full_name}, membership {$packageName} Anda akan berakhir dalam {$days} hari ({$expirationDate}). Silakan perpanjang untuk tetap menikmati fasilitas gym kami. Terima kasih!";
        } else {
            return "Halo {$member->full_name}, membership {$packageName} Anda akan berakhir pada {$expirationDate}. Silakan perpanjang untuk tetap menikmati fasilitas gym kami. Terima kasih!";
        }
    }
}

