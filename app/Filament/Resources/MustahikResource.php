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
            Forms\Components\TextInput::make('name')->label('Nama')->required()->maxLength(255),
            Forms\Components\TextInput::make('nik')->label('NIK (16 digit, disimpan terenkripsi)')
                ->length(16)->numeric()->rules([
                    fn (?Mustahik $record) => function (string $attr, $value, \Closure $fail) use ($record) {
                        $dup = Mustahik::where('nik_hash', Mustahik::hashNik((string) $value))
                            ->when($record, fn ($q) => $q->whereKeyNot($record->id))->exists();
                        if ($dup) {
                            $fail('NIK sudah terdaftar.');
                        }
                    },
                ]),
            Forms\Components\Select::make('asnaf_category')->label('Asnaf')->options(AsnafCategory::options())->required(),
            Forms\Components\Select::make('status_approval')->label('Status')->options(ApprovalStatus::options())->default('pending')->required(),
            Forms\Components\TextInput::make('phone')->label('Telepon')->tel(),
            Forms\Components\Textarea::make('address')->label('Alamat')->columnSpanFull(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Nama')->searchable(),
                Tables\Columns\TextColumn::make('masked_nik')->label('NIK'),
                Tables\Columns\TextColumn::make('asnaf_category')->label('Asnaf')->badge()->formatStateUsing(fn ($state) => $state->label()),
                Tables\Columns\TextColumn::make('status_approval')->label('Status')->badge()
                    ->color(fn ($state) => match ($state->value) {'verified' => 'success', 'rejected' => 'danger', default => 'warning'})
                    ->formatStateUsing(fn ($state) => $state->label()),
                Tables\Columns\TextColumn::make('phone')->toggleable(),
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
