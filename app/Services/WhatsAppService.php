<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class WhatsAppService
{
    protected string $token;
    protected string $phoneId;
    protected string $apiVersion;

    public function __construct()
    {
        $this->token      = config('whatsapp.token');
        $this->phoneId    = config('whatsapp.phone_id');
        $this->apiVersion = config('whatsapp.api_version', 'v20.0');
    }

    protected function endpoint(): string
    {
        return "https://graph.facebook.com/{$this->apiVersion}/{$this->phoneId}/messages";
    }

    public static function normalizeMsisdn(?string $phone): ?string
    {
        if (!$phone) return null;
        // Hilangkan non-digit
        $p = preg_replace('/\D+/', '', $phone);
        // Indonesia: jika diawali 0 → ganti 62; jika sudah 62 biarkan
        if (str_starts_with($p, '0')) $p = '62' . substr($p, 1);
        return $p;
    }

    /** Kirim pesan teks biasa */
    public function sendText(string $to, string $text): array
    {
        $to = self::normalizeMsisdn($to);
        $res = Http::withToken($this->token)
            ->post($this->endpoint(), [
                'messaging_product' => 'whatsapp',
                'to' => $to,
                'type' => 'text',
                'text' => ['preview_url' => false, 'body' => $text],
            ]);

        return [
            'ok' => $res->successful(),
            'status' => $res->status(),
            'body' => $res->json(),
        ];
    }

    /** Kirim template (jika kamu sudah membuat template disetujui di WABA) */
    public function sendTemplate(string $to, string $templateName, array $params = []): array
    {
        $to = self::normalizeMsisdn($to);
        $components = [];
        if ($params) {
            $components[] = [
                'type' => 'body',
                'parameters' => array_map(fn($p) => ['type' => 'text', 'text' => (string)$p], $params),
            ];
        }

        $res = Http::withToken($this->token)->post($this->endpoint(), [
            'messaging_product' => 'whatsapp',
            'to' => $to,
            'type' => 'template',
            'template' => [
                'name' => $templateName,
                'language' => ['code' => 'id'],
                'components' => $components,
            ],
        ]);

        return [
            'ok' => $res->successful(),
            'status' => $res->status(),
            'body' => $res->json(),
        ];
    }
}
