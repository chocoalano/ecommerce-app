<?php

namespace App\Filament\Resources\PaymentTransactions;

use App\Filament\Resources\PaymentTransactions\Pages\ManagePaymentTransactions;
use App\Models\PaymentTransaction;
use UnitEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PaymentTransactionResource extends Resource
{
    protected static ?string $model = PaymentTransaction::class;

    protected static string|UnitEnum|null $navigationGroup = 'Pesanan';

    protected static ?string $recordTitleAttribute = 'PyamentTransaction';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('payment_id')
                    ->required()
                    ->numeric(),
                Select::make('status')
                    ->options([
            'INITIATED' => 'I n i t i a t e d',
            'AUTHORIZED' => 'A u t h o r i z e d',
            'CAPTURED' => 'C a p t u r e d',
            'FAILED' => 'F a i l e d',
            'CANCELED' => 'C a n c e l e d',
            'REFUNDED' => 'R e f u n d e d',
            'PARTIAL_REFUND' => 'P a r t i a l  r e f u n d',
        ])
                    ->required(),
                TextInput::make('amount')
                    ->required()
                    ->numeric(),
                TextInput::make('raw_json'),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('payment_id')
                    ->numeric(),
                TextEntry::make('status'),
                TextEntry::make('amount')
                    ->numeric(),
                TextEntry::make('created_at')
                    ->dateTime(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('PyamentTransaction')
            ->columns([
                TextColumn::make('payment_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('status'),
                TextColumn::make('amount')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
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
