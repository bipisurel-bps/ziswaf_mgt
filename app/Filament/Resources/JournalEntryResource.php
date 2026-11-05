<?php

namespace App\Filament\Resources;

use App\Filament\Resources\JournalEntryResource\Pages;
use App\Models\CoaAccount;
use App\Models\JournalEntry;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class JournalEntryResource extends Resource
{
    protected static ?string $model = JournalEntry::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-currency-dollar';

    protected static ?string $navigationGroup = 'Akuntansi (PSAK 109)';

    protected static ?string $modelLabel = 'Jurnal Umum';

    protected static ?string $pluralModelLabel = 'Jurnal Umum (General Ledger)';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Informasi Transaksi Jurnal')
                ->schema([
                    Forms\Components\TextInput::make('entry_number')
                        ->label('Nomor Bukti Jurnal')
                        ->default(fn () => JournalEntry::generateEntryNumber())
                        ->required()
                        ->unique(ignoreRecord: true),
                    Forms\Components\DatePicker::make('entry_date')
                        ->label('Tanggal Jurnal')
                        ->default(now())
                        ->required(),
                    Forms\Components\Textarea::make('description')
                        ->label('Keterangan Transaksi')
                        ->required()
                        ->columnSpanFull(),
                ])->columns(2),

            Forms\Components\Section::make('Rincian Pos Debit & Kredit')
                ->schema([
                    Forms\Components\Repeater::make('items')
                        ->relationship('items')
                        ->schema([
                            Forms\Components\Select::make('coa_account_id')
                                ->label('Akun COA')
                                ->options(fn () => CoaAccount::where('is_active', true)->get()->pluck('full_title', 'id'))
                                ->searchable()
                                ->required()
                                ->columnSpan(6),
                            Forms\Components\TextInput::make('debit')
                                ->label('Debit')
                                ->numeric()
                                ->default(0)
                                ->prefix('Rp')
                                ->columnSpan(3),
                            Forms\Components\TextInput::make('credit')
                                ->label('Kredit')
                                ->numeric()
                                ->default(0)
                                ->prefix('Rp')
                                ->columnSpan(3),
                        ])
                        ->columns(12)
                        ->minItems(2)
                        ->defaultItems(2)
                        ->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (\Illuminate\Database\Eloquent\Builder $query) => $query->with('items.coaAccount'))
            ->columns([
                Tables\Columns\TextColumn::make('entry_number')
                    ->label('No. Bukti')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('entry_date')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('description')
                    ->label('Keterangan')
                    ->limit(45)
                    ->searchable(),
                Tables\Columns\TextColumn::make('total_debit')
                    ->label('Total Debit')
                    ->money('IDR', locale: 'id'),
                Tables\Columns\TextColumn::make('total_credit')
                    ->label('Total Kredit')
                    ->money('IDR', locale: 'id'),
                Tables\Columns\IconColumn::make('is_balanced')
                    ->label('Balance')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-exclamation-triangle'),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'posted' => 'success',
                        'draft' => 'warning',
                        default => 'danger',
                    }),
            ])
            ->filters([
                Tables\Filters\Filter::make('entry_date')
                    ->form([
                        Forms\Components\DatePicker::make('from')->label('Dari Tanggal'),
                        Forms\Components\DatePicker::make('until')->label('Sampai Tanggal'),
                    ])
                    ->query(fn (\Illuminate\Database\Eloquent\Builder $query, array $data) => $query
                        ->when($data['from'] ?? null, fn ($q, $d) => $q->whereDate('entry_date', '>=', $d))
                        ->when($data['until'] ?? null, fn ($q, $d) => $q->whereDate('entry_date', '<=', $d))),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->defaultSort('entry_date', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListJournalEntries::route('/'),
            'create' => Pages\CreateJournalEntry::route('/create'),
            'edit' => Pages\EditJournalEntry::route('/{record}/edit'),
        ];
    }
}
