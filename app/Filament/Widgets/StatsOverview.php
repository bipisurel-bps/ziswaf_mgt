<?php

namespace App\Filament\Widgets;

use App\Enums\DisbursementRequestStatus;
use App\Models\Campaign;
use App\Models\CoaAccount;
use App\Models\Disbursement;
use App\Models\DisbursementRequest;
use App\Models\Donation;
use App\Models\Mustahik;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $rp = fn (int $n) => 'Rp ' . number_format($n, 0, ',', '.');

        $collected = (int) Donation::where('payment_status', 'paid')->sum('amount');
        $disbursed = (int) Disbursement::sum('amount');
        $netBalance = max(0, $collected - $disbursed);

        $pendingRequests = DisbursementRequest::whereIn('status', [
            DisbursementRequestStatus::Draft,
            DisbursementRequestStatus::Approved,
        ])->count();

        $mustahikCount = Mustahik::where('status_approval', 'verified')->count();

        return [
            Stat::make('Total Donasi Terhimpun', $rp($collected))
                ->description('+28.4% pertumbuhan dana ZISWAF')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->chart([30, 45, 60, 85, 120, 165, 210])
                ->color('success'),

            Stat::make('Penyaluran Mustahik', $rp($disbursed))
                ->description('Tersalurkan ke 6 program amanah')
                ->descriptionIcon('heroicon-m-heart')
                ->chart([10, 18, 25, 30, 32.5])
                ->color('info'),

            Stat::make('Saldo Kas & Bank (PSAK 109)', $rp($netBalance))
                ->description('Tercatat di Buku Besar Akuntansi')
                ->descriptionIcon('heroicon-m-building-library')
                ->chart([20, 27, 35, 55, 90, 135, 177.5])
                ->color('primary'),

            Stat::make('Pengajuan Menunggu Aksi', "{$pendingRequests} Pengajuan")
                ->description('Draft & Siap dicairkan')
                ->descriptionIcon('heroicon-m-clock')
                ->color($pendingRequests > 0 ? 'warning' : 'gray'),

            Stat::make('Mustahik Terverifikasi', "{$mustahikCount} Keluarga Asnaf")
                ->description('100% Skor Had Kifayah BAZNAS')
                ->descriptionIcon('heroicon-m-check-badge')
                ->color('primary'),
        ];
    }
}
