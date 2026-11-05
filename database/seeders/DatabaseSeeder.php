<?php

namespace Database\Seeders;

use App\Enums\AsnafCategory;
use App\Enums\CampaignCategory;
use App\Enums\CampaignStatus;
use App\Models\Campaign;
use App\Models\Fundraiser;
use App\Models\Mustahik;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(CoaAccountSeeder::class);

        $admin = config('ziswaf.seed_admin');
        User::updateOrCreate(
            ['email' => $admin['email']],
            ['name' => $admin['name'], 'password' => $admin['password']]
        );

        $campaigns = [
            ['title' => 'Zakat Maal untuk Dhuafa', 'category' => CampaignCategory::Zakat, 'target_amount' => 100_000_000,
                'description' => "Tunaikan zakat maal Anda. Dana disalurkan kepada **8 asnaf** yang berhak sesuai syariat."],
            ['title' => 'Sedekah Pangan untuk Keluarga Prasejahtera', 'category' => CampaignCategory::Infaq, 'target_amount' => 50_000_000,
                'description' => "Paket pangan bulanan bagi keluarga prasejahtera di sekitar kita."],
            ['title' => 'Wakaf Sumur dan Air Bersih', 'category' => CampaignCategory::Wakaf, 'target_amount' => null,
                'description' => "Wakaf produktif pembangunan sumur bor untuk desa kesulitan air bersih. Donasi terbuka tanpa target."],
        ];

        foreach ($campaigns as $c) {
            Campaign::firstOrCreate(
                ['title' => $c['title']],
                $c + ['status' => CampaignStatus::Active, 'start_date' => now()->toDateString(), 'end_date' => now()->addMonths(6)->toDateString()]
            );
        }

        // Contoh penerima manfaat: satu per asnaf (status verified)
        foreach (AsnafCategory::cases() as $i => $asnaf) {
            Mustahik::firstOrCreate(
                ['nik_hash' => Mustahik::hashNik('317400000000000'.$i)],
                ['name' => 'Contoh '.$asnaf->label(), 'nik' => '317400000000000'.$i, 'asnaf_category' => $asnaf, 'status_approval' => 'verified']
            );
        }

        Fundraiser::firstOrCreate(
            ['referral_code' => 'RELAWAN01'],
            ['name' => 'Relawan Contoh', 'phone_number' => '6281200000001']
        );
    }
}
