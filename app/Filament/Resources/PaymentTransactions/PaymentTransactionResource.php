<?php

namespace App\Filament\Resources\PaymentTransactions;

use App\Filament\Resources\PaymentTransactions\Pages\ManagePaymentTransactions;
use App\Models\OrderProduct\PaymentTransaction;
use Filament\Forms\Components\DateTimePicker;
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

class PaymentTransactionResource extends Resource
{
    protected static ?string $model = PaymentTransaction::class;

    protected static string | UnitEnum | null $navigationGroup = 'Penjualan';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('payment_id')
                ->relationship('payment', 'id')
                ->label('Payment')
                ->helperText('Relasi ke payment utama untuk transaksi ini')
                ->required(),
            Select::make('status')
                ->options([
                    'PENDING'   => 'Pending',
                    'SUCCESS'   => 'Success',
                    'FAILED'    => 'Failed',
                    'EXPIRED'   => 'Expired',
                ])
                ->label('Status')
                ->helperText('Status transaksi pembayaran (Pending, Success, Failed, Expired)')
                ->required(),
            TextInput::make('amount')
                ->numeric()
                ->label('Amount')
                ->helperText('Nominal transaksi (dalam satuan mata uang)')
                ->required(),
            Textarea::make('raw_json')
                ->label('Raw JSON')
                ->helperText('Payload data mentah dari gateway/payment (format JSON)')
                ->rows(4),
            DateTimePicker::make('created_at')
                ->label('Created At')
                ->helperText('Waktu transaksi tercatat')
                ->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('payment.status')->label('Payment Status')->sortable(),
                TextColumn::make('status')->sortable(),
                TextColumn::make('amount')->label('Amount')->sortable(),
                TextColumn::make('created_at')->dateTime()->label('Created At'),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'PENDING'   => 'Pending',
                        'SUCCESS'   => 'Success',
                        'FAILED'    => 'Failed',
                        'EXPIRED'   => 'Expired',
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
            'index' => ManagePaymentTransactions::route('/'),
        ];
    }
}
