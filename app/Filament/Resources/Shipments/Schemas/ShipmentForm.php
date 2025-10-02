<?php

namespace App\Filament\Resources\Shipments\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ShipmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('order_id')
                    ->required()
                    ->numeric(),
                TextInput::make('courier_id')
                    ->numeric(),
                TextInput::make('tracking_no'),
                Select::make('status')
                    ->options([
            'READY_TO_SHIP' => 'R e a d y  t o  s h i p',
            'IN_TRANSIT' => 'I n  t r a n s i t',
            'DELIVERED' => 'D e l i v e r e d',
            'FAILED' => 'F a i l e d',
            'RETURNED' => 'R e t u r n e d',
        ])
                    ->default('READY_TO_SHIP')
                    ->required(),
                DateTimePicker::make('shipped_at'),
                DateTimePicker::make('delivered_at'),
                TextInput::make('shipping_fee')
                    ->required()
                    ->numeric()
                    ->default(0.0),
            ]);
    }
}
