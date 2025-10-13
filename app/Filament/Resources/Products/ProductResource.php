<?php

namespace App\Filament\Resources\Products;

use App\Filament\Resources\Products\Pages\ManageProducts;
use App\Models\Product\Product;
use Filament\Actions\BulkAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TagsColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;
use UnitEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static string | UnitEnum | null $navigationGroup = 'Produk';
    public static function getNavigationBadge(): ?string
    {
        return (string) static::getModel()::query()->count();
    }
    public static function getGloballySearchableAttributes(): array
    {
        return ['sku','name','slug','brand','short_desc'];
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Identitas Produk')
                ->columns(2)
                ->schema([
                    TextInput::make('sku')
                        ->label('SKU')
                        ->required()
                        ->unique(table: 'products', column: 'sku', ignoreRecord: true)
                        ->maxLength(100)
                        ->helperText('Kode unik stok. Gunakan format konsisten (mis. BRND-KODE-001).'),

                    TextInput::make('name')
                        ->label('Nama')
                        ->required()
                        ->maxLength(255)
                        ->live(onBlur: true)
                        ->afterStateUpdated(function ($state, callable $set, callable $get) {
                            if (blank($get('slug'))) $set('slug', Str::slug($state ?? ''));
                        })
                        ->helperText('Nama yang tampil di kartu produk & halaman detail.'),

                    TextInput::make('slug')
                        ->label('Slug SEO')
                        ->maxLength(255)
                        ->unique(table: 'products', column: 'slug', ignoreRecord: true)
                        ->helperText('Unik di URL produk. Boleh disesuaikan untuk SEO.'),

                    TextInput::make('brand')
                        ->label('Brand')
                        ->maxLength(100)
                        ->helperText('Opsional. Kosongkan jika tidak ada brand.'),

                    TextInput::make('warranty_months')
                        ->label('Garansi (bulan)')
                        ->numeric()->minValue(0)
                        ->helperText('Durasi garansi dalam bulan, kosongkan jika tidak bergaransi.'),

                    Select::make('categories')
                        ->label('Kategori')
                        ->relationship('categories','name')
                        ->multiple()
                        ->preload()
                        ->searchable()
                        ->required()
                        ->helperText('Pilih minimal satu kategori agar produk mudah ditemukan.'),
                ])->columnSpanFull(),

            Section::make('Deskripsi')
                ->schema([
                    TextInput::make('short_desc')
                        ->label('Deskripsi Singkat')
                        ->maxLength(500)
                        ->helperText('Maks. 500 karakter. Muncul di listing & meta deskripsi.'),

                    Textarea::make('long_desc')
                        ->label('Deskripsi Panjang')
                        ->rows(6)
                        ->helperText('Konten detail produk. Anda bisa memasukkan HTML sederhana.'),
                ])->columnSpanFull(),

            Section::make('Harga & Stok')
                ->columns(3)
                ->schema([
                    TextInput::make('base_price')
                        ->label('Harga Dasar')
                        ->numeric()
                        ->minValue(0)
                        ->required()
                        ->helperText('Harga sebelum diskon. Gunakan tanpa pemisah ribuan.'),

                    Select::make('currency')
                        ->label('Mata Uang')
                        ->options([
                            'IDR' => 'IDR (Rupiah)',
                            'USD' => 'USD (Dollar)',
                        ])
                        ->required()
                        ->default('IDR')
                        ->helperText('Default IDR. Pastikan konsisten di seluruh katalog.'),

                    TextInput::make('stock')
                        ->label('Stok')
                        ->numeric()
                        ->minValue(0)
                        ->required()
                        ->helperText('Jumlah stok tersedia. Tidak boleh negatif.'),
                ])->columnSpanFull(),

            Section::make('Dimensi & Status')
                ->columns(4)
                ->schema([
                    TextInput::make('weight_gram')->label('Berat (gram)')->numeric()->minValue(0)
                        ->helperText('Opsional. Berguna untuk ongkir otomatis.'),
                    TextInput::make('length_mm')->label('Panjang (mm)')->numeric()->minValue(0),
                    TextInput::make('width_mm')->label('Lebar (mm)')->numeric()->minValue(0),
                    TextInput::make('height_mm')->label('Tinggi (mm)')->numeric()->minValue(0),

                    Toggle::make('is_active')
                        ->label('Aktif')
                        ->inline(false)
                        ->default(true)
                        ->columnSpanFull()
                        ->helperText('Nonaktifkan untuk menyembunyikan produk tanpa menghapus data.'),
                ])->columnSpanFull(),

            Section::make('Media')
                ->columns(2)
                ->schema([
                    Repeater::make('media')
                        ->label('Gambar Produk')
                        ->relationship('media')
                        ->schema([
                            FileUpload::make('url')
                                ->label('URL Media')
                                ->disk('public')
                                ->directory('images/products')
                                ->required()
                                ->helperText('Tempel URL gambar/video. Untuk file lokal, unggah ke storage lalu isi path URL-nya.')
                                ->columnSpanFull(),

                            TextInput::make('sort_order')
                                ->label('Urutan')
                                ->numeric()
                                ->minValue(0)
                                ->default(0)
                                ->helperText('Kecil = tampil lebih dulu.'),

                            Toggle::make('is_primary')
                                ->label('Media Utama?')
                                ->inline(false)
                                ->helperText('Hanya satu media utama per produk. Gunakan tombol "Jadikan Utama" bila perlu.'),
                        ])
                        ->columns(2)
                        ->defaultItems(1)
                        ->minItems(1)
                        ->maxItems(10)
                        ->columnSpanFull()
                        ->helperText('Anda dapat mengunggah hingga 10 gambar per produk.'),
                ])->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('media.url')
                    ->label('Gambar')
                    ->square()
                    ->stacked()
                    ->height(44),

                TextColumn::make('sku')->label('SKU')->sortable()->searchable(),
                TextColumn::make('name')->label('Nama')->limit(30)->sortable()->searchable(),
                TagsColumn::make('categories.name')->label('Kategori')->limit(2),

                TextColumn::make('base_price')->label('Harga')->money(fn ($record) => $record->currency ?? 'IDR')->sortable(),
                TextColumn::make('stock')->label('Stok')->sortable(),

                IconColumn::make('is_active')->label('Aktif')->boolean()->sortable(),
                TextColumn::make('created_at')->label('Dibuat')->dateTime('d M Y')->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('category')
                    ->label('Kategori')
                    ->relationship('categories','name'),

                TernaryFilter::make('is_active')
                    ->label('Status')
                    ->trueLabel('Aktif')->falseLabel('Nonaktif')->placeholder('Semua')
                    ->queries(
                        true: fn (Builder $q) => $q->where('is_active', true),
                        false: fn (Builder $q) => $q->where('is_active', false),
                        blank: fn (Builder $q) => $q
                    ),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
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
            'index' => ManageProducts::route('/'),
        ];
    }
}
