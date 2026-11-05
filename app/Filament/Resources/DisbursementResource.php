<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DisbursementResource\Pages;
use App\Models\Campaign;
use App\Models\Disbursement;
use App\Models\Mustahik;
use App\Rules\WithinCampaignBalance;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class DisbursementResource extends Resource
{
    protected static ?string $model = Disbursement::class;

    protected static ?string $navigationIcon = 'heroicon-o-paper-airplane';

    protected static ?string $navigationGroup = 'Penyaluran';

    protected static ?string $modelLabel = 'Penyaluran';

    protected static ?string $pluralModelLabel = 'Penyaluran';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('campaign_id')->label('Kampanye')->required()->live()->searchable()
                ->options(fn () => Campaign::orderBy('title')->get()->mapWithKeys(fn (Campaign $c) => [
                    $c->id => $c->title.' — sisa Rp '.number_format($c->remaining_balance, 0, ',', '.'),
                ])),
            Forms\Components\Select::make('mustahik_id')->label('Mustahik (terverifikasi)')->required()->searchable()
                ->options(fn () => Mustahik::where('status_approval', 'verified')->orderBy('name')->pluck('name', 'id')),
            Forms\Components\TextInput::make('amount')->label('Nominal')->required()->numeric()->minValue(1)->prefix('Rp')
                ->rules(fn (Forms\Get $get, ?Disbursement $record) => [
                    new WithinCampaignBalance($get('campaign_id') ? (int) $get('campaign_id') : null, $record?->id),
                ])
                ->helperText(function (Forms\Get $get) {
                    $c = $get('campaign_id') ? Campaign::withTrashed()->find($get('campaign_id')) : null;

                    return $c ? 'Sisa dana: Rp '.number_format($c->remaining_balance, 0, ',', '.') : 'Pilih kampanye untuk melihat sisa dana.';
                }),
            Forms\Components\DatePicker::make('disbursement_date')->label('Tanggal penyaluran')->required()->default(now())->maxDate(now()),
            Forms\Components\FileUpload::make('proof_document')->label('Bukti (foto / berita acara)')
                ->disk('local')->directory('disbursement-proofs')->visibility('private')
                ->acceptedFileTypes(['image/jpeg', 'image/png', 'application/pdf'])->maxSize(5120)->downloadable()->columnSpanFull(),
            Forms\Components\Textarea::make('notes')->label('Catatan')->columnSpanFull(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (\Illuminate\Database\Eloquent\Builder $query) => $query->with(['campaign', 'mustahik', 'creator']))
            ->columns([
                Tables\Columns\TextColumn::make('disbursement_date')->label('Tanggal')->date('d M Y')->sortable(),
                Tables\Columns\TextColumn::make('campaign.title')->label('Kampanye')->limit(30)->searchable(),
                Tables\Columns\TextColumn::make('mustahik.name')->label('Mustahik')->searchable(),
                Tables\Columns\TextColumn::make('amount')->label('Nominal')->money('IDR', locale: 'id')->sortable(),
                Tables\Columns\IconColumn::make('proof_document')->label('Bukti')->boolean()->trueIcon('heroicon-o-paper-clip')->falseIcon('heroicon-o-x-mark'),
                Tables\Columns\TextColumn::make('creator.name')->label('Dicatat oleh')->toggleable(),
            ])
            ->filters([Tables\Filters\SelectFilter::make('campaign_id')->label('Kampanye')->relationship('campaign', 'title')])
            ->actions([Tables\Actions\EditAction::make()])
            ->defaultSort('disbursement_date', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDisbursements::route('/'),
            'create' => Pages\CreateDisbursement::route('/create'),
            'edit' => Pages\EditDisbursement::route('/{record}/edit'),
        ];
    }
}
