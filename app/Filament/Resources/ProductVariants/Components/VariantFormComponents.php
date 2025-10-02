<?php
namespace App\Filament\Resources\ProductVariants\Components;

use App\Filament\Resources\Products\Components\MediaFormComponents;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;

class VariantFormComponents{
    public static function form():array
    {
        return [
            Section::make('Identitas Variasi')
                ->schema([
                    TextInput::make('variant_sku')
                        ->label('SKU Variasi')
                        ->helperText('SKU unik untuk variasi ini. Berbeda dari SKU produk utama.')
                        ->required()
                        ->maxLength(120)
                        ->unique(ignoreRecord: true),

                    TextInput::make('name')
                        ->label('Nama Variasi')
                        ->helperText('Contoh: Warna – Hitam, Ukuran – L. Opsional namun memudahkan penamaan di listing.')
                        ->maxLength(255),

                    KeyValue::make('attributes_json')
                        ->label('Atribut Variasi')
                        ->helperText('Simpan pasangan kunci–nilai, misal: color=Black, size=L.')
                        ->keyLabel('Atribut')
                        ->valueLabel('Nilai')
                        ->addButtonLabel('Tambah Atribut')
                        ->reorderable()
                        ->columnSpanFull(),
                ])->columns(2)->columnSpanFull(),

            Section::make('Harga & Mata Uang')
                ->schema([
                    TextInput::make('base_price')
                        ->label('Harga Dasar')
                        ->helperText('Harga dasar sebelum diskon/promo. Gunakan angka saja.')
                        ->numeric()
                        ->required()
                        ->prefix('Rp'),

                    TextInput::make('currency')
                        ->label('Mata Uang')
                        ->helperText('Kode mata uang 3 huruf (misal: IDR).')
                        ->default('IDR')
                        ->maxLength(3),
                ])->columns(2)->columnSpanFull(),

            Section::make('Dimensi & Berat')
                ->schema([
                    TextInput::make('weight_gram')
                        ->label('Berat (gram)')
                        ->helperText('Berat bersih produk dalam gram. Berguna untuk perhitungan ongkir.')
                        ->numeric()
                        ->minValue(0),

                    TextInput::make('length_mm')
                        ->label('Panjang (mm)')
                        ->helperText('Panjang produk/kemasan dalam milimeter.')
                        ->numeric()
                        ->minValue(0),

                    TextInput::make('width_mm')
                        ->label('Lebar (mm)')
                        ->helperText('Lebar produk/kemasan dalam milimeter.')
                        ->numeric()
                        ->minValue(0),

                    TextInput::make('height_mm')
                        ->label('Tinggi (mm)')
                        ->helperText('Tinggi produk/kemasan dalam milimeter.')
                        ->numeric()
                        ->minValue(0),
                ])->columns(4)->columnSpanFull(),

            Section::make('Status')
                ->schema([
                    Toggle::make('is_active')
                        ->label('Aktif')
                        ->helperText('Nonaktifkan jika variasi tidak dijual sementara.')
                        ->default(true),
                ])->columnSpanFull(),

            Section::make('Media Variasi')
                ->schema([
                    Repeater::make('media')
                        ->relationship('media') // rel: product_variant_media
                        ->label('Media Variasi')
                        ->helperText('Unggah gambar/video khusus untuk variasi ini (misalnya beda warna). Tandai salah satu sebagai “Utama”.')
                        ->reorderableWithButtons()
                        ->orderColumn('sort_order')
                        ->defaultItems(0)
                        ->schema(MediaFormComponents::form())
                        ->columns(2)
                        ->collapsed(),
                ])->columnSpanFull()
        ];
    }
}
