<?php

namespace App\Filament\Resources\DisbursementRequestResource\Pages;

use App\Filament\Resources\DisbursementRequestResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDisbursementRequests extends ListRecords
{
    protected static string $resource = DisbursementRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
