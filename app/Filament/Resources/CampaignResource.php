<?php

namespace App\Filament\Resources;

use App\Enums\CampaignCategory;
use App\Enums\CampaignStatus;
use App\Filament\Resources\CampaignResource\Pages;
use App\Models\Campaign;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\Column;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class CampaignResource extends Resource
{
    protected static ?string $model = Campaign::class;

    protected static ?string $navigationIcon = 'heroicon-o-megaphone';

    protected static ?string $navigationGroup = 'Fundraising';

    protected static ?string $modelLabel = 'Kampanye';

    protected static ?string $pluralModelLabel = 'Kampanye';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('title')->label('Judul')->required()->maxLength(255)
                ->live(onBlur: true)
                ->afterStateUpdated(fn ($state, Forms\Set $set, ?Campaign $record) => $record ? null : $set('slug', Str::slug($state))),
            Forms\Components\TextInput::make('slug')->required()->unique(ignoreRecord: true)->maxLength(255),
            Forms\Components\Select::make('category')->label('Kategori')->options(CampaignCategory::options())->required(),
            Forms\Components\Select::make('status')->options(CampaignStatus::options())->default('draft')->required(),
            Forms\Components\TextInput::make('target_amount')->label('Target dana (kosongkan = open-ended)')
                ->numeric()->minValue(1)->prefix('Rp'),
            Forms\Components\TextInput::make('collected_amount')->label('Dana terkumpul')->prefix('Rp')
                ->disabled()->dehydrated(false)->default(0)
                ->helperText('Diisi otomatis dari pembayaran terkonfirmasi (webhook).'),
            Forms\Components\DatePicker::make('start_date')->label('Mulai'),
            Forms\Components\DatePicker::make('end_date')->label('Berakhir')->afterOrEqual('start_date'),
            Forms\Components\FileUpload::make('cover_image')->label('Gambar sampul')->image()->directory('campaigns')->disk('public'),
            Forms\Components\MarkdownEditor::make('description')->label('Deskripsi')->columnSpanFull(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('cover_image')->disk('public')->label('')->circular(),
                Tables\Columns\TextColumn::make('title')->label('Judul')->searchable()->limit(40),
                Tables\Columns\TextColumn::make('category')->badge()->formatStateUsing(fn ($state) => $state->label()),
                Tables\Columns\TextColumn::make('status')->badge()
                    ->color(fn ($state) => match ($state->value) {
                        'active' => 'success', 'draft' => 'gray', 'completed' => 'info', default => 'danger',
                    })->formatStateUsing(fn ($state) => $state->label()),
                Tables\Columns\ViewColumn::make('progress')->label('Progres')->view('filament.tables.campaign-progress'),
                Tables\Columns\TextColumn::make('collected_amount')->label('Terkumpul')->money('IDR', locale: 'id')->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options(CampaignStatus::options()),
                Tables\Filters\SelectFilter::make('category')->options(CampaignCategory::options()),
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([Tables\Actions\EditAction::make()])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCampaigns::route('/'),
            'create' => Pages\CreateCampaign::route('/create'),
            'edit' => Pages\EditCampaign::route('/{record}/edit'),
        ];
    }
}
