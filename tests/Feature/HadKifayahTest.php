<?php

namespace Tests\Feature;

use App\Models\Mustahik;
use App\Services\HadKifayahCalculatorService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HadKifayahTest extends TestCase
{
    use RefreshDatabase;

    private HadKifayahCalculatorService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(HadKifayahCalculatorService::class);
    }

    public function test_household_under_50_percent_classified_as_fakir(): void
    {
        // Base cost: 3,000,000. 0 dependents. Total = 3,000,000.
        // Income: 1,000,000 -> 33.33% (< 50%)
        $result = $this->service->calculate(1_000_000, 0);

        $this->assertSame('fakir', $result['category']);
        $this->assertTrue($result['is_eligible']);
        $this->assertEqualsWithDelta(0.3333, $result['score'], 0.001);
        $this->assertSame(3_000_000, $result['total_had_kifayah']);
    }

    public function test_household_between_50_and_100_percent_classified_as_miskin(): void
    {
        // Base cost: 3,000,000 + (2 * 1,000,000) = 5,000,000.
        // Income: 3,500,000 -> 70% (between 50% and 100%)
        $result = $this->service->calculate(3_500_000, 2);

        $this->assertSame('miskin', $result['category']);
        $this->assertTrue($result['is_eligible']);
        $this->assertEqualsWithDelta(0.70, $result['score'], 0.001);
        $this->assertSame(5_000_000, $result['total_had_kifayah']);
    }

    public function test_household_above_100_percent_classified_as_kifayah_terpenuhi(): void
    {
        // Base cost: 3,000,000. 0 dependents.
        // Income: 4,000,000 -> 133.33% (> 100%)
        $result = $this->service->calculate(4_000_000, 0);

        $this->assertSame('kifayah_terpenuhi', $result['category']);
        $this->assertFalse($result['is_eligible']);
        $this->assertGreaterThan(1.0, $result['score']);
    }

    public function test_mustahik_model_auto_calculates_had_kifayah_score_on_save(): void
    {
        $mustahik = Mustahik::create([
            'name' => 'Pak Budi',
            'nik' => '3201000000000001',
            'family_card_number' => '3201000000000002',
            'asnaf_category' => 'fakir',
            'income_per_month' => 1_200_000,
            'dependents_count' => 1, // Had Kifayah = 3M + 1M = 4M. Ratio = 1.2M / 4M = 0.30 (30%)
            'status_approval' => 'pending',
        ]);

        $this->assertNotNull($mustahik->had_kifayah_score);
        $this->assertEqualsWithDelta(0.30, (float) $mustahik->had_kifayah_score, 0.01);

        // Update dependents: 1 -> 3 (Had Kifayah = 3M + 3M = 6M. Ratio = 1.2M / 6M = 0.20)
        $mustahik->update(['dependents_count' => 3]);
        $this->assertEqualsWithDelta(0.20, (float) $mustahik->fresh()->had_kifayah_score, 0.01);
    }
}
