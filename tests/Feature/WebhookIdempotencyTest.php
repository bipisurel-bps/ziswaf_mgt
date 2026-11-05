<?php

namespace Tests\Feature;

use App\Jobs\SendDonationReceiptJob;
use App\Models\Campaign;
use App\Models\Donation;
use App\Models\Fundraiser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class WebhookIdempotencyTest extends TestCase
{
    use RefreshDatabase;

    private function payload(Donation $d, array $override = []): array
    {
        $base = [
            'order_id' => $d->invoice_number,
            'status_code' => '200',
            'gross_amount' => number_format($d->total_charge, 2, '.', ''),
            'transaction_status' => 'settlement',
            'transaction_id' => 'trx-'.$d->id,
            'payment_type' => 'qris',
        ];
        $base = array_merge($base, $override);
        $base['signature_key'] = $override['signature_key'] ?? hash(
            'sha512',
            $base['order_id'].$base['status_code'].$base['gross_amount'].config('ziswaf.midtrans.server_key')
        );

        return $base;
    }

    public function test_paid_webhook_credits_once_even_when_replayed(): void
    {
        Queue::fake();
        $fundraiser = Fundraiser::factory()->create();
        $campaign = Campaign::factory()->create();
        $donation = Donation::factory()->create([
            'campaign_id' => $campaign->id, 'amount' => 100000, 'fundraiser_id' => $fundraiser->id,
        ]);

        for ($i = 0; $i < 3; $i++) {
            $this->postJson('/api/webhooks/midtrans', $this->payload($donation))->assertOk();
        }

        $this->assertSame(100000, $campaign->fresh()->collected_amount);
        $this->assertSame(100000, $fundraiser->fresh()->total_collected);
        $this->assertSame('paid', $donation->fresh()->payment_status->value);
        $this->assertNotNull($donation->fresh()->paid_at);
        Queue::assertPushed(SendDonationReceiptJob::class, 1);
    }

    public function test_invalid_signature_is_rejected_and_changes_nothing(): void
    {
        $donation = Donation::factory()->create(['amount' => 50000]);

        $this->postJson('/api/webhooks/midtrans', $this->payload($donation, ['signature_key' => 'bad']))
            ->assertForbidden();

        $this->assertSame('pending', $donation->fresh()->payment_status->value);
        $this->assertSame(0, $donation->campaign->fresh()->collected_amount);
    }

    public function test_amount_mismatch_is_not_credited(): void
    {
        $donation = Donation::factory()->create(['amount' => 50000]);

        $this->postJson('/api/webhooks/midtrans', $this->payload($donation, ['gross_amount' => '1000.00']))->assertOk();

        $this->assertSame('pending', $donation->fresh()->payment_status->value);
    }

    public function test_late_expire_event_does_not_downgrade_paid_donation(): void
    {
        Queue::fake();
        $donation = Donation::factory()->create(['amount' => 50000]);

        $this->postJson('/api/webhooks/midtrans', $this->payload($donation))->assertOk();
        $this->postJson('/api/webhooks/midtrans', $this->payload($donation, ['transaction_status' => 'expire', 'status_code' => '407']))->assertOk();

        $this->assertSame('paid', $donation->fresh()->payment_status->value);
        $this->assertSame(50000, $donation->campaign->fresh()->collected_amount);
    }

    public function test_pending_then_expire_updates_status_without_crediting(): void
    {
        $donation = Donation::factory()->create(['amount' => 50000]);

        $this->postJson('/api/webhooks/midtrans', $this->payload($donation, ['transaction_status' => 'expire', 'status_code' => '407']))->assertOk();

        $this->assertSame('expired', $donation->fresh()->payment_status->value);
        $this->assertSame(0, $donation->campaign->fresh()->collected_amount);
    }
}
