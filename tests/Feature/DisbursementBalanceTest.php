<?php

namespace Tests\Feature;

use App\Exceptions\InsufficientCampaignBalance;
use App\Models\Campaign;
use App\Models\Disbursement;
use App\Models\Mustahik;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DisbursementBalanceTest extends TestCase
{
    use RefreshDatabase;

    private function make(Campaign $c, int $amount): Disbursement
    {
        return Disbursement::create([
            'campaign_id' => $c->id,
            'mustahik_id' => Mustahik::factory()->create()->id,
            'amount' => $amount,
            'disbursement_date' => now()->toDateString(),
            'created_by' => User::factory()->create()->id,
        ]);
    }

    public function test_can_disburse_up_to_remaining_balance(): void
    {
        $c = Campaign::factory()->withCollected(1_000_000)->create();

        $this->make($c, 600_000);
        $this->make($c, 400_000);

        $this->assertSame(0, $c->fresh()->remaining_balance);
    }

    public function test_cannot_exceed_remaining_balance(): void
    {
        $c = Campaign::factory()->withCollected(1_000_000)->create();
        $this->make($c, 900_000);

        $this->expectException(InsufficientCampaignBalance::class);
        $this->make($c, 100_001);
    }

    public function test_editing_existing_disbursement_excludes_itself_from_total(): void
    {
        $c = Campaign::factory()->withCollected(1_000_000)->create();
        $d = $this->make($c, 1_000_000);

        $d->update(['amount' => 900_000]); // must not be double counted
        $this->assertSame(100_000, $c->fresh()->remaining_balance);
    }

    public function test_nik_is_encrypted_at_rest_and_hashed_for_lookup(): void
    {
        $m = Mustahik::factory()->create(['nik' => '3174012345678901']);

        $raw = \DB::table('mustahiks')->where('id', $m->id)->first();
        $this->assertNotSame('3174012345678901', $raw->nik);
        $this->assertSame(Mustahik::hashNik('3174012345678901'), $raw->nik_hash);
        $this->assertSame('3174012345678901', $m->fresh()->nik);
    }
}
