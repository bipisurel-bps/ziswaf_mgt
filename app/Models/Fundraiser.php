<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Fundraiser extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'phone_number', 'referral_code', 'total_collected'];

    protected $casts = ['total_collected' => 'integer'];

    public function donations(): HasMany
    {
        return $this->hasMany(Donation::class);
    }

    public function setReferralCodeAttribute(string $value): void
    {
        $this->attributes['referral_code'] = strtoupper(trim($value));
    }

    public function setPhoneNumberAttribute(?string $value): void
    {
        $this->attributes['phone_number'] = Donor::normalizePhone($value);
    }

    public function getReferralUrlAttribute(): string
    {
        return url('/?ref='.$this->referral_code);
    }
}
