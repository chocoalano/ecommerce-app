<?php

namespace App\Filament\Resources\Couriers\Pages;

use App\Filament\Resources\Couriers\CourierResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageCouriers extends ManageRecords
{
    protected static string $resource = CourierResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
