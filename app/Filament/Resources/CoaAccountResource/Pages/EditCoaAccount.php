<?php

namespace App\Filament\Resources\CoaAccountResource\Pages;

use App\Filament\Resources\CoaAccountResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCoaAccount extends EditRecord
{
    protected static string $resource = CoaAccountResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
