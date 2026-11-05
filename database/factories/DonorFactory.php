<?php

namespace Database\Factories;

use App\Models\Donor;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Donor> */
class DonorFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'phone_number' => '628'.fake()->unique()->numerify('##########'),
            'email' => fake()->safeEmail(),
            'address' => fake()->address(),
            'is_verified' => false,
        ];
    }
}
