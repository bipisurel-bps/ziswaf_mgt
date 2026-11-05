<?php

namespace App\Filament\Widgets;

use App\Enums\DisbursementRequestStatus;
use App\Filament\Resources\DisbursementRequestResource;
use App\Models\DisbursementRequest;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class PendingDisbursementsWidget extends BaseWidget
{
    protected static ?string $heading = 'Pengajuan Penyaluran Menunggu Tindakan (Approval / Pencairan)';

    protected static ?int $sort = 4;

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                DisbursementRequest::query()
                    ->with(['campaign', 'mustahik'])
                    ->whereIn('status', [DisbursementRequestStatus::Draft, DisbursementRequestStatus::Approved])
                    ->latest()
            )
            ->columns([
                Tables\Columns\TextColumn::make('request_number')
                    ->label('No. Pengajuan')
                    ->fontFamily('mono')
                    ->searchable(),
                Tables\Columns\TextColumn::make('campaign.title')
                    ->label('Program / Kampanye')
                    ->limit(30),
                Tables\Columns\TextColumn::make('mustahik.name')
                    ->label('Mustahik Penerima'),
                Tables\Columns\TextColumn::make('amount')
                    ->label('Nominal')
                    ->money('IDR', locale: 'id')
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (DisbursementRequestStatus $state) => match ($state) {
                        DisbursementRequestStatus::Draft => 'warning',
                        DisbursementRequestStatus::Approved => 'info',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (DisbursementRequestStatus $state) => $state->label()),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Diajukan')
                    ->dateTime('d M Y H:i'),
            ])
            ->actions([
                Tables\Actions\Action::make('process')
                    ->label('Proses di Penyaluran')
                    ->icon('heroicon-m-arrow-top-right-on-square')
                    ->url(fn () => DisbursementRequestResource::getUrl('index')),
            ])
            ->paginated(false);
    }
}
