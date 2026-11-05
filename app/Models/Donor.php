<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Donor extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'phone_number', 'email', 'address', 'is_verified'];

    protected $casts = ['is_verified' => 'boolean'];

    public function donations(): HasMany
    {
        return $this->hasMany(Donation::class);
    }

    /** Normalize Indonesian phone numbers to 628xxx format. */
    public static function normalizePhone(?string $phone): ?string
    {
        if ($phone === null) {
            return null;
        }
        $digits = preg_replace('/\D+/', '', $phone);
        if ($digits === '') {
            return '';
        }
        if (str_starts_with($digits, '0')) {
            $digits = '62'.substr($digits, 1);
        } elseif (str_starts_with($digits, '8')) {
            $digits = '62'.$digits;
        }

        return $digits;
    }

    public function setPhoneNumberAttribute(?string $value): void
    {
        $this->attributes['phone_number'] = self::normalizePhone($value);
    }

    public function getTotalPaidAttribute(): int
    {
        return (int) $this->donations()->where('payment_status', 'paid')->sum('amount');
    }
}
