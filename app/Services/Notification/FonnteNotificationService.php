<?php

namespace App\Services\Notification;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/** Fonnte WhatsApp adapter (https://docs.fonnte.com/api-send-message/). */
class FonnteNotificationService implements NotificationService
{
    public function __construct(
        private readonly ?string $token,
        private readonly string $endpoint = 'https://api.fonnte.com/send',
    ) {}

    public function send(string $to, string $message, ?string $attachmentUrl = null): bool
    {
        if (! $this->token) {
            throw new RuntimeException('FONNTE_TOKEN belum dikonfigurasi.');
        }

        $payload = ['target' => $to, 'message' => $message, 'countryCode' => '62'];
        if ($attachmentUrl) {
            $payload['url'] = $attachmentUrl;
        }

        $response = Http::withHeaders(['Authorization' => $this->token])
            ->asForm()
            ->timeout(15)
            ->post($this->endpoint, $payload);

        $ok = $response->successful() && ($response->json('status') === true);

        if (! $ok) {
            Log::warning('[Notification:fonnte] gagal kirim', [
                'to' => $to, 'http' => $response->status(), 'body' => $response->json(),
            ]);
        }

        return $ok;
    }
}
