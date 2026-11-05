<?php

namespace App\Filament\Resources;

use App\Enums\CoaAccountType;
use App\Enums\FundCategory;
use App\Filament\Resources\CoaAccountResource\Pages;
use App\Models\CoaAccount;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CoaAccountResource extends Resource
{
    protected static ?string $model = CoaAccount::class;

    protected static ?string $navigationIcon = 'heroicon-o-book-open';

    protected static ?string $navigationGroup = 'Akuntansi (PSAK 109)';

    protected static ?string $modelLabel = 'Bagan Akun (COA)';

    protected static ?string $pluralModelLabel = 'Bagan Akun (COA)';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('code')
                ->label('Kode Akun')
                ->required()
                ->unique(ignoreRecord: true)
                ->maxLength(32)
                ->placeholder('Contoh: 1-1001'),
            Forms\Components\TextInput::make('name')
                ->label('Nama Akun')
                ->required()
                ->maxLength(255)
                ->placeholder('Contoh: Kas Payment Gateway'),
            Forms\Components\Select::make('type')
                ->label('Klasifikasi Akun')
                ->options(CoaAccountType::options())
                ->required(),
            Forms\Components\Select::make('fund_category')
                ->label('Kategori Dana (PSAK 109)')
                ->options(FundCategory::options())
                ->required(),
            Forms\Components\Textarea::make('description')
                ->label('Keterangan / Peruntukan Akun')
                ->columnSpanFull(),
            Forms\Components\Toggle::make('is_active')
                ->label('Status Aktif')
                ->default(true),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->label('Kode')
                    ->sortable()
                    ->searchable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Akun')
                    ->searchable(),
                Tables\Columns\TextColumn::make('type')
                    ->label('Klasifikasi')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state->label()),
                Tables\Columns\TextColumn::make('fund_category')
                    ->label('Kategori Dana')
                    ->badge()
                    ->color(fn ($state) => match ($state->value) {
                        'zakat' => 'success',
                        'infaq_terikat', 'infaq_bebas' => 'info',
                        'wakaf' => 'warning',
                        'amil' => 'gray',
                        default => 'danger',
                    })
                    ->formatStateUsing(fn ($state) => $state->label()),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('Klasifikasi')
                    ->options(CoaAccountType::options()),
                Tables\Filters\SelectFilter::make('fund_category')
                    ->label('Kategori Dana')
                    ->options(FundCategory::options()),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->defaultSort('code', 'asc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCoaAccounts::route('/'),
            'create' => Pages\CreateCoaAccount::route('/create'),
            'edit' => Pages\EditCoaAccount::route('/{record}/edit'),
        ];
    }
}
