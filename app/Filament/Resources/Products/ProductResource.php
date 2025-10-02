<?php

namespace App\Filament\Resources\Products;

use App\Filament\Resources\Products\Components\ProductFormComponents;
use App\Filament\Resources\Products\Components\ProductInfolistComponents;
use App\Filament\Resources\Products\Pages\ManageProducts;
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
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static string|UnitEnum|null $navigationGroup = 'Produk';

    protected static ?string $recordTitleAttribute = 'Product';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components(ProductFormComponents::form());
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components(ProductInfolistComponents::infolist());
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('Product')
            ->columns([
                TextColumn::make('sku')
                    ->label('SKU')
                    ->searchable(),
                TextColumn::make('slug')
                    ->searchable(),
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('short_desc')
                    ->searchable(),
                TextColumn::make('brand')
                    ->searchable(),
                TextColumn::make('warranty_months')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('is_active')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
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
                        true: fn (Builder $q) => $q->where('is_active', true),
                        false: fn (Builder $q) => $q->where('is_active', false),
                        blank: fn (Builder $q) => $q
                    )
                    ->indicateUsing(fn ($state) => match ($state['value'] ?? null) {
                        true  => 'Aktif',
                        false => 'Nonaktif',
                        default => null,
                    }),

                // Brand (multi pilih dari data yang ada)
                SelectFilter::make('brand')
                    ->label('Brand')
                    ->multiple()
                    ->options(fn () => Product::query()
                        ->whereNotNull('brand')
                        ->distinct()
                        ->orderBy('brand')
                        ->pluck('brand', 'brand')
                        ->all()
                    )
                    ->indicator('Brand'),

                // Kategori (relasi many-to-many)
                SelectFilter::make('categories')
                    ->label('Kategori')
                    ->relationship('productCategories', 'name')
                    ->multiple()
                    ->searchable()
                    ->preload()
                    ->indicator('Kategori'),

                // Rentang garansi
                Filter::make('warranty_range')
                    ->label('Garansi (bulan)')
                    ->form([
                        TextInput::make('min')->label('Tgl. Min')
                            ->helperText('Tentukan batas minimum dan maksimum garansi dalam bulan.')
                            ->numeric(),
                        TextInput::make('max')->label('Tgl. Max')
                            ->helperText('Tentukan batas minimum dan maksimum garansi dalam bulan.')
                            ->numeric(),
                    ])
                    ->query(function (Builder $q, array $data) {
                        return $q
                            ->when($data['min'] ?? null, fn ($qq, $min) => $qq->where('warranty_months', '>=', (int) $min))
                            ->when($data['max'] ?? null, fn ($qq, $max) => $qq->where('warranty_months', '<=', (int) $max));
                    })
                    ->indicateUsing(function (array $data) {
                        $labels = [];
                        if (!empty($data['min'])) $labels[] = "Garansi ≥ {$data['min']} bln";
                        if (!empty($data['max'])) $labels[] = "Garansi ≤ {$data['max']} bln";
                        return $labels;
                    })->columns(2)->columnSpan(2),

                // Rentang tanggal dibuat
                Filter::make('created_between')
                    ->label('Dibuat (rentang)')
                    ->form([
                        DatePicker::make('from')->label('Dari'),
                        DatePicker::make('until')->label('Sampai'),
                    ])
                    ->query(function (Builder $q, array $data) {
                        $q->when($data['from'] ?? null, fn ($qq, $from) => $qq->whereDate('created_at', '>=', $from));
                        $q->when($data['until'] ?? null, fn ($qq, $until) => $qq->whereDate('created_at', '<=', $until));
                    })
                    ->indicateUsing(function (array $data) {
                        $labels = [];
                        if (!empty($data['from']))  $labels[] = 'Dari ' . \Carbon\Carbon::parse($data['from'])->format('d M Y');
                        if (!empty($data['until'])) $labels[] = 'Sampai ' . \Carbon\Carbon::parse($data['until'])->format('d M Y');
                        return $labels;
                    })->columns(2)->columnSpan(2),

                // Ada/Tidak ada media produk
                TernaryFilter::make('has_media')
                    ->label('Media')
                    ->placeholder('Semua')
                    ->trueLabel('Ada media')
                    ->falseLabel('Tidak ada media')
                    ->queries(
                        true: fn (Builder $q) => $q->whereHas('productMedia'),
                        false: fn (Builder $q) => $q->whereDoesntHave('productMedia'),
                        blank: fn (Builder $q) => $q
                    )
                    ->indicateUsing(fn ($state) => match ($state['value'] ?? null) {
                        true  => 'Dengan media',
                        false => 'Tanpa media',
                        default => null,
                    }),
            ], layout: FiltersLayout::AboveContent)
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make()->slideOver(),
                    EditAction::make()->slideOver(),
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
            'index' => ManageProducts::route('/'),
        ];
    }
}
