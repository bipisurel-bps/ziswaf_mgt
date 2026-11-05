<?php

namespace App\Models;

use App\Enums\CoaAccountType;
use App\Enums\FundCategory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CoaAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'type',
        'fund_category',
        'description',
        'is_active',
    ];

    protected $casts = [
        'type' => CoaAccountType::class,
        'fund_category' => FundCategory::class,
        'is_active' => 'boolean',
    ];

    public function journalEntryItems(): HasMany
    {
        return $this->hasMany(JournalEntryItem::class);
    }

    public static function findByCode(string $code): ?self
    {
        return static::where('code', $code)->first();
    }

    public function getFullTitleAttribute(): string
    {
        return "[{$this->code}] {$this->name} ({$this->fund_category->label()})";
    }
}
