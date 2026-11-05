<?php

namespace Tests\Feature;

use App\Models\Donor;
use App\Models\Setting;
use App\Services\ZakatCalculationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ZakatTest extends TestCase
{
    use RefreshDatabase;

    public function test_nishab_uses_env_fallback_gold_price(): void
    {
        $s = app(ZakatCalculationService::class);

        $this->assertSame(1_500_000, $s->goldPricePerGram());
        $this->assertSame(127_500_000, $s->nishabYearly());
        $this->assertSame(10_625_000, $s->nishabMonthly());
    }

    public function test_admin_setting_overrides_fallback(): void
    {
        Setting::set('gold_price_per_gram', 2_000_000);

        $this->assertSame(170_000_000, app(ZakatCalculationService::class)->nishabYearly());
    }

    public function test_profession_zakat_at_and_below_nishab(): void
    {
        $s = app(ZakatCalculationService::class);

        $ok = $s->profession(12_000_000, 1_000_000, 2_000_000); // 11jt >= 10.625jt
        $this->assertTrue($ok['obligatory']);
        $this->assertSame(275_000, $ok['zakat']);

        $no = $s->profession(10_000_000);
        $this->assertFalse($no['obligatory']);
        $this->assertSame(0, $no['zakat']);
    }

    public function test_maal_requires_haul_and_nishab(): void
    {
        $s = app(ZakatCalculationService::class);

        $this->assertSame(3_200_000, $s->maal(128_000_000)['zakat']);
        $this->assertSame(0, $s->maal(128_000_000, 0, false)['zakat']);
        $this->assertSame(0, $s->maal(100_000_000)['zakat']);
    }

    public function test_phone_normalization(): void
    {
        $this->assertSame('6281234567890', Donor::normalizePhone('0812-3456-7890'));
        $this->assertSame('6281234567890', Donor::normalizePhone('+62 812 3456 7890'));
        $this->assertSame('6281234567890', Donor::normalizePhone('81234567890'));
    }
}
