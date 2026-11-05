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
        $this->call(DemoSeeder::class);
    }
}
