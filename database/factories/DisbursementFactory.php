<?php

namespace Database\Factories;

use App\Models\Campaign;
use App\Models\Disbursement;
use App\Models\Mustahik;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Disbursement> */
class DisbursementFactory extends Factory
{
    public function definition(): array
    {
        return [
            'campaign_id' => Campaign::factory(),
            'mustahik_id' => Mustahik::factory(),
            'amount' => 100000,
            'disbursement_date' => now()->toDateString(),
            'notes' => null,
            'created_by' => User::factory(),
        ];
    }
}
