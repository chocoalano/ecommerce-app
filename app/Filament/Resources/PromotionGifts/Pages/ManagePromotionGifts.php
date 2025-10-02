<?php

namespace App\Filament\Resources\PromotionGifts\Pages;

use App\Filament\Resources\PromotionGifts\PromotionGiftResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManagePromotionGifts extends ManageRecords
{
    protected static string $resource = PromotionGiftResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
