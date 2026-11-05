<?php

namespace Tests\Feature;

use App\Enums\CampaignCategory;
use App\Enums\PaymentStatus;
use App\Jobs\RecordDonationJournalEntryJob;
use App\Models\Campaign;
use App\Models\CoaAccount;
use App\Models\Donor;
use App\Models\Donation;
use App\Models\JournalEntry;
use App\Services\AccountingService;
use Database\Seeders\CoaAccountSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

class AccountingJournalTest extends TestCase
{
    use RefreshDatabase;

    private AccountingService $accounting;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CoaAccountSeeder::class);
        $this->accounting = app(AccountingService::class);
    }

    public function test_cannot_create_unbalanced_journal_entry(): void
    {
        $kas = CoaAccount::where('code', '1-1001')->firstOrFail();
        $zakat = CoaAccount::where('code', '4-1001')->firstOrFail();

        $this->expectException(InvalidArgumentException::class);
        $this->accounting->createEntry([
            'description' => 'Unbalanced test',
        ], [
            ['coa_account_id' => $kas->id, 'debit' => 100_000, 'credit' => 0],
            ['coa_account_id' => $zakat->id, 'debit' => 0, 'credit' => 90_000], // Mismatch!
        ]);
    }

    public function test_can_create_balanced_manual_journal_entry(): void
    {
        $kas = CoaAccount::where('code', '1-1001')->firstOrFail();
        $infaq = CoaAccount::where('code', '4-2001')->firstOrFail();

        $entry = $this->accounting->createEntry([
            'description' => 'Manual entry test',
        ], [
            ['coa_account_id' => $kas->id, 'debit' => 250_000, 'credit' => 0],
            ['coa_account_id' => $infaq->id, 'debit' => 0, 'credit' => 250_000],
        ]);

        $this->assertDatabaseHas('journal_entries', ['id' => $entry->id, 'status' => 'posted']);
        $this->assertSame(250_000, $entry->fresh()->total_debit);
        $this->assertSame(250_000, $entry->fresh()->total_credit);
        $this->assertTrue($entry->fresh()->is_balanced);
    }

    public function test_settled_infaq_donation_generates_balanced_journal_entry(): void
    {
        $donor = Donor::factory()->create();
        $donation = Donation::create([
            'invoice_number' => 'INV-TEST-INFAQ-01',
            'donor_id' => $donor->id,
            'amount' => 500_000,
            'payment_status' => PaymentStatus::Paid,
            'paid_at' => now(),
            'payment_method' => 'qris',
        ]);

        $job = new RecordDonationJournalEntryJob($donation->id);
        $job->handle($this->accounting);

        $entry = JournalEntry::where('reference_type', Donation::class)
            ->where('reference_id', $donation->id)
            ->first();

        $this->assertNotNull($entry);
        $this->assertSame(500_000, $entry->total_debit);
        $this->assertSame(500_000, $entry->total_credit);
        $this->assertTrue($entry->is_balanced);

        // Check items: Debit Kas Gateway (1-1001), Credit Infaq Bebas (4-2001)
        $kasItem = $entry->items()->whereHas('coaAccount', fn ($q) => $q->where('code', '1-1001'))->first();
        $infaqItem = $entry->items()->whereHas('coaAccount', fn ($q) => $q->where('code', '4-2001'))->first();

        $this->assertNotNull($kasItem);
        $this->assertSame(500_000, $kasItem->debit);
        $this->assertSame(0, $kasItem->credit);

        $this->assertNotNull($infaqItem);
        $this->assertSame(0, $infaqItem->debit);
        $this->assertSame(500_000, $infaqItem->credit);
    }

    public function test_zakat_donation_splits_amil_share_when_configured(): void
    {
        config(['ziswaf.accounting.amil_zakat_percentage' => 0.125]); // 12.5% Hak Amil

        $campaign = Campaign::factory()->create(['category' => CampaignCategory::Zakat]);
        $donor = Donor::factory()->create();
        $donation = Donation::create([
            'invoice_number' => 'INV-TEST-ZAKAT-01',
            'campaign_id' => $campaign->id,
            'donor_id' => $donor->id,
            'amount' => 1_000_000,
            'payment_status' => PaymentStatus::Paid,
            'paid_at' => now(),
            'payment_method' => 'bca_va',
        ]);

        $this->accounting->recordDonation($donation);

        $entry = JournalEntry::where('reference_type', Donation::class)
            ->where('reference_id', $donation->id)
            ->first();

        $this->assertNotNull($entry);
        $this->assertSame(1_000_000, $entry->total_debit);
        $this->assertSame(1_000_000, $entry->total_credit);

        // Kas: Debit 1,000,000
        $kasItem = $entry->items()->whereHas('coaAccount', fn ($q) => $q->where('code', '1-1001'))->first();
        $this->assertSame(1_000_000, $kasItem->debit);

        // Zakat: Credit 875,000 (87.5%)
        $zakatItem = $entry->items()->whereHas('coaAccount', fn ($q) => $q->where('code', '4-1001'))->first();
        $this->assertSame(875_000, $zakatItem->credit);

        // Amil: Credit 125,000 (12.5%)
        $amilItem = $entry->items()->whereHas('coaAccount', fn ($q) => $q->where('code', '4-3001'))->first();
        $this->assertSame(125_000, $amilItem->credit);
    }

    public function test_record_donation_job_is_idempotent(): void
    {
        $donor = Donor::factory()->create();
        $donation = Donation::create([
            'invoice_number' => 'INV-TEST-IDEMP-01',
            'donor_id' => $donor->id,
            'amount' => 100_000,
            'payment_status' => PaymentStatus::Paid,
            'paid_at' => now(),
            'payment_method' => 'qris',
        ]);

        $job = new RecordDonationJournalEntryJob($donation->id);

        // Run 1st time
        $job->handle($this->accounting);
        $count = JournalEntry::where('reference_type', Donation::class)
            ->where('reference_id', $donation->id)
            ->count();
        $this->assertSame(1, $count);

        // Run 2nd time (should skip)
        $job->handle($this->accounting);
        $countAfter = JournalEntry::where('reference_type', Donation::class)
            ->where('reference_id', $donation->id)
            ->count();
        $this->assertSame(1, $countAfter);
    }
}
