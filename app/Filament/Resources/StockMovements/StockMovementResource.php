<?php

namespace App\Filament\Resources\StockMovements;

use App\Filament\Resources\StockMovements\Pages\ManageStockMovements;
use App\Models\Inventory\StockMovement;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class StockMovementResource extends Resource
{
    protected static ?string $model = StockMovement::class;

    protected static string | UnitEnum | null $navigationGroup = 'Inventory';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Pergerakan Stok')
                    ->description('Catat perubahan stok per produk. Sistem akan meng-update tabel Inventories sesuai tipe pergerakan.')
                    ->columns(2)
                    ->schema([
                        Select::make('product_id')
                            ->label('Produk')
                            ->relationship('product','name')
                            ->searchable()->preload()->required()
                            ->helperText('Produk yang stoknya berubah.'),

                        Select::make('location_id')
                            ->label('Lokasi')
                            ->relationship('location','code')
                            ->searchable()->preload()
                            ->required(fn (Get $get) => in_array($get('type'), ['IN','OUT','RESERVE','RELEASE']))
                            ->helperText('Lokasi gudang terkait pergerakan. Untuk ADJUST global, boleh dikosongkan (opsional).'),

                        Select::make('type')
                            ->label('Tipe')
                            ->options([
                                'IN'      => 'IN (Masuk / Penerimaan)',
                                'OUT'     => 'OUT (Keluar / Pengiriman)',
                                'RESERVE' => 'RESERVE (Tahan untuk pesanan)',
                                'RELEASE' => 'RELEASE (Lepas reservasi)',
                                'ADJUST'  => 'ADJUST (Penyesuaian ±)',
                            ])
                            ->required()
                            ->native(false)
                            ->helperText('IN/OUT mengubah stok fisik (on hand). RESERVE/RELEASE mengubah stok dipesan. ADJUST menambah/mengurangi secara manual.'),

                        TextInput::make('qty')
                            ->label('Qty')
                            ->numeric()
                            ->required()
                            ->helperText('Gunakan bilangan positif. Khusus ADJUST, boleh negatif/positif (contoh: -3 untuk koreksi).'),

                        TextInput::make('ref_type')
                            ->label('Ref Type')
                            ->maxLength(50)
                            ->helperText('Opsional: jenis referensi transaksi (ORDER, RETURN, MANUAL, dsb).'),

                        TextInput::make('ref_id')
                            ->label('Ref ID')
                            ->numeric()
                            ->helperText('Opsional: ID referensi transaksi (misal order_id).'),

                        Textarea::make('note')
                            ->label('Catatan')
                            ->rows(3)
                            ->maxLength(255)
                            ->helperText('Opsional: catatan tambahan untuk audit.'),
                    ])
                    ->helperText('Aturan bisnis: 
                        - IN: stok fisik bertambah. 
                        - OUT: stok fisik berkurang (tidak boleh melebihi available). 
                        - RESERVE: reserved bertambah (tidak boleh melebihi available). 
                        - RELEASE: reserved berkurang (tidak boleh negatif). 
                        - ADJUST: menambah/mengurangi on hand; pastikan tidak membuat on hand negatif.')
                        ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')->label('Waktu')->dateTime('d M Y H:i')->sortable(),
                TextColumn::make('product.sku')->label('Produk')->limit(30)->sortable()->searchable(),
                TextColumn::make('location.code')->label('Lokasi')->sortable()->searchable()->toggleable(),
                BadgeColumn::make('type')
                    ->label('Tipe')
                    ->colors([
                        'success' => 'IN',
                        'danger'  => 'OUT',
                        'warning' => 'RESERVE',
                        'info'    => 'RELEASE',
                        'gray'    => 'ADJUST',
                    ])
                    ->formatStateUsing(fn ($state) => strtoupper($state)),
                TextColumn::make('qty')->label('Qty')->sortable(),
                TextColumn::make('ref_type')->label('Ref')->toggleable(),
                TextColumn::make('ref_id')->label('Ref ID')->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('note')->label('Catatan')->limit(30)->toggleable(),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->options([
                        'IN' => 'IN', 'OUT' => 'OUT', 'RESERVE' => 'RESERVE', 'RELEASE' => 'RELEASE', 'ADJUST' => 'ADJUST'
                    ]),
                Filter::make('date_range')
                    ->form([
                        DatePicker::make('from')->label('Dari'),
                        DatePicker::make('to')->label('Sampai'),
                    ])
                    ->query(function (Builder $q, array $data) {
                        return $q
                            ->when($data['from'] ?? null, fn ($qq, $from) => $qq->whereDate('created_at', '>=', $from))
                            ->when($data['to'] ?? null, fn ($qq, $to)   => $qq->whereDate('created_at', '<=', $to));
                    }),
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
            'index' => ManageStockMovements::route('/'),
        ];
    }
}
