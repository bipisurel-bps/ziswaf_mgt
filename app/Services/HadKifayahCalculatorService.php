<?php

namespace App\Services;

class HadKifayahCalculatorService
{
    /** Base monthly living cost for head of family (Rupiah). */
    public const DEFAULT_BASE_COST = 3_000_000;

    /** Additional living cost per dependent per month (Rupiah). */
    public const DEFAULT_COST_PER_DEPENDENT = 1_000_000;

    /**
     * Calculate Had Kifayah (Poverty & Sufficiency Line) for a household.
     *
     * @param  int  $incomePerMonth  Household monthly income in IDR
     * @param  int  $dependentsCount  Number of family dependents
     * @return array{
     *     base_kifayah: int,
     *     dependents_kifayah: int,
     *     total_had_kifayah: int,
     *     income: int,
     *     score: float,
     *     score_percent: float,
     *     category: string,
     *     is_eligible: bool
     * }
     */
    public function calculate(int $incomePerMonth, int $dependentsCount = 0): array
    {
        $dependentsCount = max(0, $dependentsCount);
        $baseKifayah = (int) config('ziswaf.had_kifayah.base_cost', self::DEFAULT_BASE_COST);
        $costPerDependent = (int) config('ziswaf.had_kifayah.cost_per_dependent', self::DEFAULT_COST_PER_DEPENDENT);

        $dependentsKifayah = $dependentsCount * $costPerDependent;
        $totalHadKifayah = $baseKifayah + $dependentsKifayah;

        $score = $totalHadKifayah > 0 ? round($incomePerMonth / $totalHadKifayah, 4) : 0.0;
        $scorePercent = round($score * 100, 2);

        $category = match (true) {
            $score < 0.50 => 'fakir',
            $score <= 1.00 => 'miskin',
            default => 'kifayah_terpenuhi',
        };

        $isEligible = in_array($category, ['fakir', 'miskin'], true);

        return [
            'base_kifayah' => $baseKifayah,
            'dependents_kifayah' => $dependentsKifayah,
            'total_had_kifayah' => $totalHadKifayah,
            'income' => $incomePerMonth,
            'score' => $score,
            'score_percent' => $scorePercent,
            'category' => $category,
            'is_eligible' => $isEligible,
        ];
    }
}
