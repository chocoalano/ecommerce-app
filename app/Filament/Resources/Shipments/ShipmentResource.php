<?php

namespace App\Filament\Resources\Shipments;

use App\Filament\Resources\Shipments\Pages\ManageShipments;
use App\Models\OrderProduct\Shipment;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use UnitEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class ShipmentResource extends Resource
{
    protected static ?string $model = Shipment::class;

    protected static string | UnitEnum | null $navigationGroup = 'Penjualan';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            // Relasi order
            Select::make('order_id')
                ->relationship('order', 'order_no')
                ->required()
                ->helperText('Pilih nomor order yang akan dikirim.'),
            // Relasi courier
            Select::make('courier_id')
                ->relationship('courier', 'name')
                ->required()
                ->helperText('Pilih kurir yang akan digunakan untuk pengiriman.'),
            TextInput::make('tracking_no')
                ->label('Tracking No')
                ->required()
                ->helperText('Nomor resi pengiriman. Akan digenerate otomatis jika kosong.')
                ->default(fn () => 'TRK-' . strtoupper(uniqid()))
                ->disabled(fn ($record) => filled($record?->tracking_no)),
            Select::make('status')
                ->options([
                    Shipment::ST_READY => 'Ready to Ship',
                    Shipment::ST_TRANSIT => 'In Transit',
                    Shipment::ST_DELIVERED => 'Delivered',
                    Shipment::ST_FAILED => 'Failed',
                    Shipment::ST_RETURNED => 'Returned',
                ])
                ->required()
                ->helperText('Status pengiriman saat ini.'),
            TextInput::make('shipping_fee')
                ->label('Shipping Fee')
                ->prefix('Rp')
                ->helperText('Biaya pengiriman untuk order ini (dalam Rupiah).'),
            DateTimePicker::make('shipped_at')
                ->label('Shipped At')
                ->helperText('Tanggal dan waktu pengiriman.'),
            DateTimePicker::make('delivered_at')
                ->label('Delivered At')
                ->helperText('Tanggal dan waktu pesanan diterima.'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('order.order_no')->label('Order')->sortable(),
                TextColumn::make('courier.name')->label('Courier')->sortable(),
                TextColumn::make('tracking_no')->label('Tracking No')->searchable(),
                TextColumn::make('status')->sortable(),
                TextColumn::make('shipping_fee')->label('Shipping Fee')->sortable(),
                TextColumn::make('shipped_at')->dateTime()->label('Shipped At'),
                TextColumn::make('delivered_at')->dateTime()->label('Delivered At'),
            ])
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('status')
                    ->options([
                        \App\Models\OrderProduct\Shipment::ST_READY => 'Ready to Ship',
                        \App\Models\OrderProduct\Shipment::ST_TRANSIT => 'In Transit',
                        \App\Models\OrderProduct\Shipment::ST_DELIVERED => 'Delivered',
                        \App\Models\OrderProduct\Shipment::ST_FAILED => 'Failed',
                        \App\Models\OrderProduct\Shipment::ST_RETURNED => 'Returned',
                    ]),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageShipments::route('/'),
        ];
    }
}
