<?php

namespace App\Enums;

enum AsnafCategory: string
{
    case Fakir = 'fakir';
    case Miskin = 'miskin';
    case Amil = 'amil';
    case Mualaf = 'mualaf';
    case Riqab = 'riqab';
    case Gharim = 'gharim';
    case Fisabilillah = 'fisabilillah';
    case IbnuSabil = 'ibnu_sabil';

    public function label(): string
    {
        return match ($this) {
            self::IbnuSabil => 'Ibnu Sabil',
            default => ucfirst($this->value),
        };
    }

    /** @return array<string,string> */
    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn ($c) => [$c->value => $c->label()])->all();
    }
}
