<?php

namespace App\Filament\Resources\BankInstallments\Pages;

use App\Filament\Resources\BankInstallments\BankInstallmentResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageBankInstallments extends ManageRecords
{
    protected static string $resource = BankInstallmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
