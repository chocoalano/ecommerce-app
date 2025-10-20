<?php

namespace App\Filament\Resources\FooterGuarantes\Pages;

use App\Filament\Resources\FooterGuarantes\FooterGuaranteResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageFooterGuarantes extends ManageRecords
{
    protected static string $resource = FooterGuaranteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
