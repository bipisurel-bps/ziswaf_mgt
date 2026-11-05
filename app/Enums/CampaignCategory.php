<?php

namespace App\Enums;

enum CampaignCategory: string
{
    case Zakat = 'zakat';
    case Infaq = 'infaq';
    case Wakaf = 'wakaf';
    case Kemanusiaan = 'kemanusiaan';

    public function label(): string
    {
        return ucfirst($this->value);
    }

    /** @return array<string,string> */
    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn ($c) => [$c->value => $c->label()])->all();
    }
}
