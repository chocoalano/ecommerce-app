<?php

namespace App\Filament\Resources\Refunds;

use App\Filament\Resources\Refunds\Pages\ManageRefunds;
use App\Models\OrderProduct\Refund;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use UnitEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class RefundResource extends Resource
{
    protected static ?string $model = Refund::class;

    protected static string | UnitEnum | null $navigationGroup = 'Penjualan';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('order_id')
                ->relationship('order', 'id')
                ->label('Order')
                ->helperText('Relasi ke order yang direfund')
                ->required(),
            Select::make('payment_id')
                ->relationship('payment', 'id')
                ->label('Payment')
                ->helperText('Relasi ke payment utama untuk refund ini')
                ->required(),
            Select::make('status')
                ->options([
                    'PENDING'   => 'Pending',
                    'APPROVED'  => 'Approved',
                    'REJECTED'  => 'Rejected',
                    'COMPLETED' => 'Completed',
                ])
                ->label('Status')
                ->helperText('Status refund (Pending, Approved, Rejected, Completed)')
                ->required(),
            TextInput::make('amount')
                ->numeric()
                ->label('Amount')
                ->helperText('Nominal refund (dalam satuan mata uang)')
                ->required(),
            Textarea::make('reason')
                ->label('Reason')
                ->helperText('Alasan/refund note dari customer/admin')
                ->rows(3),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->sortable(),
                TextColumn::make('order.order_no')->label('Order')->sortable(),
                TextColumn::make('payment.status')->label('Payment Status')->sortable(),
                TextColumn::make('status')->sortable(),
                TextColumn::make('amount')->label('Amount')->sortable(),
                TextColumn::make('reason')->label('Reason')->limit(30),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'PENDING'   => 'Pending',
                        'APPROVED'  => 'Approved',
                        'REJECTED'  => 'Rejected',
                        'COMPLETED' => 'Completed',
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
            'index' => ManageRefunds::route('/'),
        ];
    }
}
