<?php

namespace App\Filament\Resources\EvenLogs\Pages;

use App\Filament\Resources\EvenLogs\EvenLogResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageEvenLogs extends ManageRecords
{
    protected static string $resource = EvenLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
