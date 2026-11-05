<?php

namespace App\Filament\Resources\DisbursementResource\Pages;

use App\Exceptions\InsufficientCampaignBalance;
use App\Filament\Resources\DisbursementResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateDisbursement extends CreateRecord
{
    protected static string $resource = DisbursementResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = auth()->id();

        return $data;
    }

    protected function handleRecordCreation(array $data): Model
    {
        try {
            return parent::handleRecordCreation($data);
        } catch (InsufficientCampaignBalance $e) {
            // Race-condition backstop (model guard under row lock).
            Notification::make()->title('Penyaluran ditolak')->body($e->getMessage())->danger()->send();
            $this->halt();
        }
    }
}
