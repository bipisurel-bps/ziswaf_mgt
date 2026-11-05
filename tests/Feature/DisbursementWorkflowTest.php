<?php

namespace Tests\Feature;

use App\Enums\CampaignCategory;
use App\Enums\DisbursementRequestStatus;
use App\Exceptions\InsufficientCampaignBalance;
use App\Models\Campaign;
use App\Models\Disbursement;
use App\Models\DisbursementRequest;
use App\Models\JournalEntry;
use App\Models\Mustahik;
use App\Models\User;
use Database\Seeders\CoaAccountSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class DisbursementWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private Campaign $campaign;
    private Mustahik $mustahik;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CoaAccountSeeder::class);

        $this->admin = User::factory()->create();
        $this->campaign = Campaign::factory()->withCollected(5_000_000)->create([
            'category' => CampaignCategory::Zakat,
        ]);
        $this->mustahik = Mustahik::factory()->create([
            'status_approval' => 'verified',
        ]);
    }

    public function test_can_create_disbursement_request_in_draft(): void
    {
        $request = DisbursementRequest::create([
            'request_number' => DisbursementRequest::generateRequestNumber(),
            'campaign_id' => $this->campaign->id,
            'mustahik_id' => $this->mustahik->id,
            'amount' => 1_500_000,
            'purpose' => 'Bantuan biaya hidup fakir miskin',
            'status' => DisbursementRequestStatus::Draft,
            'requested_by' => $this->admin->id,
        ]);

        $this->assertDatabaseHas('disbursement_requests', [
            'id' => $request->id,
            'status' => DisbursementRequestStatus::Draft->value,
            'amount' => 1_500_000,
        ]);
    }

    public function test_can_approve_disbursement_request(): void
    {
        $request = DisbursementRequest::create([
            'request_number' => DisbursementRequest::generateRequestNumber(),
            'campaign_id' => $this->campaign->id,
            'mustahik_id' => $this->mustahik->id,
            'amount' => 1_000_000,
            'purpose' => 'Bantuan pengobatan',
            'status' => DisbursementRequestStatus::Draft,
            'requested_by' => $this->admin->id,
        ]);

        $request->approve($this->admin);

        $this->assertSame(DisbursementRequestStatus::Approved, $request->fresh()->status);
        $this->assertSame($this->admin->id, $request->fresh()->approved_by);
        $this->assertNotNull($request->fresh()->approved_at);
    }

    public function test_can_reject_disbursement_request(): void
    {
        $request = DisbursementRequest::create([
            'request_number' => DisbursementRequest::generateRequestNumber(),
            'campaign_id' => $this->campaign->id,
            'mustahik_id' => $this->mustahik->id,
            'amount' => 1_000_000,
            'purpose' => 'Bantuan modal usaha',
            'status' => DisbursementRequestStatus::Draft,
            'requested_by' => $this->admin->id,
        ]);

        $request->reject($this->admin, 'Data mustahik tidak memenuhi syarat');

        $this->assertSame(DisbursementRequestStatus::Rejected, $request->fresh()->status);
        $this->assertStringContainsString('Data mustahik tidak memenuhi syarat', $request->fresh()->notes);
    }

    public function test_disburse_creates_official_record_and_psak_109_journal(): void
    {
        $request = DisbursementRequest::create([
            'request_number' => DisbursementRequest::generateRequestNumber(),
            'campaign_id' => $this->campaign->id,
            'mustahik_id' => $this->mustahik->id,
            'amount' => 2_000_000,
            'purpose' => 'Penyaluran zakat produktif',
            'status' => DisbursementRequestStatus::Approved,
            'requested_by' => $this->admin->id,
            'approved_by' => $this->admin->id,
            'approved_at' => now(),
        ]);

        $disbursement = $request->disburse($this->admin, 'proofs/transfer.jpg', 'Transfer via BSI');

        // Request status updated
        $this->assertSame(DisbursementRequestStatus::Disbursed, $request->fresh()->status);
        $this->assertNotNull($request->fresh()->disbursed_at);

        // Official Disbursement created
        $this->assertDatabaseHas('disbursements', [
            'id' => $disbursement->id,
            'campaign_id' => $this->campaign->id,
            'mustahik_id' => $this->mustahik->id,
            'amount' => 2_000_000,
        ]);

        // Campaign remaining balance deducted: 5,000,000 - 2,000,000 = 3,000,000
        $this->assertSame(3_000_000, $this->campaign->fresh()->remaining_balance);

        // PSAK 109 Journal Entry created
        $journal = JournalEntry::where('reference_type', Disbursement::class)
            ->where('reference_id', $disbursement->id)
            ->first();

        $this->assertNotNull($journal);
        $this->assertSame(2_000_000, $journal->total_debit);
        $this->assertSame(2_000_000, $journal->total_credit);
        $this->assertTrue($journal->is_balanced);

        // Debit: Beban Penyaluran Zakat (5-1001)
        $expenseItem = $journal->items()->whereHas('coaAccount', fn ($q) => $q->where('code', '5-1001'))->first();
        $this->assertNotNull($expenseItem);
        $this->assertSame(2_000_000, $expenseItem->debit);

        // Credit: Kas/Bank (1-1002 or 1-1001)
        $kasItem = $journal->items()->whereHas('coaAccount', fn ($q) => $q->whereIn('code', ['1-1001', '1-1002']))->first();
        $this->assertNotNull($kasItem);
        $this->assertSame(2_000_000, $kasItem->credit);
    }

    public function test_cannot_disburse_exceeding_campaign_balance(): void
    {
        // Campaign collected is 5,000,000. Try to disburse 5,000,001.
        $request = DisbursementRequest::create([
            'request_number' => DisbursementRequest::generateRequestNumber(),
            'campaign_id' => $this->campaign->id,
            'mustahik_id' => $this->mustahik->id,
            'amount' => 5_000_001,
            'purpose' => 'Over budget request',
            'status' => DisbursementRequestStatus::Approved,
            'requested_by' => $this->admin->id,
        ]);

        $this->expectException(InsufficientCampaignBalance::class);
        $request->disburse($this->admin);
    }

    public function test_cannot_disburse_rejected_request(): void
    {
        $request = DisbursementRequest::create([
            'request_number' => DisbursementRequest::generateRequestNumber(),
            'campaign_id' => $this->campaign->id,
            'mustahik_id' => $this->mustahik->id,
            'amount' => 500_000,
            'purpose' => 'Sample',
            'status' => DisbursementRequestStatus::Rejected,
            'requested_by' => $this->admin->id,
        ]);

        $this->expectException(RuntimeException::class);
        $request->disburse($this->admin);
    }
}
