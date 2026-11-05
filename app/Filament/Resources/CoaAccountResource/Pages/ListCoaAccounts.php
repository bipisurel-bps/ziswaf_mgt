<?php

namespace App\Filament\Resources\CoaAccountResource\Pages;

use App\Filament\Resources\CoaAccountResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCoaAccounts extends ListRecords
{
    protected static string $resource = CoaAccountResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
