<?php

namespace App\Filament\Resources\Inventories;

use App\Filament\Resources\Inventories\Pages\ManageInventories;
use App\Models\Inventory\Inventory;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rule;
use UnitEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class InventoryResource extends Resource
{
    protected static ?string $model = Inventory::class;

    protected static string | UnitEnum | null $navigationGroup = 'Inventory';
    public static function getGloballySearchableAttributes(): array
    {
        return ['product.name', 'location.code'];
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Stok per Lokasi')
                    ->description('Catatan stok fisik (on hand), stok dipesan (reserved), dan safety stock pada kombinasi Produk × Lokasi.')
                    ->columns(2)
                    ->schema([
                        Select::make('product_id')
                            ->label('Produk')
                            ->relationship('product','name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->helperText('Pilih produk yang ingin dicatat stoknya.'),

                        Select::make('location_id')
                            ->label('Lokasi')
                            ->relationship('location','code')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->helperText('Pilih gudang/lokasi penyimpanan stok.'),

                        TextInput::make('qty_on_hand')
                            ->label('Qty On Hand')
                            ->numeric()->minValue(0)->default(0)
                            ->required()
                            ->helperText('Jumlah stok fisik tersedia di lokasi ini.'),

                        TextInput::make('qty_reserved')
                            ->label('Qty Reserved')
                            ->numeric()->minValue(0)->default(0)
                            ->required()
                            ->helperText('Jumlah stok yang sudah dipesan/ditahan (belum dikirim).'),

                        TextInput::make('safety_stock')
                            ->label('Safety Stock')
                            ->numeric()->minValue(0)->default(0)
                            ->helperText('Ambang minimal agar sistem bisa memberi peringatan low stock.'),
                    ])->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('product.sku')->label('Produk')->sortable()->searchable(),
                TextColumn::make('product.name')->label('Produk')->limit(30)->sortable()->searchable(),
                TextColumn::make('location.code')->label('Lokasi')->sortable()->searchable(),
                TextColumn::make('qty_on_hand')->label('On Hand')->sortable(),
                TextColumn::make('qty_reserved')->label('Reserved')->sortable(),
                TextColumn::make('available')
                    ->label('Available')
                    ->getStateUsing(fn ($r) => ($r->qty_on_hand ?? 0) - ($r->qty_reserved ?? 0))
                    ->sortable(),
                TextColumn::make('safety_stock')->label('Safety')->sortable(),
            ])
            ->filters([
                SelectFilter::make('location_id')
                    ->label('Lokasi')
                    ->relationship('location','code'),

                SelectFilter::make('product_id')
                    ->label('Produk')
                    ->relationship('product','name'),

                Filter::make('low_stock')
                    ->label('Di bawah Safety Stock')
                    ->query(fn (Builder $q) => $q->whereRaw('(qty_on_hand - qty_reserved) < safety_stock')),
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
            'index' => ManageInventories::route('/'),
        ];
    }
}
