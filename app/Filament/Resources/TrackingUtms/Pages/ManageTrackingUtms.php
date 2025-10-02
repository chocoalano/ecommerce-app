<?php

namespace App\Filament\Resources\TrackingUtms\Pages;

use App\Filament\Resources\TrackingUtms\TrackingUtmResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageTrackingUtms extends ManageRecords
{
    protected static string $resource = TrackingUtmResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
