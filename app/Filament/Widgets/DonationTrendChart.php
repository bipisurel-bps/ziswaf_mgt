<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;

class DonationTrendChart extends ChartWidget
{
    protected static ?string $heading = 'Tren Penghimpunan vs Penyaluran Dana ZISWAF';

    protected static ?int $sort = 2;

    protected int | string | array $columnSpan = [
        'md' => 2,
        'xl' => 2,
    ];

    protected function getData(): array
    {
        return [
            'datasets' => [
                [
                    'label' => 'Donasi Masuk (Rp)',
                    'data' => [45_000_000, 68_000_000, 95_000_000, 140_000_000, 185_000_000, 245_000_000],
                    'borderColor' => '#2563eb', // Royal Blue
                    'backgroundColor' => 'rgba(37, 99, 235, 0.1)',
                    'fill' => true,
                    'tension' => 0.35,
                ],
                [
                    'label' => 'Dana Disalurkan (Rp)',
                    'data' => [15_000_000, 25_000_000, 32_000_000, 48_000_000, 65_000_000, 85_000_000],
                    'borderColor' => '#059669', // Emerald
                    'backgroundColor' => 'rgba(5, 150, 105, 0.1)',
                    'fill' => true,
                    'tension' => 0.35,
                ],
            ],
            'labels' => ['Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt'],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
