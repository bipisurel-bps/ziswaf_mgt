<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FundraiserResource\Pages;
use App\Models\Fundraiser;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class FundraiserResource extends Resource
{
    protected static ?string $model = Fundraiser::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationGroup = 'Fundraising';

    protected static ?string $modelLabel = 'Fundraiser';

    protected static ?string $pluralModelLabel = 'Fundraiser';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')->label('Nama')->required(),
            Forms\Components\TextInput::make('phone_number')->label('WhatsApp')->required()->tel(),
            Forms\Components\TextInput::make('referral_code')->label('Kode referral')->required()
                ->unique(ignoreRecord: true)->alphaDash()->maxLength(32)
                ->default(fn () => strtoupper(Str::random(8))),
            Forms\Components\TextInput::make('total_collected')->label('Total terkumpul')->prefix('Rp')
                ->disabled()->dehydrated(false)->default(0),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Nama')->searchable(),
                Tables\Columns\TextColumn::make('referral_code')->label('Kode')->copyable()->searchable(),
                Tables\Columns\TextColumn::make('referral_url')->label('Link referral')->copyable()->limit(35),
                Tables\Columns\TextColumn::make('total_collected')->label('Terkumpul')->money('IDR', locale: 'id')->sortable(),
            ])
            ->actions([Tables\Actions\EditAction::make()])
            ->defaultSort('total_collected', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFundraisers::route('/'),
            'create' => Pages\CreateFundraiser::route('/create'),
            'edit' => Pages\EditFundraiser::route('/{record}/edit'),
        ];
    }
}
