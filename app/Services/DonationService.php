<?php

namespace App\Services;

use App\Enums\PaymentStatus;
use App\Jobs\RecordDonationJournalEntryJob;
use App\Jobs\SendDonationReceiptJob;
use App\Models\Campaign;
use App\Models\Donation;
use App\Models\Donor;
use App\Models\Fundraiser;
use Illuminate\Support\Facades\DB;

class DonationService
{
    public function __construct(private readonly PaymentGatewayService $gateway) {}

    /**
     * Create a pending donation (+ find/create donor) and a Midtrans Snap transaction.
     *
     * @param  array{name:string,phone_number:string,email?:?string,amount:int,campaign_id?:?int,
     *   payment_method?:?string,doa_harapan?:?string,is_anonim?:bool,referral_code?:?string,donation_type?:?string}  $data
     */
    public function create(array $data): Donation
    {
        $donation = DB::transaction(function () use ($data) {
            $phone = Donor::normalizePhone($data['phone_number']);

            $donor = Donor::firstOrCreate(
                ['phone_number' => $phone],
                ['name' => $data['name'], 'email' => $data['email'] ?? null]
            );
            // Keep latest email if donor previously had none
            if (! $donor->email && ! empty($data['email'])) {
                $donor->update(['email' => $data['email']]);
            }

            $fundraiserId = null;
            if (! empty($data['referral_code'])) {
                $fundraiserId = Fundraiser::where('referral_code', strtoupper($data['referral_code']))->value('id');
            }

            $campaign = ! empty($data['campaign_id']) ? Campaign::find($data['campaign_id']) : null;

            return Donation::create([
                'invoice_number' => Donation::generateInvoiceNumber(),
                'campaign_id' => $campaign?->id,
                'donor_id' => $donor->id,
                'amount' => (int) $data['amount'],
                'unique_code' => 0,
                'donation_type' => $data['donation_type'] ?? ($campaign?->category->value ?? 'infaq'),
                'payment_method' => $data['payment_method'] ?? null,
                'payment_status' => PaymentStatus::Pending,
                'doa_harapan' => $data['doa_harapan'] ?? null,
                'is_anonim' => (bool) ($data['is_anonim'] ?? false),
                'fundraiser_id' => $fundraiserId,
            ]);
        });

        // External HTTP call kept outside the DB transaction.
        $snap = $this->gateway->createTransaction($donation);
        $donation->update([
            'snap_token' => $snap['token'],
            'payment_gateway_ref' => null, // filled from webhook (transaction_id)
        ]);

        return $donation;
    }

    /**
     * Process a verified gateway notification IDEMPOTENTLY.
     *
     * Row lock on the donation serializes concurrent/replayed webhooks; a donation that is
     * already paid is never credited again. Returns true if this call changed state.
     */
    public function handleGatewayNotification(array $payload): bool
    {
        $newStatus = PaymentStatus::from($this->gateway->mapStatus($payload));

        return DB::transaction(function () use ($payload, $newStatus) {
            $donation = Donation::where('invoice_number', $payload['order_id'] ?? '')
                ->lockForUpdate()
                ->first();

            if (! $donation) {
                return false;
            }

            // Terminal "paid" state is final: ignore replays and late expire/cancel events.
            if ($donation->payment_status === PaymentStatus::Paid) {
                return false;
            }

            // Defense in depth: the settled amount must match what we asked for.
            if ($newStatus === PaymentStatus::Paid
                && (int) round((float) ($payload['gross_amount'] ?? 0)) !== $donation->total_charge) {
                return false;
            }

            if ($newStatus === PaymentStatus::Paid) {
                $this->markPaid($donation, $payload);

                return true;
            }

            if ($donation->payment_status === $newStatus) {
                return false;
            }

            $donation->update([
                'payment_status' => $newStatus,
                'payment_gateway_ref' => $payload['transaction_id'] ?? $donation->payment_gateway_ref,
            ]);

            return true;
        });
    }

    /** Must be called inside a transaction with $donation row locked. */
    private function markPaid(Donation $donation, array $payload): void
    {
        $donation->update([
            'payment_status' => PaymentStatus::Paid,
            'paid_at' => now(),
            'payment_gateway_ref' => $payload['transaction_id'] ?? $donation->payment_gateway_ref,
            'payment_method' => $payload['payment_type'] ?? $donation->payment_method,
        ]);

        if ($donation->campaign_id) {
            Campaign::withTrashed()->whereKey($donation->campaign_id)->increment('collected_amount', $donation->amount);
        }

        if ($donation->fundraiser_id) {
            Fundraiser::whereKey($donation->fundraiser_id)->increment('total_collected', $donation->amount);
        }

        SendDonationReceiptJob::dispatch($donation->id)->afterCommit();
        RecordDonationJournalEntryJob::dispatch($donation->id)->afterCommit();
    }
}
