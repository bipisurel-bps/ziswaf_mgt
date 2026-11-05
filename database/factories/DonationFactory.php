<?php

namespace Database\Factories;

use App\Enums\PaymentStatus;
use App\Models\Campaign;
use App\Models\Donation;
use App\Models\Donor;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Donation> */
class DonationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'invoice_number' => Donation::generateInvoiceNumber(),
            'campaign_id' => Campaign::factory(),
            'donor_id' => Donor::factory(),
            'amount' => fake()->randomElement([25000, 50000, 100000, 500000]),
            'unique_code' => 0,
            'donation_type' => 'infaq',
            'payment_method' => 'qris',
            'payment_status' => PaymentStatus::Pending,
            'is_anonim' => false,
        ];
    }

    public function paid(): static
    {
        return $this->state(['payment_status' => PaymentStatus::Paid, 'paid_at' => now()]);
    }
}
