<?php

namespace App\Models;

use App\Enums\DisbursementRequestStatus;
use App\Exceptions\InsufficientCampaignBalance;
use App\Services\AccountingService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class DisbursementRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'request_number',
        'campaign_id',
        'mustahik_id',
        'amount',
        'purpose',
        'notes',
        'proof_document',
        'status',
        'requested_by',
        'approved_by',
        'approved_at',
        'disbursed_at',
    ];

    protected $casts = [
        'amount' => 'integer',
        'status' => DisbursementRequestStatus::class,
        'approved_at' => 'datetime',
        'disbursed_at' => 'datetime',
    ];

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class)->withTrashed();
    }

    public function mustahik(): BelongsTo
    {
        return $this->belongsTo(Mustahik::class);
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public static function generateRequestNumber(): string
    {
        do {
            $number = 'REQ-'.now()->format('Ymd').'-'.strtoupper(Str::random(4));
        } while (static::where('request_number', $number)->exists());

        return $number;
    }

    public function approve(User $approver): void
    {
        if ($this->status !== DisbursementRequestStatus::Draft) {
            throw new RuntimeException("Pengajuan berstatus [{$this->status->label()}] tidak dapat disetujui.");
        }

        $this->update([
            'status' => DisbursementRequestStatus::Approved,
            'approved_by' => $approver->id,
            'approved_at' => now(),
        ]);
    }

    public function reject(User $approver, ?string $reason = null): void
    {
        if ($this->status === DisbursementRequestStatus::Disbursed) {
            throw new RuntimeException('Pengajuan yang sudah dicairkan tidak dapat ditolak.');
        }

        $this->update([
            'status' => DisbursementRequestStatus::Rejected,
            'approved_by' => $approver->id,
            'notes' => $reason ? ($this->notes ? $this->notes."\n[Alasan Ditolak: {$reason}]" : "Alasan Ditolak: {$reason}") : $this->notes,
        ]);
    }

    /**
     * Executes the disbursement atomically:
     * 1. Validates and deducts campaign balance.
     * 2. Creates the official Disbursement record.
     * 3. Records Islamic Fund Accounting (PSAK 109) journal entry.
     * 4. Updates request status to `disbursed`.
     */
    public function disburse(User $operator, ?string $proofDocument = null, ?string $notes = null): Disbursement
    {
        if (! in_array($this->status, [DisbursementRequestStatus::Approved, DisbursementRequestStatus::Draft], true)) {
            throw new RuntimeException("Pengajuan berstatus [{$this->status->label()}] tidak dapat dicairkan.");
        }

        return DB::transaction(function () use ($operator, $proofDocument, $notes) {
            // Lock campaign to prevent race conditions on balance
            $campaign = Campaign::withTrashed()->lockForUpdate()->findOrFail($this->campaign_id);
            $disbursed = (int) Disbursement::where('campaign_id', $campaign->id)->sum('amount');
            $remaining = $campaign->collected_amount - $disbursed;

            if ($this->amount > $remaining) {
                throw new InsufficientCampaignBalance($remaining, $this->amount);
            }

            // Create official disbursement record (model guard will also verify)
            $disbursement = Disbursement::create([
                'campaign_id' => $this->campaign_id,
                'mustahik_id' => $this->mustahik_id,
                'amount' => $this->amount,
                'disbursement_date' => now()->toDateString(),
                'proof_document' => $proofDocument ?? $this->proof_document,
                'notes' => $notes ?? $this->purpose,
                'created_by' => $operator->id,
            ]);

            // Update this request
            $this->update([
                'status' => DisbursementRequestStatus::Disbursed,
                'approved_by' => $this->approved_by ?? $operator->id,
                'approved_at' => $this->approved_at ?? now(),
                'disbursed_at' => now(),
                'proof_document' => $proofDocument ?? $this->proof_document,
            ]);

            // Auto-ledger entry in PSAK 109 Islamic Fund Accounting
            app(AccountingService::class)->recordDisbursement($disbursement);

            return $disbursement;
        });
    }
}
