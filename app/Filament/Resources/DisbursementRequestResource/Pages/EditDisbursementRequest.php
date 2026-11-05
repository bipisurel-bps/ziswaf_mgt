<?php

namespace App\Filament\Resources\DisbursementRequestResource\Pages;

use App\Filament\Resources\DisbursementRequestResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDisbursementRequest extends EditRecord
{
    protected static string $resource = DisbursementRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->visible(fn ($record) => $record->status !== \App\Enums\DisbursementRequestStatus::Disbursed),
        ];
    }
}
