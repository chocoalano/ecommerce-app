<?php

namespace App\Filament\Resources\InventoryLocations;

use App\Filament\Resources\InventoryLocations\Pages\ManageInventoryLocations;
use App\Models\Inventory\InventoryLocation;
use Filament\Actions\BulkAction;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use UnitEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class InventoryLocationResource extends Resource
{
    protected static ?string $model = InventoryLocation::class;

    protected static string | UnitEnum | null $navigationGroup = 'Inventory';
    public static function getGloballySearchableAttributes(): array
    {
        return ['type','ref_type','note', 'product.name', 'location.code'];
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Lokasi Gudang')
                    ->description('Identitas lokasi untuk pemetaan stok dan pergerakan inventory. Kode harus unik dan konsisten di seluruh sistem.')
                    ->columns(2)
                    ->schema([
                        TextInput::make('code')
                            ->label('Kode Lokasi')
                            ->required()
                            ->unique(table: 'inventory_locations', column: 'code', ignoreRecord: true)
                            ->maxLength(50)
                            ->helperText('Contoh: WH-JKT-01. Gunakan pola konsisten, misalnya [WH]-[KOTA]-[NOMOR].'),

                        TextInput::make('name')
                            ->label('Nama Lokasi')
                            ->maxLength(255)
                            ->helperText('Opsional. Contoh: Gudang Jakarta Timur.'),

                        Toggle::make('is_active')
                            ->label('Aktif')
                            ->inline(false)
                            ->default(true)
                            ->helperText('Nonaktifkan untuk menghentikan alokasi / penerimaan stok ke lokasi ini.'),

                        KeyValue::make('address_json')
                            ->label('Alamat (JSON)')
                            ->keyLabel('Field')
                            ->valueLabel('Nilai')
                            ->addButtonLabel('Tambah Field')
                            ->reorderable()
                            ->default([])
                            ->helperText('Simpan detail alamat terstruktur: street, city, province, postal_code, country, lat, lng. Dapat diperluas sesuai kebutuhan.')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')->label('Kode')->sortable()->searchable(),
                TextColumn::make('name')->label('Nama')->sortable()->searchable(),
                TextColumn::make('address_json.city')
                    ->label('Kota'),
                IconColumn::make('is_active')->label('Aktif')->boolean()->sortable(),
                TextColumn::make('created_at')->label('Dibuat')->dateTime('d M Y')->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('is_active')
                    ->label('Status')
                    ->trueLabel('Aktif')->falseLabel('Nonaktif')->placeholder('Semua')
                    ->queries(
                        true: fn (Builder $q) => $q->where('is_active', true),
                        false: fn (Builder $q) => $q->where('is_active', false),
                        blank: fn (Builder $q) => $q
                    ),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    BulkAction::make('activate')
                        ->label('Aktifkan')
                        ->icon('heroicon-o-check-circle')
                        ->requiresConfirmation()
                        ->action(fn (Collection $records) => $records->each->update(['is_active' => true])),

                    BulkAction::make('deactivate')
                        ->label('Nonaktifkan')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(fn (Collection $records) => $records->each->update(['is_active' => false])),

                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageInventoryLocations::route('/'),
        ];
    }
}
