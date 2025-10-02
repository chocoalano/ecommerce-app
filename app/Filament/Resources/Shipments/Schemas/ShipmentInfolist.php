<?php

namespace App\Filament\Resources\Shipments\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class ShipmentInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('order_id')
                    ->numeric(),
                TextEntry::make('courier_id')
                    ->numeric(),
                TextEntry::make('tracking_no'),
                TextEntry::make('status'),
                TextEntry::make('shipped_at')
                    ->dateTime(),
                TextEntry::make('delivered_at')
                    ->dateTime(),
                TextEntry::make('shipping_fee')
                    ->numeric(),
                TextEntry::make('created_at')
                    ->dateTime(),
                TextEntry::make('updated_at')
                    ->dateTime(),
            ]);
    }
}
