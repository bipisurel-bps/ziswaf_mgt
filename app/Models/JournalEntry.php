<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Str;

class JournalEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'entry_number',
        'entry_date',
        'reference_type',
        'reference_id',
        'description',
        'status',
    ];

    protected $casts = [
        'entry_date' => 'date',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(JournalEntryItem::class);
    }

    public function reference(): MorphTo
    {
        return $this->morphTo();
    }

    public function getTotalDebitAttribute(): int
    {
        return (int) $this->items->sum('debit');
    }

    public function getTotalCreditAttribute(): int
    {
        return (int) $this->items->sum('credit');
    }

    public function getIsBalancedAttribute(): bool
    {
        return $this->total_debit === $this->total_credit && $this->total_debit > 0;
    }

    public static function generateEntryNumber(): string
    {
        do {
            $number = 'JE-'.now()->format('Ymd').'-'.strtoupper(Str::random(4));
        } while (static::where('entry_number', $number)->exists());

        return $number;
    }
}
