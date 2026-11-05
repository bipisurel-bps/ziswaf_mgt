<?php

namespace App\Filament\Resources;

use App\Enums\ApprovalStatus;
use App\Enums\AsnafCategory;
use App\Filament\Resources\MustahikResource\Pages;
use App\Models\Mustahik;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class MustahikResource extends Resource
{
    protected static ?string $model = Mustahik::class;

    protected static ?string $navigationIcon = 'heroicon-o-heart';

    protected static ?string $navigationGroup = 'Penyaluran';

    protected static ?string $modelLabel = 'Mustahik';

    protected static ?string $pluralModelLabel = 'Mustahik';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Identitas Mustahik')
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->label('Nama Lengkap')
                        ->required()
                        ->maxLength(255),
                    Forms\Components\TextInput::make('nik')
                        ->label('NIK (16 digit, enkripsi)')
                        ->length(16)
                        ->numeric()
                        ->rules([
                            fn (?Mustahik $record) => function (string $attr, $value, \Closure $fail) use ($record) {
                                $dup = Mustahik::where('nik_hash', Mustahik::hashNik((string) $value))
                                    ->when($record, fn ($q) => $q->whereKeyNot($record->id))->exists();
                                if ($dup) {
                                    $fail('NIK sudah terdaftar.');
                                }
                            },
                        ]),
                    Forms\Components\TextInput::make('family_card_number')
                        ->label('Nomor Kartu Keluarga (No KK)')
                        ->length(16)
                        ->numeric()
                        ->nullable(),
                    Forms\Components\TextInput::make('phone')
                        ->label('No. Telepon / WhatsApp')
                        ->tel()
                        ->maxLength(20),
                    Forms\Components\Select::make('asnaf_category')
                        ->label('Kategori Asnaf')
                        ->options(AsnafCategory::options())
                        ->required(),
                    Forms\Components\Select::make('status_approval')
                        ->label('Status Verifikasi')
                        ->options(ApprovalStatus::options())
                        ->default('pending')
                        ->required(),
                    Forms\Components\Textarea::make('address')
                        ->label('Alamat Domisili')
                        ->columnSpanFull(),
                ])->columns(2),

            Forms\Components\Section::make('Penilaian Kerentanan Ekonomi (Had Kifayah)')
                ->description('Kebutuhan dasar minimum rumah tangga menurut standar BAZNAS')
                ->schema([
                    Forms\Components\TextInput::make('income_per_month')
                        ->label('Penghasilan Bulanan (Rp)')
                        ->prefix('Rp')
                        ->numeric()
                        ->default(0)
                        ->live(debounce: 500)
                        ->helperText('Total pemasukan bulanan seluruh anggota keluarga yang bekerja'),
                    Forms\Components\TextInput::make('dependents_count')
                        ->label('Jumlah Tanggungan (Orang)')
                        ->numeric()
                        ->default(0)
                        ->minValue(0)
                        ->live(debounce: 500)
                        ->helperText('Jumlah anggota keluarga dalam tanggungan selain kepala keluarga'),
                    Forms\Components\Placeholder::make('had_kifayah_preview')
                        ->label('Hasil Analisis Had Kifayah')
                        ->content(function (Forms\Get $get) {
                            $income = (int) ($get('income_per_month') ?? 0);
                            $dependents = (int) ($get('dependents_count') ?? 0);
                            $calc = app(\App\Services\HadKifayahCalculatorService::class)->calculate($income, $dependents);

                            $categoryLabel = match ($calc['category']) {
                                'fakir' => '🔴 Fakir (Penghasilan < 50% Had Kifayah) - Sangat Prioritas',
                                'miskin' => '🟡 Miskin (Penghasilan 50% - 100% Had Kifayah) - Berhak Menerima',
                                default => '🟢 Kifayah Terpenuhi (Penghasilan > 100% Had Kifayah) - Mampu',
                            };

                            return sprintf(
                                "Total Had Kifayah: Rp %s | Rasio Pemenuhan: %s%% \nKategori Rekomendasi: %s",
                                number_format($calc['total_had_kifayah'], 0, ',', '.'),
                                $calc['score_percent'],
                                $categoryLabel
                            );
                        })
                        ->columnSpanFull(),
                ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Nama')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('masked_nik')->label('NIK'),
                Tables\Columns\TextColumn::make('family_card_number')->label('No KK')->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('asnaf_category')->label('Asnaf')->badge()->formatStateUsing(fn ($state) => $state->label()),
                Tables\Columns\TextColumn::make('income_per_month')->label('Penghasilan')
                    ->money('IDR', locale: 'id')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('had_kifayah_score')->label('Skor Kifayah')
                    ->formatStateUsing(function ($state) {
                        if ($state === null) {
                            return '-';
                        }
                        $pct = number_format($state * 100, 1) . '%';
                        if ($state < 0.50) {
                            return "🔴 {$pct} (Fakir)";
                        }
                        if ($state <= 1.00) {
                            return "🟡 {$pct} (Miskin)";
                        }
                        return "🟢 {$pct} (Mampu)";
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('status_approval')->label('Status')->badge()
                    ->color(fn ($state) => match ($state->value) {'verified' => 'success', 'rejected' => 'danger', default => 'warning'})
                    ->formatStateUsing(fn ($state) => $state->label()),
                Tables\Columns\TextColumn::make('phone')->label('Telepon')->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('asnaf_category')->label('Asnaf')->options(AsnafCategory::options()),
                Tables\Filters\SelectFilter::make('status_approval')->label('Status')->options(ApprovalStatus::options()),
            ])
            ->actions([Tables\Actions\EditAction::make()])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMustahiks::route('/'),
            'create' => Pages\CreateMustahik::route('/create'),
            'edit' => Pages\EditMustahik::route('/{record}/edit'),
        ];
    }
}
