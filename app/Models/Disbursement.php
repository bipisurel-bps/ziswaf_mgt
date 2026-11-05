<?php

namespace App\Models;

use App\Exceptions\InsufficientCampaignBalance;
use App\Models\CoaAccount;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class Disbursement extends Model
{
    use HasFactory;

    protected $fillable = [
        'campaign_id', 'mustahik_id', 'amount', 'disbursement_date',
        'proof_document', 'notes', 'created_by',
    ];

    protected $casts = [
        'amount' => 'integer',
        'disbursement_date' => 'date',
    ];

    /**
     * Model-level guard: a disbursement may never exceed the campaign's
     * remaining balance (collected_amount - total disbursed). The campaign row is
     * locked so concurrent disbursements cannot overspend.
     */
    protected static function booted(): void
    {
        static::saving(function (Disbursement $d) {
            DB::transaction(function () use ($d) {
                $campaign = Campaign::withTrashed()->lockForUpdate()->findOrFail($d->campaign_id);

                $others = (int) static::where('campaign_id', $campaign->id)
                    ->when($d->exists, fn ($q) => $q->where('id', '!=', $d->id))
                    ->sum('amount');

                $remaining = $campaign->collected_amount - $others;

                if ($d->amount > $remaining) {
                    throw new InsufficientCampaignBalance($remaining, $d->amount);
                }
            });
        });

        static::created(function (Disbursement $d) {
            if (config('ziswaf.accounting.auto_record_journal', true) && CoaAccount::exists()) {
                $exists = JournalEntry::where('reference_type', Disbursement::class)
                    ->where('reference_id', $d->id)
                    ->exists();

                if (! $exists) {
                    app(\App\Services\AccountingService::class)->recordDisbursement($d);
                }
            }
        });
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class)->withTrashed();
    }

    public function mustahik(): BelongsTo
    {
        return $this->belongsTo(Mustahik::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
