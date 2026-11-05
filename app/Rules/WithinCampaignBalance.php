<?php

namespace App\Rules;

use App\Models\Campaign;
use App\Models\Disbursement;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/** Form-level mirror of the guard enforced in Disbursement::booted(). */
class WithinCampaignBalance implements ValidationRule
{
    public function __construct(private readonly ?int $campaignId, private readonly ?int $ignoreDisbursementId = null) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! $this->campaignId) {
            return;
        }

        $campaign = Campaign::withTrashed()->find($this->campaignId);
        if (! $campaign) {
            return;
        }

        $disbursed = (int) Disbursement::where('campaign_id', $campaign->id)
            ->when($this->ignoreDisbursementId, fn ($q) => $q->whereKeyNot($this->ignoreDisbursementId))
            ->sum('amount');
        $remaining = $campaign->collected_amount - $disbursed;

        if ((int) $value > $remaining) {
            $fail('Melebihi sisa dana kampanye: Rp '.number_format(max(0, $remaining), 0, ',', '.').'.');
        }
    }
}
