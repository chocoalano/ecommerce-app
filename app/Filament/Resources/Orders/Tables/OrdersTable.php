<?php

namespace App\Filament\Resources\Orders\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
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
                TextColumn::make('order_no')->label('Order No')->searchable()->sortable(),
                TextColumn::make('user.name')->label('User')->toggleable()->searchable(),
                BadgeColumn::make('status')->label('Status')
                    ->colors([
                        'warning' => ['PENDING','PROCESSING','READY_TO_SHIP'],
                        'info'    => ['PAID','SHIPPED'],
                        'success' => ['COMPLETED'],
                        'danger'  => ['FAILED','CANCELED'],
                        'secondary' => ['REFUNDED','PARTIAL_REFUND'],
                    ])
                    ->sortable(),
                TextColumn::make('grand_total')->money('idr', true)->label('Grand Total')->sortable(),
                TextColumn::make('placed_at')->dateTime('Y-m-d H:i')->label('Placed')->sortable(),
                TextColumn::make('updated_at')->dateTime('Y-m-d H:i')->label('Updated')->toggleable()->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'PENDING'=>'PENDING','PAID'=>'PAID','PROCESSING'=>'PROCESSING','SHIPPED'=>'SHIPPED',
                        'COMPLETED'=>'COMPLETED','CANCELED'=>'CANCELED','REFUNDED'=>'REFUNDED','PARTIAL_REFUND'=>'PARTIAL_REFUND',
                    ]),
                // Filter::make('periode')
                //     ->form([
                //         DateTimePicker::make('from')->label('Dari'),
                //         DateTimePicker::make('until')->label('Sampai'),
                //     ])
                //     ->query(fn ($q, $data) => $q
                //         ->when($data['from'] ?? null, fn ($qq, $v) => $qq->where('placed_at','>=',$v))
                //         ->when($data['until'] ?? null, fn ($qq, $v) => $qq->where('placed_at','<=',$v))
                //     ),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
