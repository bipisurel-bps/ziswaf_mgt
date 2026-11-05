<?php

namespace Tests\Feature;

use App\Filament\Pages\ManageZakatSettings;
use App\Filament\Resources\CampaignResource;
use App\Filament\Resources\DisbursementResource;
use App\Filament\Resources\DonationResource;
use App\Filament\Resources\DonorResource;
use App\Filament\Resources\FundraiserResource;
use App\Filament\Resources\MustahikResource;
use App\Models\Campaign;
use App\Models\Disbursement;
use App\Models\Donation;
use App\Models\Mustahik;
use App\Models\Setting;
use App\Models\User;
use App\Services\ZakatCalculationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminPanelTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get('/admin')->assertRedirect('/admin/login');
    }

    public function test_all_admin_list_pages_render(): void
    {
        $this->actingAs(User::factory()->create());
        Donation::factory()->paid()->count(2)->create();
        Mustahik::factory()->create();

        foreach ([CampaignResource::class, DonationResource::class, DonorResource::class,
            MustahikResource::class, DisbursementResource::class, FundraiserResource::class] as $resource) {
            $this->get($resource::getUrl('index'))->assertOk();
        }
        $this->get('/admin')->assertOk();
        $this->get(ManageZakatSettings::getUrl())->assertOk();
    }

    public function test_gold_price_setting_form_saves_and_affects_nishab(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(ManageZakatSettings::class)
            ->fillForm(['gold_price_per_gram' => 2000000])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame(170_000_000, app(ZakatCalculationService::class)->nishabYearly());
    }

    public function test_disbursement_form_rejects_amount_over_balance(): void
    {
        $this->actingAs($user = User::factory()->create());
        $c = Campaign::factory()->withCollected(500_000)->create();
        $m = Mustahik::factory()->create();

        Livewire::test(DisbursementResource\Pages\CreateDisbursement::class)
            ->fillForm(['campaign_id' => $c->id, 'mustahik_id' => $m->id, 'amount' => 500_001, 'disbursement_date' => now()->toDateString()])
            ->call('create')
            ->assertHasFormErrors(['amount']);

        Livewire::test(DisbursementResource\Pages\CreateDisbursement::class)
            ->fillForm(['campaign_id' => $c->id, 'mustahik_id' => $m->id, 'amount' => 500_000, 'disbursement_date' => now()->toDateString()])
            ->call('create')
            ->assertHasNoFormErrors();

        $d = Disbursement::firstOrFail();
        $this->assertSame($user->id, $d->created_by);
        $this->assertSame(0, $c->fresh()->remaining_balance);
    }

    public function test_donation_csv_export_neutralizes_formulas(): void
    {
        $this->assertSame("'=SUM(A1)", DonationResource::safeCell('=SUM(A1)'));
        $this->assertSame('Budi', DonationResource::safeCell('Budi'));
    }
}
