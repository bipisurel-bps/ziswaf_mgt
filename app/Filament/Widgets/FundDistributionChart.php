<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;

class FundDistributionChart extends ChartWidget
{
    protected static ?string $heading = 'Komposisi Dana Berdasarkan Akad Syariah (PSAK 109)';

    protected static ?int $sort = 3;

    protected int | string | array $columnSpan = [
        'md' => 1,
        'xl' => 1,
    ];

    protected function getData(): array
    {
        return [
            'datasets' => [
                [
                    'label' => 'Total Dana (Rp)',
                    'data' => [185_000_000, 150_000_000, 420_000_000, 112_500_000],
                    'backgroundColor' => [
                        '#d97706', // Amber (Zakat)
                        '#2563eb', // Royal Blue (Infaq)
                        '#0d9488', // Teal (Wakaf)
                        '#e11d48', // Rose (Kemanusiaan)
                    ],
                ],
            ],
            'labels' => [
                'Zakat Maal & Fitrah',
                'Infaq & Sedekah',
                'Wakaf Uang Produktif',
                'Tanggap Kemanusiaan',
            ],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
