<?php

namespace App\Models;

use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Donation extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_number', 'campaign_id', 'donor_id', 'amount', 'unique_code', 'donation_type',
        'payment_method', 'payment_gateway_ref', 'snap_token', 'payment_status', 'paid_at',
        'doa_harapan', 'is_anonim', 'fundraiser_id',
    ];

    protected $casts = [
        'amount' => 'integer',
        'unique_code' => 'integer',
        'payment_status' => PaymentStatus::class,
        'paid_at' => 'datetime',
        'is_anonim' => 'boolean',
    ];

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class)->withTrashed();
    }

    public function donor(): BelongsTo
    {
        return $this->belongsTo(Donor::class);
    }

    public function fundraiser(): BelongsTo
    {
        return $this->belongsTo(Fundraiser::class);
    }

    public function getIsPaidAttribute(): bool
    {
        return $this->payment_status === PaymentStatus::Paid;
    }

    /** Name shown publicly / on receipts. */
    public function getDisplayNameAttribute(): string
    {
        return $this->is_anonim ? 'Hamba Allah' : $this->donor->name;
    }

    public function getTotalChargeAttribute(): int
    {
        return $this->amount + $this->unique_code;
    }

    /**
     * Generate a unique invoice number: INV-YYYYMMDD-XXXX.
     */
    public static function generateInvoiceNumber(): string
    {
        do {
            $number = 'INV-'.now()->format('Ymd').'-'.strtoupper(\Illuminate\Support\Str::random(4));
        } while (static::where('invoice_number', $number)->exists());

        return $number;
    }
}
