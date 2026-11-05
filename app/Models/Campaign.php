<?php

namespace App\Models;

use App\Enums\CampaignCategory;
use App\Enums\CampaignStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Campaign extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title', 'slug', 'category', 'target_amount', 'collected_amount',
        'start_date', 'end_date', 'status', 'description', 'cover_image',
    ];

    protected $casts = [
        'category' => CampaignCategory::class,
        'status' => CampaignStatus::class,
        'target_amount' => 'integer',
        'collected_amount' => 'integer',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    protected static function booted(): void
    {
        static::creating(function (Campaign $c) {
            if (empty($c->slug)) {
                $base = Str::slug($c->title);
                $slug = $base;
                $i = 2;
                while (static::withTrashed()->where('slug', $slug)->exists()) {
                    $slug = $base.'-'.$i++;
                }
                $c->slug = $slug;
            }
        });
    }

    public function donations(): HasMany
    {
        return $this->hasMany(Donation::class);
    }

    public function disbursements(): HasMany
    {
        return $this->hasMany(Disbursement::class);
    }

    public function scopeActive(Builder $q): Builder
    {
        return $q->where('status', CampaignStatus::Active->value);
    }

    public function getTotalDisbursedAttribute(): int
    {
        return (int) $this->disbursements()->sum('amount');
    }

    public function getRemainingBalanceAttribute(): int
    {
        return max(0, $this->collected_amount - $this->total_disbursed);
    }

    /** Progress percent (0-100); null for open-ended campaigns. */
    public function getProgressPercentAttribute(): ?float
    {
        if (! $this->target_amount) {
            return null;
        }

        return min(100, round($this->collected_amount / $this->target_amount * 100, 1));
    }

    public function getCoverUrlAttribute(): ?string
    {
        return $this->cover_image ? asset('storage/'.$this->cover_image) : null;
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
