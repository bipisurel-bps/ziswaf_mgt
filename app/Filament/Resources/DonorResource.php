<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DonorResource\Pages;
use App\Models\Donor;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class DonorResource extends Resource
{
    protected static ?string $model = Donor::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationGroup = 'CRM';

    protected static ?string $modelLabel = 'Donatur';

    protected static ?string $pluralModelLabel = 'Donatur';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')->label('Nama')->required()->maxLength(255),
            Forms\Components\TextInput::make('phone_number')->label('WhatsApp (628xxx)')->required()->unique(ignoreRecord: true)->tel(),
            Forms\Components\TextInput::make('email')->email(),
            Forms\Components\Toggle::make('is_verified')->label('Terverifikasi'),
            Forms\Components\Textarea::make('address')->label('Alamat')->columnSpanFull(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (\Illuminate\Database\Eloquent\Builder $query) => $query->withCount(['donations as paid_count' => fn ($q) => $q->where('payment_status', 'paid')])
                ->withSum(['donations as paid_total' => fn ($q) => $q->where('payment_status', 'paid')], 'amount'))
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Nama')->searchable(),
                Tables\Columns\TextColumn::make('phone_number')->label('WhatsApp')->searchable(),
                Tables\Columns\TextColumn::make('email')->toggleable(),
                Tables\Columns\TextColumn::make('paid_count')->label('Jml donasi')->sortable(),
                Tables\Columns\TextColumn::make('paid_total')->label('Total donasi')->money('IDR', locale: 'id')->sortable(),
                Tables\Columns\IconColumn::make('is_verified')->label('Verif')->boolean(),
            ])
            ->filters([Tables\Filters\TernaryFilter::make('is_verified')->label('Terverifikasi')])
            ->actions([Tables\Actions\EditAction::make()])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDonors::route('/'),
            'create' => Pages\CreateDonor::route('/create'),
            'edit' => Pages\EditDonor::route('/{record}/edit'),
        ];
    }
}
