<?php

namespace App\Filament\Resources;

use App\Enums\DisbursementRequestStatus;
use App\Filament\Resources\DisbursementRequestResource\Pages;
use App\Models\Campaign;
use App\Models\DisbursementRequest;
use App\Models\Mustahik;
use App\Rules\WithinCampaignBalance;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class DisbursementRequestResource extends Resource
{
    protected static ?string $model = DisbursementRequest::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static ?string $navigationGroup = 'Penyaluran';

    protected static ?string $modelLabel = 'Pengajuan Penyaluran';

    protected static ?string $pluralModelLabel = 'Pengajuan Penyaluran';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Pengajuan')
                    ->schema([
                        Forms\Components\TextInput::make('request_number')
                            ->label('Nomor Pengajuan')
                            ->default(fn () => DisbursementRequest::generateRequestNumber())
                            ->disabled()
                            ->dehydrated(),
                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options(DisbursementRequestStatus::options())
                            ->default(DisbursementRequestStatus::Draft->value)
                            ->disabled()
                            ->dehydrated(),
                        Forms\Components\Select::make('campaign_id')
                            ->label('Program / Kampanye')
                            ->required()
                            ->live()
                            ->searchable()
                            ->options(fn () => Campaign::orderBy('title')->get()->mapWithKeys(fn (Campaign $c) => [
                                $c->id => $c->title.' (Sisa: Rp '.number_format($c->remaining_balance, 0, ',', '.').')',
                            ])),
                        Forms\Components\Select::make('mustahik_id')
                            ->label('Mustahik Penerima')
                            ->required()
                            ->searchable()
                            ->options(fn () => Mustahik::where('status_approval', 'verified')
                                ->orderBy('name')
                                ->get()
                                ->mapWithKeys(function (Mustahik $m) {
                                    $score = $m->had_kifayah_score !== null ? ' [Skor: '.round($m->had_kifayah_score * 100, 1).'%]' : '';

                                    return [$m->id => "{$m->name} ({$m->asnaf_category->label()}){$score}"];
                                })),
                        Forms\Components\TextInput::make('amount')
                            ->label('Nominal Pengajuan')
                            ->required()
                            ->numeric()
                            ->minValue(1)
                            ->prefix('Rp')
                            ->rules(fn (Forms\Get $get, ?DisbursementRequest $record) => [
                                new WithinCampaignBalance($get('campaign_id') ? (int) $get('campaign_id') : null),
                            ])
                            ->helperText(function (Forms\Get $get) {
                                $c = $get('campaign_id') ? Campaign::withTrashed()->find($get('campaign_id')) : null;

                                return $c ? 'Sisa saldo tersedia pada kampanye: Rp '.number_format($c->remaining_balance, 0, ',', '.') : 'Pilih kampanye untuk melihat sisa dana.';
                            }),
                        Forms\Components\Textarea::make('purpose')
                            ->label('Tujuan / Rencana Penyaluran')
                            ->required()
                            ->columnSpanFull(),
                        Forms\Components\FileUpload::make('proof_document')
                            ->label('Lampiran / Dokumen Pendukung (Proposal/Surat Permohonan)')
                            ->disk('local')
                            ->directory('disbursement-proofs')
                            ->visibility('private')
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'application/pdf'])
                            ->maxSize(5120)
                            ->downloadable()
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('notes')
                            ->label('Catatan Tambahan')
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['campaign', 'mustahik', 'requester', 'approver']))
            ->columns([
                Tables\Columns\TextColumn::make('request_number')
                    ->label('No. Pengajuan')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('campaign.title')
                    ->label('Kampanye')
                    ->limit(25)
                    ->searchable(),
                Tables\Columns\TextColumn::make('mustahik.name')
                    ->label('Mustahik')
                    ->searchable(),
                Tables\Columns\TextColumn::make('amount')
                    ->label('Nominal')
                    ->money('IDR', locale: 'id')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (DisbursementRequestStatus $state) => match ($state) {
                        DisbursementRequestStatus::Draft => 'gray',
                        DisbursementRequestStatus::Approved => 'info',
                        DisbursementRequestStatus::Disbursed => 'success',
                        DisbursementRequestStatus::Rejected => 'danger',
                    })
                    ->formatStateUsing(fn (DisbursementRequestStatus $state) => $state->label()),
                Tables\Columns\TextColumn::make('requester.name')
                    ->label('Diajukan Oleh')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('approver.name')
                    ->label('Disetujui/Ditolak Oleh')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal Diajukan')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options(DisbursementRequestStatus::options()),
                Tables\Filters\SelectFilter::make('campaign_id')
                    ->label('Kampanye')
                    ->relationship('campaign', 'title'),
            ])
            ->actions([
                Tables\Actions\Action::make('approve')
                    ->label('Setujui')
                    ->icon('heroicon-o-check-circle')
                    ->color('info')
                    ->visible(fn (DisbursementRequest $record) => $record->status === DisbursementRequestStatus::Draft)
                    ->requiresConfirmation()
                    ->modalHeading('Setujui Pengajuan Penyaluran')
                    ->modalDescription('Apakah Anda yakin menyetujui pengajuan ini? Status akan berubah menjadi [Disetujui].')
                    ->action(function (DisbursementRequest $record) {
                        $record->approve(auth()->user());
                        Notification::make()
                            ->title('Pengajuan disetujui')
                            ->body("Pengajuan {$record->request_number} siap dicairkan.")
                            ->success()
                            ->send();
                    }),

                Tables\Actions\Action::make('disburse')
                    ->label('Cairkan Dana')
                    ->icon('heroicon-o-banknotes')
                    ->color('success')
                    ->visible(fn (DisbursementRequest $record) => in_array($record->status, [DisbursementRequestStatus::Draft, DisbursementRequestStatus::Approved], true))
                    ->modalHeading('Pencairan Dana Penyaluran')
                    ->modalDescription('Tindakan ini akan memotong saldo kampanye secara atomik, mencatat penyaluran resmi, dan menjurnal transaksi ke buku besar PSAK 109.')
                    ->form([
                        Forms\Components\FileUpload::make('proof_document')
                            ->label('Bukti Pencairan / Transfer / Berita Acara')
                            ->disk('local')
                            ->directory('disbursement-proofs')
                            ->visibility('private')
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'application/pdf'])
                            ->maxSize(5120),
                        Forms\Components\Textarea::make('notes')
                            ->label('Catatan Tambahan Saat Pencairan'),
                    ])
                    ->action(function (DisbursementRequest $record, array $data) {
                        try {
                            $record->disburse(
                                auth()->user(),
                                $data['proof_document'] ?? null,
                                $data['notes'] ?? null
                            );

                            Notification::make()
                                ->title('Dana Berhasil Dicairkan')
                                ->body("Penyaluran {$record->request_number} dicatat dan dijurnal ke PSAK 109.")
                                ->success()
                                ->send();
                        } catch (\Throwable $e) {
                            Notification::make()
                                ->title('Gagal Mencairkan Dana')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),

                Tables\Actions\Action::make('reject')
                    ->label('Tolak')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (DisbursementRequest $record) => ! in_array($record->status, [DisbursementRequestStatus::Disbursed, DisbursementRequestStatus::Rejected], true))
                    ->form([
                        Forms\Components\Textarea::make('reason')
                            ->label('Alasan Penolakan')
                            ->required(),
                    ])
                    ->action(function (DisbursementRequest $record, array $data) {
                        $record->reject(auth()->user(), $data['reason']);
                        Notification::make()
                            ->title('Pengajuan Ditolak')
                            ->warning()
                            ->send();
                    }),

                Tables\Actions\EditAction::make()
                    ->visible(fn (DisbursementRequest $record) => $record->status === DisbursementRequestStatus::Draft),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDisbursementRequests::route('/'),
            'create' => Pages\CreateDisbursementRequest::route('/create'),
            'edit' => Pages\EditDisbursementRequest::route('/{record}/edit'),
        ];
    }
}
