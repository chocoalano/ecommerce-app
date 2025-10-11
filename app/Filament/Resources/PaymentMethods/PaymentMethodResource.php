<?php

namespace App\Filament\Resources\PaymentMethods;

use App\Filament\Resources\PaymentMethods\Pages\ManagePaymentMethods;
use App\Models\OrderProduct\PaymentMethod;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use UnitEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class PaymentMethodResource extends Resource
{
    protected static ?string $model = PaymentMethod::class;

    protected static string | UnitEnum | null $navigationGroup = 'Penjualan';
    protected static ?string $navigationLabel = 'Metode Pembayaran';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Metode Pembayaran')
                    ->description('Kelola master metode pembayaran yang didukung checkout.')
                    ->schema([
                        TextInput::make('code')
                            ->label('Kode')
                            ->helperText('Kode unik, misal: VA, QRIS, CC, E-WALLET, COD.')
                            ->required()
                            ->maxLength(50)
                            ->unique(ignoreRecord: true),

                        TextInput::make('name')
                            ->label('Nama Tampilan')
                            ->helperText('Nama yang ditampilkan kepada pelanggan (opsional).')
                            ->maxLength(100),

                        Toggle::make('is_active')
                            ->label('Aktif')
                            ->helperText('Nonaktifkan jika sementara tidak tersedia.')
                            ->default(true),
                    ])
                    ->columns(3)
                    ->columnSpanFull()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label('Kode')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->toggleable(),

                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean()
                    ->sortable(),
            ])
            ->filters([
                TernaryFilter::make('is_active')
                    ->label('Status Aktif')
                    ->placeholder('Semua')
                    ->trueLabel('Aktif')
                    ->falseLabel('Nonaktif')
                    ->queries(
                        true: fn ($q) => $q->where('is_active', true),
                        false: fn ($q) => $q->where('is_active', false),
                        blank: fn ($q) => $q
                    ),
            ])
            ->actions([
                Action::make('toggle')
                    ->label('Toggle')
                    ->icon('heroicon-o-power')
                    ->tooltip('Aktif/Nonaktifkan')
                    ->action(fn (PaymentMethod $record) => $record->update(['is_active' => ! $record->is_active])),

                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    BulkAction::make('activate')
                        ->label('Aktifkan')
                        ->icon('heroicon-o-check-circle')
                        ->action(fn ($records) => $records->each->update(['is_active' => true])),

                    BulkAction::make('deactivate')
                        ->label('Nonaktifkan')
                        ->icon('heroicon-o-x-circle')
                        ->action(fn ($records) => $records->each->update(['is_active' => false])),

                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManagePaymentMethods::route('/'),
        ];
    }
}
