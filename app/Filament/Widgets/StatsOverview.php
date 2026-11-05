<?php

namespace App\Filament\Widgets;

use App\Models\Campaign;
use App\Models\Disbursement;
use App\Models\Donation;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $rp = fn (int $n) => 'Rp '.number_format($n, 0, ',', '.');
        $collected = (int) Donation::where('payment_status', 'paid')->sum('amount');
        $disbursed = (int) Disbursement::sum('amount');

        return [
            Stat::make('Total donasi terkonfirmasi', $rp($collected)),
            Stat::make('Total disalurkan', $rp($disbursed)),
            Stat::make('Donasi menunggu bayar', Donation::where('payment_status', 'pending')->count()),
            Stat::make('Kampanye aktif', Campaign::active()->count()),
        ];
    }
}
