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

    protected $fillable = ['name', 'nik', 'asnaf_category', 'phone', 'address', 'status_approval'];

    protected $hidden = ['nik', 'nik_hash'];

    protected $casts = [
        'nik' => 'encrypted',
        'asnaf_category' => AsnafCategory::class,
        'status_approval' => ApprovalStatus::class,
    ];

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
}
