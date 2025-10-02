<?php

namespace App\Filament\Resources\ProductVariants;

use App\Filament\Resources\ProductVariants\Components\VariantFormComponents;
use App\Filament\Resources\ProductVariants\Pages\ManageProductVariants;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class ProductVariantResource extends Resource
{
    protected static ?string $model = ProductVariant::class;
    protected static string | UnitEnum | null $navigationGroup = 'Produk';
    protected static ?string $recordTitleAttribute = 'ProductVariant';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components(VariantFormComponents::form());
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('product.sku')
                    ->numeric(),
                TextEntry::make('variant_sku'),
                TextEntry::make('name'),
                TextEntry::make('base_price')
                    ->numeric(),
                TextEntry::make('currency'),
                TextEntry::make('weight_gram')
                    ->numeric(),
                TextEntry::make('length_mm')
                    ->numeric(),
                TextEntry::make('width_mm')
                    ->numeric(),
                TextEntry::make('height_mm')
                    ->numeric(),
                IconEntry::make('is_active')
                    ->boolean(),
                TextEntry::make('created_at')
                    ->dateTime(),
                TextEntry::make('updated_at')
                    ->dateTime(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('ProductVariant')
            ->columns([
                TextColumn::make('product.sku')
                    ->label('SKU Produk')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('variant_sku')
                    ->label('SKU Variasi')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('name')
                    ->label('Nama Variasi')
                    ->searchable(),
                ImageColumn::make('media.url')
                    ->imageHeight(40)
                    ->circular()
                    ->stacked(),

                TextColumn::make('base_price')
                    ->label('Harga Dasar')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('currency')
                    ->label('Mata Uang')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('weight_gram')
                    ->label('Berat (g)')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('length_mm')
                    ->label('Panjang (mm)')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('width_mm')
                    ->label('Lebar (mm)')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('height_mm')
                    ->label('Tinggi (mm)')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('Diubah')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('is_active')
                    ->label('Status Aktif')
                    ->placeholder('Semua')
                    ->trueLabel('Aktif')
                    ->falseLabel('Nonaktif')
                    ->queries(
                        true: fn (Builder $query) => $query->where('is_active', true),
                        false: fn (Builder $query) => $query->where('is_active', false),
                        blank: fn (Builder $query) => $query,
                    )
                    ->indicateUsing(fn (array $data): ?string => match ($data['value'] ?? null) {
                        true => 'Aktif',
                        false => 'Nonaktif',
                        default => null,
                    }),

                Filter::make('price_range')
                    ->label('Harga Dasar (Rp)')
                    ->form([
                        TextInput::make('min')->label('Min. Harga')->numeric()
                            ->helperText('Tampilkan variasi dengan harga lebih besar atau sama dengan nilai ini.'),
                        TextInput::make('max')->label('Max. Harga')->numeric()
                            ->helperText('Tampilkan variasi dengan harga lebih kecil atau sama dengan nilai ini.'),
                    ])->columns(2)->columnSpan(2)
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['min'] ?? null, fn ($q, $min) => $q->where('base_price', '>=', (float) $min))
                            ->when($data['max'] ?? null, fn ($q, $max) => $q->where('base_price', '<=', (float) $max));
                    }),

                Filter::make('created_between')
                    ->label('Dibuat (Rentang)')
                    ->form([
                        DatePicker::make('from')->label('Dari')
                            ->helperText('Tanggal mulai pencarian data.'),
                        DatePicker::make('until')->label('Sampai')
                            ->helperText('Tanggal akhir pencarian data.'),
                    ])->columns(2)->columnSpan(2)
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'] ?? null, fn ($q, $from) => $q->whereDate('created_at', '>=', $from))
                            ->when($data['until'] ?? null, fn ($q, $until) => $q->whereDate('created_at', '<=', $until));
                    }),

                Filter::make('dimension_range')
                    ->label('Dimensi (mm)')
                    ->form([
                        TextInput::make('l_min')->label('Panjang Min')->numeric()->helperText('Filter panjang minimal.'),
                        TextInput::make('l_max')->label('Panjang Max')->numeric()->helperText('Filter panjang maksimal.'),
                        TextInput::make('w_min')->label('Lebar Min')->numeric()->helperText('Filter lebar minimal.'),
                        TextInput::make('w_max')->label('Lebar Max')->numeric()->helperText('Filter lebar maksimal.'),
                        TextInput::make('h_min')->label('Tinggi Min')->numeric()->helperText('Filter tinggi minimal.'),
                        TextInput::make('h_max')->label('Tinggi Max')->numeric()->helperText('Filter tinggi maksimal.'),
                    ])->columns(6)->columnSpanFull()
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['l_min'] ?? null, fn ($q, $v) => $q->where('length_mm', '>=', (int) $v))
                            ->when($data['l_max'] ?? null, fn ($q, $v) => $q->where('length_mm', '<=', (int) $v))
                            ->when($data['w_min'] ?? null, fn ($q, $v) => $q->where('width_mm', '>=', (int) $v))
                            ->when($data['w_max'] ?? null, fn ($q, $v) => $q->where('width_mm', '<=', (int) $v))
                            ->when($data['h_min'] ?? null, fn ($q, $v) => $q->where('height_mm', '>=', (int) $v))
                            ->when($data['h_max'] ?? null, fn ($q, $v) => $q->where('height_mm', '<=', (int) $v));
                    }),
            ], layout: FiltersLayout::AboveContent)
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                    DeleteAction::make(),
                ])
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
            'index' => ManageProductVariants::route('/'),
        ];
    }
}
