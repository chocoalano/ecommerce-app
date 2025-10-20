<?php

namespace App\Filament\Resources\FooterPayments\Pages;

use App\Filament\Resources\FooterPayments\FooterPaymentResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageFooterPayments extends ManageRecords
{
    protected static string $resource = FooterPaymentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
