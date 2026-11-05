<?php

namespace App\Models;

use App\Enums\ApprovalStatus;
use App\Enums\AsnafCategory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Mustahik extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'nik',
        'family_card_number',
        'asnaf_category',
        'income_per_month',
        'dependents_count',
        'had_kifayah_score',
        'phone',
        'address',
        'status_approval',
        'verification_status',
    ];

    protected $hidden = ['nik', 'nik_hash'];

    protected $casts = [
        'nik' => 'encrypted',
        'asnaf_category' => AsnafCategory::class,
        'status_approval' => ApprovalStatus::class,
        'income_per_month' => 'integer',
        'dependents_count' => 'integer',
        'had_kifayah_score' => 'float',
    ];

    protected static function booted(): void
    {
        static::saving(function (Mustahik $m) {
            // Keep status_approval and verification_status in sync
            if ($m->isDirty('status_approval') && ! $m->isDirty('verification_status')) {
                $m->verification_status = $m->status_approval->value;
            } elseif ($m->isDirty('verification_status') && ! $m->isDirty('status_approval')) {
                $m->status_approval = ApprovalStatus::tryFrom($m->verification_status) ?? ApprovalStatus::Pending;
            }

            // Auto calculate had_kifayah_score if income or dependents are set/dirty
            if ($m->isDirty(['income_per_month', 'dependents_count']) || $m->had_kifayah_score === null) {
                $calc = app(\App\Services\HadKifayahCalculatorService::class)
                    ->calculate((int) $m->income_per_month, (int) $m->dependents_count);
                $m->had_kifayah_score = $calc['score'];
            }
        });
    }

    /** Keep a keyed hash of the NIK for uniqueness/lookup without decrypting. */
    public function setNikAttribute(?string $value): void
    {
        if ($value === null || $value === '') {
            $this->attributes['nik'] = null;
            $this->attributes['nik_hash'] = null;

            return;
        }
        $this->attributes['nik'] = encrypt($value, false);
        $this->attributes['nik_hash'] = self::hashNik($value);
    }

    public static function hashNik(string $nik): string
    {
        return hash_hmac('sha256', preg_replace('/\D+/', '', $nik), config('app.key'));
    }

    /** NIK masked for display, e.g. 3174************12 */
    public function getMaskedNikAttribute(): ?string
    {
        $nik = $this->nik;

        return $nik ? substr($nik, 0, 4).str_repeat('*', max(0, strlen($nik) - 6)).substr($nik, -2) : null;
    }

    public function disbursements(): HasMany
    {
        return $this->hasMany(Disbursement::class);
    }

    public function disbursementRequests(): HasMany
    {
        return $this->hasMany(DisbursementRequest::class);
    }
}
