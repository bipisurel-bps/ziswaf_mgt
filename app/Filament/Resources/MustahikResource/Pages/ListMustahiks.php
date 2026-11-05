<?php

namespace App\Filament\Resources\MustahikResource\Pages;

use App\Filament\Resources\MustahikResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMustahiks extends ListRecords
{
    protected static string $resource = MustahikResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
