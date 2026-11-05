<?php

namespace App\Enums;

enum CoaAccountType: string
{
    case Asset = 'asset';
    case Liability = 'liability';
    case Equity = 'equity';
    case Revenue = 'revenue';
    case Expense = 'expense';

    public function label(): string
    {
        return match ($this) {
            self::Asset => 'Aset / Kas',
            self::Liability => 'Kewajiban',
            self::Equity => 'Saldo Dana (Ekuitas)',
            self::Revenue => 'Pendapatan',
            self::Expense => 'Beban / Penyaluran',
        };
    }

    /** @return array<string,string> */
    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn ($c) => [$c->value => $c->label()])->all();
    }
}
