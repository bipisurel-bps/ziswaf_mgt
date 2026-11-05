<?php

namespace Database\Factories;

use App\Enums\ApprovalStatus;
use App\Enums\AsnafCategory;
use App\Models\Mustahik;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Mustahik> */
class MustahikFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'nik' => fake()->unique()->numerify('################'),
            'asnaf_category' => fake()->randomElement(AsnafCategory::cases()),
            'phone' => '628'.fake()->numerify('##########'),
            'address' => fake()->address(),
            'status_approval' => ApprovalStatus::Verified,
        ];
    }
}
