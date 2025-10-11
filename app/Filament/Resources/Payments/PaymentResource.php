<?php

namespace App\Filament\Resources\Payments;

use App\Filament\Resources\Payments\Pages\ManagePayments;
use App\Models\OrderProduct\Payment;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Filters\SelectFilter;
use UnitEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;

class PaymentResource extends Resource
{
    protected static ?string $model = Payment::class;

    protected static string | UnitEnum | null $navigationGroup = 'Penjualan';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('order_id')
                    ->label('Order ID')
                    ->required(),
                TextInput::make('amount')
                    ->label('Amount')
                    ->numeric()
                    ->required(),
                Select::make('method')
                    ->label('Method')
                    ->options([
                        'cash' => 'Cash',
                        'transfer' => 'Transfer',
                        'credit_card' => 'Credit Card',
                    ])
                    ->required(),
                Select::make('status')
                    ->label('Status')
                    ->options([
                        'pending' => 'Pending',
                        'paid' => 'Paid',
                        'failed' => 'Failed',
                    ])
                    ->required(),
                DateTimePicker::make('created_at')
                    ->label('Created At')
                    ->disabled(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->label('ID')->sortable(),
                TextColumn::make('order.order_no')->label('Order Number')->sortable(),
                TextColumn::make('amount')->label('Amount')->money('IDR'),
                BadgeColumn::make('method')->label('Method'),
                BadgeColumn::make('status')->label('Status')
                    ->colors([
                        'pending' => 'warning',
                        'paid' => 'success',
                        'failed' => 'danger',
                    ]),
                TextColumn::make('created_at')->label('Created At')->dateTime(),
            ])
            ->filters([
                // Filter berdasarkan metode pembayaran
                SelectFilter::make('method')
                    ->label('Method')
                    ->options([
                        'cash' => 'Cash',
                        'transfer' => 'Transfer',
                        'credit_card' => 'Credit Card',
                    ]),
                // Filter berdasarkan status pembayaran
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'pending' => 'Pending',
                        'paid' => 'Paid',
                        'failed' => 'Failed',
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
            'index' => ManagePayments::route('/'),
        ];
    }
}
