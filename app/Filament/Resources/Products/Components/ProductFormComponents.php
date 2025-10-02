<?php
namespace App\Filament\Resources\Products\Components;

use App\Filament\Resources\Categories\Components\CategoryFormComponents;
use App\Filament\Resources\ProductVariants\Components\VariantFormComponents;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;

class ProductFormComponents{
    public static function form():array
    {
        return [
            Tabs::make('Produk')
                ->tabs([
                    Tabs\Tab::make('Informasi Utama')
                        ->schema([
                            Section::make('Identitas Produk')
                                ->schema([
                                    TextInput::make('sku')
                                        ->label('SKU')
                                        ->helperText('Kode unik produk untuk stok dan pelacakan. Harus unik dan mudah diingat.')
                                        ->required()
                                        ->maxLength(100)
                                        ->unique(ignoreRecord: true),

                                    TextInput::make('slug')
                                        ->label('Slug')
                                        ->helperText('Digunakan untuk URL produk. Hanya huruf kecil, angka, dan tanda minus (-). Harus unik.')
                                        ->required()
                                        ->maxLength(255)
                                        ->unique(ignoreRecord: true),

                                    TextInput::make('name')
                                        ->label('Nama Produk')
                                        ->helperText('Nama yang tampil ke pelanggan. Gunakan penamaan yang jelas & konsisten.')
                                        ->required()
                                        ->maxLength(255),

                                    Textarea::make('short_desc')
                                        ->label('Deskripsi Singkat')
                                        ->helperText('Ringkasan 1–2 kalimat tentang produk. Ditampilkan di listing/preview.')
                                        ->rows(3)
                                        ->maxLength(500),

                                    RichEditor::make('long_desc')
                                        ->label('Deskripsi Panjang')
                                        ->helperText('Detail lengkap produk: fitur, material, cara pakai, dan info penting lain.')
                                        ->columnSpanFull(),
                                ])->columns(2),

                            Section::make('Brand & Garansi')
                                ->schema([
                                    TextInput::make('brand')
                                        ->label('Merek/Brand')
                                        ->helperText('Opsional. Kosongkan jika tidak ada brand khusus.')
                                        ->maxLength(100),

                                    TextInput::make('warranty_months')
                                        ->label('Garansi (bulan)')
                                        ->helperText('Isi jumlah bulan garansi. Kosongkan jika tidak ada garansi.')
                                        ->numeric()
                                        ->minValue(0),
                                ])->columns(2),

                            Section::make('Status')
                                ->schema([
                                    Toggle::make('is_active')
                                        ->label('Aktif')
                                        ->helperText('Jika non-aktif, produk tidak akan tampil di katalog/toko.')
                                        ->default(true),
                                ]),
                        ]),

                    Tabs\Tab::make('Kategori')
                        ->schema([
                            Section::make('Kategori Produk')
                                ->schema([
                                    Select::make('categories')
                                        ->label('Kategori')
                                        ->helperText('Pilih satu atau lebih kategori yang relevan. Gunakan pencarian untuk mempercepat.')
                                        ->relationship('productCategories', 'name')
                                        ->searchable()
                                        ->preload()
                                        ->multiple()
                                        ->createOptionForm(CategoryFormComponents::form())
                                        ->editOptionForm(CategoryFormComponents::form())
                                        ->columnSpanFull(),
                                ]),
                        ]),

                    Tabs\Tab::make('Media')
                        ->schema([
                            Section::make('Gambar / Video')
                                ->schema([
                                    Repeater::make('productMedia')
                                        ->label('Media Produk')
                                        ->helperText('Unggah gambar/video utama produk. Tandai salah satu sebagai “utama” untuk thumbnail.')
                                        ->relationship('productMedia')
                                        ->reorderableWithButtons()
                                        ->orderColumn('sort_order')
                                        ->schema(MediaFormComponents::form())
                                        ->columns(2)
                                        ->collapsed(),
                                ]),
                        ]),

                    Tabs\Tab::make('Variasi')
                        ->schema([
                            Repeater::make('productVariants')
                                ->label('Daftar Variasi')
                                ->helperText('Tambahkan variasi (misal: warna/ukuran) lengkap dengan harga, atribut, dimensi, dan media khusus.')
                                ->relationship('productVariants') // rel: product_variants
                                ->itemLabel(fn ($state) => $state['variant_sku'] ?? 'Variasi baru')
                                ->reorderable(false) // tak ada kolom sort di tabel variasi
                                ->defaultItems(0)
                                ->collapsible()
                                ->schema(VariantFormComponents::form())
                                ->columns(1) // setiap variasi ditata vertikal
                                ->columnSpanFull(),
                        ]),
                ])->columnSpanFull()
            ];
    }
}
