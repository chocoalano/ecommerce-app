<?php

namespace App\Filament\Resources\Orders\Tables;

use App\Models\OrderProduct\Order;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Builder;
use Filament\Forms\Components\DateTimePicker;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('order_no')->label('Order #')->searchable()->sortable(),
                TextColumn::make('customer.name')->label('Customer')->placeholder('Guest')->searchable(),
                BadgeColumn::make('status')->label('Status')->colors([
                    'warning' => ['PENDING','PROCESSING'],
                    'success' => ['PAID','COMPLETED'],
                    'info'    => ['SHIPPED'],
                    'danger'  => ['CANCELED','REFUNDED','PARTIAL_REFUND'],
                ])->sortable(),
                TextColumn::make('grand_total')->label('Total')->money('idr', true)->sortable(),
                TextColumn::make('placed_at')->label('Placed')->dateTime('d M Y H:i')->sortable(),
                TextColumn::make('created_at')->label('Dibuat')->since()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')->options([
                    'PENDING'=>'PENDING','PAID'=>'PAID','PROCESSING'=>'PROCESSING',
                    'SHIPPED'=>'SHIPPED','COMPLETED'=>'COMPLETED','CANCELED'=>'CANCELED',
                    'REFUNDED'=>'REFUNDED','PARTIAL_REFUND'=>'PARTIAL_REFUND',
                ]),
            ])
            ->actions([
                EditAction::make(),
                Action::make('markPaid')
                    ->label('Mark as Paid')->icon('heroicon-o-banknotes')
                    ->requiresConfirmation()
                    ->visible(fn(Order $record)=>$record->status==='PENDING')
                    ->action(fn(Order $record)=>$record->update(['status'=>'PAID'])),
                Action::make('cancelOrder')
                    ->label('Cancel Order')->color('danger')->icon('heroicon-o-x-circle')
                    ->requiresConfirmation()
                    ->visible(fn(Order $record)=>in_array($record->status,['PENDING','PAID','PROCESSING']))
                    ->action(fn(Order $record)=>$record->update(['status'=>'CANCELED'])),
            ])
            ->bulkActions([
                BulkAction::make('export')->label('Export CSV')->action(fn()=>null)
                    ->visible(false), // siapkan jika mau
            ])
            ->defaultSort('placed_at','desc');;
    }
}
