<?php

namespace App\Filament\Resources\DisbursementRequestResource\Pages;

use App\Filament\Resources\DisbursementRequestResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateDisbursementRequest extends CreateRecord
{
    protected static string $resource = DisbursementRequestResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['requested_by'] = auth()->id();
        if (empty($data['request_number'])) {
            $data['request_number'] = \App\Models\DisbursementRequest::generateRequestNumber();
        }

        return $data;
    }
}
