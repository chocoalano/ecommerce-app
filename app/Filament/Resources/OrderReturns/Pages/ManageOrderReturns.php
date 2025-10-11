<?php

namespace App\Filament\Resources\OrderReturns\Pages;

use App\Filament\Resources\OrderReturns\OrderReturnResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageOrderReturns extends ManageRecords
{
    protected static string $resource = OrderReturnResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
