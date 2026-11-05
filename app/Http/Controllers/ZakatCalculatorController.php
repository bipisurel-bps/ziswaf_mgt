<?php

namespace App\Http\Controllers;

use App\Services\ZakatCalculationService;

class ZakatCalculatorController extends Controller
{
    public function __invoke(ZakatCalculationService $zakat)
    {
        return view('zakat.calculator', [
            'goldPrice' => $zakat->goldPricePerGram(),
            'nishabYearly' => $zakat->nishabYearly(),
            'nishabMonthly' => $zakat->nishabMonthly(),
            'rate' => config('ziswaf.zakat_rate'),
        ]);
    }
}
