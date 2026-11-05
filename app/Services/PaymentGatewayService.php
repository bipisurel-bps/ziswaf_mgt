<?php

namespace App\Services;

use App\Models\Donation;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Midtrans Snap integration: create transactions and verify webhook signatures.
 */
class PaymentGatewayService
{
    public function snapUrl(): string
    {
        return config('ziswaf.midtrans.is_production')
            ? 'https://app.midtrans.com/snap/v1/transactions'
            : 'https://app.sandbox.midtrans.com/snap/v1/transactions';
    }

    public function snapJsUrl(): string
    {
        return config('ziswaf.midtrans.is_production')
            ? 'https://app.midtrans.com/snap/snap.js'
            : 'https://app.sandbox.midtrans.com/snap/snap.js';
    }

    /**
     * Create a Snap transaction. Returns ['token' => ..., 'redirect_url' => ...].
     */
    public function createTransaction(Donation $donation): array
    {
        $serverKey = config('ziswaf.midtrans.server_key');
        if (! $serverKey) {
            throw new RuntimeException('MIDTRANS_SERVER_KEY belum dikonfigurasi.');
        }

        $donation->loadMissing('donor', 'campaign');

        $payload = [
            'transaction_details' => [
                'order_id' => $donation->invoice_number,
                'gross_amount' => $donation->total_charge,
            ],
            'item_details' => [[
                'id' => 'donation-'.$donation->id,
                'price' => $donation->total_charge,
                'quantity' => 1,
                'name' => mb_substr($donation->campaign?->title ?? 'Donasi Umum', 0, 50),
            ]],
            'customer_details' => [
                'first_name' => $donation->donor->name,
                'phone' => $donation->donor->phone_number,
                'email' => $donation->donor->email,
            ],
            'expiry' => [
                'unit' => 'hours',
                'duration' => config('ziswaf.midtrans.expiry_hours'),
            ],
            'callbacks' => ['finish' => route('donation.thanks', $donation->invoice_number)],
        ];

        if ($donation->payment_method && $donation->payment_method !== 'any') {
            $payload['enabled_payments'] = [$donation->payment_method];
        }

        $response = Http::withBasicAuth($serverKey, '')
            ->acceptJson()
            ->timeout(20)
            ->post($this->snapUrl(), $payload);

        if (! $response->successful() || ! $response->json('token')) {
            throw new RuntimeException('Gagal membuat transaksi Midtrans: '.$response->body());
        }

        return $response->json();
    }

    /**
     * Midtrans signature = SHA512(order_id + status_code + gross_amount + ServerKey).
     */
    public function verifySignature(array $payload): bool
    {
        $serverKey = (string) config('ziswaf.midtrans.server_key');
        if ($serverKey === '' || empty($payload['signature_key'])) {
            return false;
        }

        $expected = hash('sha512',
            ($payload['order_id'] ?? '').($payload['status_code'] ?? '').($payload['gross_amount'] ?? '').$serverKey
        );

        return hash_equals($expected, (string) $payload['signature_key']);
    }

    /**
     * Map Midtrans transaction_status/fraud_status to our internal status.
     *
     * @return 'paid'|'pending'|'expired'|'failed'
     */
    public function mapStatus(array $payload): string
    {
        $status = $payload['transaction_status'] ?? '';
        $fraud = $payload['fraud_status'] ?? 'accept';

        return match (true) {
            $status === 'settlement' => 'paid',
            $status === 'capture' && $fraud === 'accept' => 'paid',
            $status === 'expire' => 'expired',
            in_array($status, ['deny', 'cancel', 'failure'], true) => 'failed',
            default => 'pending',
        };
    }
}
