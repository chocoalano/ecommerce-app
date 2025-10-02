<?php

namespace App\Filament\Resources\Wislists\Pages;

use App\Filament\Resources\Wislists\WislistResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageWislists extends ManageRecords
{
    protected static string $resource = WislistResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
