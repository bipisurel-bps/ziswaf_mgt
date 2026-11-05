<?php

namespace App\Filament\Widgets;

use App\Enums\PaymentStatus;
use App\Models\Donation;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestDonationsWidget extends BaseWidget
{
    protected static ?string $heading = 'Transaksi Donasi Masuk Terkini (Real-Time)';

    protected static ?int $sort = 5;

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Donation::query()
                    ->with(['donor', 'campaign'])
                    ->latest('created_at')
                    ->limit(8)
            )
            ->columns([
                Tables\Columns\TextColumn::make('invoice_number')
                    ->label('Invoice')
                    ->fontFamily('mono')
                    ->searchable(),
                Tables\Columns\TextColumn::make('display_name')
                    ->label('Donatur'),
                Tables\Columns\TextColumn::make('campaign.title')
                    ->label('Program / Kampanye')
                    ->limit(28)
                    ->default('Donasi Umum'),
                Tables\Columns\TextColumn::make('amount')
                    ->label('Nominal')
                    ->money('IDR', locale: 'id')
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('payment_method')
                    ->label('Metode')
                    ->badge()
                    ->color('gray'),
                Tables\Columns\TextColumn::make('payment_status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (PaymentStatus $state) => match ($state) {
                        PaymentStatus::Paid => 'success',
                        PaymentStatus::Pending => 'warning',
                        default => 'danger',
                    })
                    ->formatStateUsing(fn (PaymentStatus $state) => $state->label()),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Waktu')
                    ->dateTime('d M Y H:i'),
            ])
            ->paginated(false);
    }
}
