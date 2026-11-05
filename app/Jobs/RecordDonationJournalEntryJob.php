<?php

namespace App\Jobs;

use App\Models\CoaAccount;
use App\Models\Donation;
use App\Models\JournalEntry;
use App\Services\AccountingService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class RecordDonationJournalEntryJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function __construct(public int $donationId) {}

    public function handle(AccountingService $accounting): void
    {
        if (! CoaAccount::exists()) {
            return;
        }

        $donation = Donation::with('campaign', 'donor')->find($this->donationId);

        if (! $donation || ! $donation->is_paid) {
            return;
        }

        // Idempotency: skip if already journaled for this donation
        $exists = JournalEntry::where('reference_type', Donation::class)
            ->where('reference_id', $donation->id)
            ->exists();

        if ($exists) {
            return;
        }

        $accounting->recordDonation($donation);
    }
}
