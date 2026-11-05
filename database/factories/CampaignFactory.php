<?php

namespace Database\Factories;

use App\Enums\CampaignCategory;
use App\Enums\CampaignStatus;
use App\Models\Campaign;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Campaign> */
class CampaignFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => fake()->unique()->sentence(4),
            'category' => fake()->randomElement(CampaignCategory::cases()),
            'target_amount' => fake()->randomElement([null, 10_000_000, 50_000_000, 100_000_000]),
            'collected_amount' => 0,
            'start_date' => now()->subDays(7),
            'end_date' => now()->addDays(60),
            'status' => CampaignStatus::Active,
            'description' => fake()->paragraphs(3, true),
        ];
    }

    public function withCollected(int $amount): static
    {
        return $this->state(['collected_amount' => $amount]);
    }
}
