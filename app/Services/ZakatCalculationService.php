<?php

namespace App\Services;

use App\Models\Setting;

class ZakatCalculationService
{
    public function goldPricePerGram(): int
    {
        return (int) Setting::get('gold_price_per_gram', config('ziswaf.gold_price_per_gram'));
    }

    public function nishabYearly(): int
    {
        return (int) round(config('ziswaf.nishab_gold_grams') * $this->goldPricePerGram());
    }

    public function nishabMonthly(): int
    {
        return (int) round($this->nishabYearly() / 12);
    }

    /**
     * Zakat penghasilan: (penghasilan bulanan + bonus) - pengeluaran pokok.
     * Wajib bila hasil >= nishab/12.
     *
     * @return array{base:int,nishab:int,obligatory:bool,zakat:int}
     */
    public function profession(int $monthlyIncome, int $bonus = 0, int $basicExpenses = 0): array
    {
        $base = max(0, $monthlyIncome + $bonus - $basicExpenses);
        $nishab = $this->nishabMonthly();

        return $this->result($base, $nishab);
    }

    /**
     * Zakat maal: total aset tersimpan 1 tahun (haul). Wajib bila >= nishab emas 85 g.
     *
     * @return array{base:int,nishab:int,obligatory:bool,zakat:int}
     */
    public function maal(int $totalAssets, int $debts = 0, bool $haulReached = true): array
    {
        $base = max(0, $totalAssets - $debts);
        $nishab = $this->nishabYearly();

        $result = $this->result($base, $nishab);
        if (! $haulReached) {
            $result['obligatory'] = false;
            $result['zakat'] = 0;
        }

        return $result;
    }

    private function result(int $base, int $nishab): array
    {
        $obligatory = $base >= $nishab && $nishab > 0;

        return [
            'base' => $base,
            'nishab' => $nishab,
            'obligatory' => $obligatory,
            'zakat' => $obligatory ? (int) floor($base * config('ziswaf.zakat_rate')) : 0,
        ];
    }
}
