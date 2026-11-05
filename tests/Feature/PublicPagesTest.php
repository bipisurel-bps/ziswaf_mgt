<?php

namespace Tests\Feature;

use App\Models\Campaign;
use App\Models\Donation;
use App\Models\Fundraiser;
use App\Services\ReceiptService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PublicPagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_public_pages_render(): void
    {
        $campaign = Campaign::factory()->create(['description' => "Halo <script>alert(1)</script> **tebal**"]);

        $this->get('/')->assertOk()->assertSee($campaign->title);
        $this->get('/zakat')->assertOk()->assertSee('Kalkulator Zakat');
        $this->get('/donasi')->assertOk();
        $this->get('/kampanye/'.$campaign->slug)->assertOk()
            ->assertDontSee('<script>alert(1)</script>', false)
            ->assertSee('<strong>tebal</strong>', false);
    }

    public function test_checkout_creates_pending_donation_with_snap_token_and_referral(): void
    {
        Http::fake(['*snap/v1/transactions' => Http::response(['token' => 'snap-token-123', 'redirect_url' => 'https://x'], 201)]);
        $campaign = Campaign::factory()->create();
        $fr = Fundraiser::factory()->create(['referral_code' => 'ABC123']);

        $res = $this->withCookie('ref', 'ABC123')->post('/donasi', [
            'amount' => '100.000', 'name' => 'Budi', 'phone_number' => '0812-3456-7890',
            'campaign_id' => $campaign->id, 'is_anonim' => '1', 'payment_method' => 'qris',
            'referral_code' => 'ABC123',
        ]);

        $d = Donation::firstOrFail();
        $res->assertRedirect(route('donation.pay', $d->invoice_number));
        $this->assertSame(100000, $d->amount);
        $this->assertSame('snap-token-123', $d->snap_token);
        $this->assertSame($fr->id, $d->fundraiser_id);
        $this->assertSame('6281234567890', $d->donor->phone_number);
        $this->assertSame('Hamba Allah', $d->display_name);
        $this->assertMatchesRegularExpression('/^INV-\d{8}-[A-Z0-9]{4}$/', $d->invoice_number);
    }

    public function test_checkout_rejects_below_minimum(): void
    {
        $this->post('/donasi', ['amount' => 500, 'name' => 'A', 'phone_number' => '081234567890'])
            ->assertSessionHasErrors('amount');
        $this->assertDatabaseCount('donations', 0);
    }

    public function test_receipt_pdf_and_signed_verification(): void
    {
        $d = Donation::factory()->paid()->create();
        $receipts = app(ReceiptService::class);

        $this->assertStringStartsWith('%PDF', $receipts->renderPdf($d)->output());

        $this->get('/verifikasi/'.$d->invoice_number)->assertForbidden(); // unsigned
        $this->get($receipts->verificationUrl($d))->assertOk()->assertSee('Kwitansi Valid');
    }
}
