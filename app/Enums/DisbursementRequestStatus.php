<?php

namespace App\Enums;

enum DisbursementRequestStatus: string
{
    case Draft = 'draft';
    case Approved = 'approved';
    case Disbursed = 'disbursed';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft / Pengajuan',
            self::Approved => 'Disetujui',
            self::Disbursed => 'Dicairkan (Selesai)',
            self::Rejected => 'Ditolak',
        };
    }

    /** @return array<string,string> */
    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn ($c) => [$c->value => $c->label()])->all();
    }
}
