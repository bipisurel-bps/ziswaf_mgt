<?php

namespace Database\Factories;

use App\Models\Fundraiser;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<Fundraiser> */
class FundraiserFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'phone_number' => '628'.fake()->unique()->numerify('##########'),
            'referral_code' => strtoupper(Str::random(8)),
            'total_collected' => 0,
        ];
    }
}
