<?php

namespace App\Enums;

enum FundCategory: string
{
    case Zakat = 'zakat';
    case InfaqTerikat = 'infaq_terikat';
    case InfaqBebas = 'infaq_bebas';
    case Wakaf = 'wakaf';
    case Amil = 'amil';
    case NonHalal = 'non_halal';

    public function label(): string
    {
        return match ($this) {
            self::Zakat => 'Dana Zakat',
            self::InfaqTerikat => 'Dana Infaq / Sedekah Terikat',
            self::InfaqBebas => 'Dana Infaq / Sedekah Tidak Terikat',
            self::Wakaf => 'Dana Wakaf',
            self::Amil => 'Dana Amil',
            self::NonHalal => 'Dana Non-Halal',
        };
    }

    /** @return array<string,string> */
    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn ($c) => [$c->value => $c->label()])->all();
    }
}
