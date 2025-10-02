<?php

namespace App\Filament\Resources\WislistItems\Pages;

use App\Filament\Resources\WislistItems\WislistItemResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageWislistItems extends ManageRecords
{
    protected static string $resource = WislistItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
