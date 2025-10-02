<?php

namespace App\Filament\Resources\VoucherRedemtions\Pages;

use App\Filament\Resources\VoucherRedemtions\VoucherRedemtionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageVoucherRedemtions extends ManageRecords
{
    protected static string $resource = VoucherRedemtionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
