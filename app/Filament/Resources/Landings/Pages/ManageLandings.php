<?php

namespace App\Filament\Resources\Landings\Pages;

use App\Filament\Resources\Landings\LandingResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageLandings extends ManageRecords
{
    protected static string $resource = LandingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
