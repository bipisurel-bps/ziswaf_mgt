<?php

namespace App\Filament\Resources;

use App\Enums\PaymentStatus;
use App\Filament\Resources\DonationResource\Pages;
use App\Models\Donation;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

/**
 * Donations are financial records: created only by checkout and mutated only by the
 * payment webhook, so this resource is read-only (list + filters + CSV export).
 */
class DonationResource extends Resource
{
    protected static ?string $model = Donation::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationGroup = 'Fundraising';

    protected static ?string $modelLabel = 'Donasi';

    protected static ?string $pluralModelLabel = 'Donasi';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['donor', 'campaign', 'fundraiser']))
            ->columns([
                Tables\Columns\TextColumn::make('invoice_number')->label('Invoice')->searchable()->copyable(),
                Tables\Columns\TextColumn::make('donor.name')->label('Donatur')->searchable(),
                Tables\Columns\TextColumn::make('donor.phone_number')->label('WhatsApp')->toggleable(),
                Tables\Columns\TextColumn::make('campaign.title')->label('Kampanye')->default('Donasi Umum')->limit(30),
                Tables\Columns\TextColumn::make('amount')->label('Nominal')->money('IDR', locale: 'id')->sortable(),
                Tables\Columns\TextColumn::make('payment_method')->label('Metode')->toggleable(),
                Tables\Columns\TextColumn::make('payment_status')->label('Status')->badge()
                    ->color(fn ($state) => match ($state->value) {
                        'paid' => 'success', 'pending' => 'warning', 'expired' => 'gray', default => 'danger',
                    })->formatStateUsing(fn ($state) => $state->label()),
                Tables\Columns\TextColumn::make('fundraiser.name')->label('Fundraiser')->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\IconColumn::make('is_anonim')->label('Anonim')->boolean()->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('paid_at')->label('Dibayar')->dateTime('d M Y H:i')->sortable(),
                Tables\Columns\TextColumn::make('created_at')->label('Dibuat')->dateTime('d M Y H:i')->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('payment_status')->label('Status')->options(PaymentStatus::options()),
                Tables\Filters\Filter::make('created_range')->label('Rentang tanggal')
                    ->form([
                        Forms\Components\DatePicker::make('from')->label('Dari'),
                        Forms\Components\DatePicker::make('until')->label('Sampai'),
                    ])
                    ->query(fn (Builder $query, array $data) => $query
                        ->when($data['from'] ?? null, fn ($q, $d) => $q->whereDate('created_at', '>=', $d))
                        ->when($data['until'] ?? null, fn ($q, $d) => $q->whereDate('created_at', '<=', $d))),
                Tables\Filters\SelectFilter::make('campaign')->label('Kampanye')->relationship('campaign', 'title'),
            ])
            ->headerActions([
                Tables\Actions\Action::make('export')
                    ->label('Ekspor CSV (Excel)')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(fn (Pages\ListDonations $livewire) => static::exportCsv($livewire->getFilteredTableQuery())),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function exportCsv(Builder $query)
    {
        $filename = 'donasi-'.now()->format('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($query) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // UTF-8 BOM so Excel reads it correctly
            fputcsv($out, ['Invoice', 'Tanggal Dibuat', 'Tanggal Bayar', 'Donatur', 'WhatsApp', 'Kampanye', 'Jenis', 'Nominal', 'Metode', 'Status', 'Anonim', 'Fundraiser']);
            $query->with(['donor', 'campaign', 'fundraiser'])->reorder('id')->chunkById(500, function ($rows) use ($out) {
                foreach ($rows as $d) {
                    fputcsv($out, array_map([static::class, 'safeCell'], [
                        $d->invoice_number, $d->created_at, $d->paid_at, $d->donor->name, $d->donor->phone_number,
                        $d->campaign?->title ?? 'Donasi Umum', $d->donation_type, $d->amount,
                        $d->payment_method, $d->payment_status->value, $d->is_anonim ? 'ya' : 'tidak', $d->fundraiser?->name,
                    ]));
                }
            });
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /** Neutralize spreadsheet formula injection from user-supplied text. */
    public static function safeCell(mixed $v): mixed
    {
        if (is_string($v) && $v !== '' && in_array($v[0], ['=', '+', '-', '@', "\t", "\r"], true)) {
            return "'".$v;
        }

        return $v;
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ListDonations::route('/')];
    }
}
