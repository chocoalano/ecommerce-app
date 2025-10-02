<?php

namespace App\Filament\Resources\Products\Components;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\KeyValueEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;

class ProductInfolistComponents
{
    public static function infolist(): array
    {
        return [
            // ================== INFORMASI UTAMA ==================
            Section::make('Informasi Utama')
                ->schema([
                    TextEntry::make('sku')
                        ->label('SKU')
                        ->helperText('Kode unik produk (Stock Keeping Unit).'),

                    TextEntry::make('slug')
                        ->label('Slug')
                        ->helperText('Slug unik untuk URL produk.'),

                    TextEntry::make('name')
                        ->label('Nama Produk'),

                    TextEntry::make('short_desc')
                        ->label('Deskripsi Singkat')
                        ->default('—'),

                    TextEntry::make('long_desc')
                        ->label('Deskripsi Panjang')
                        ->markdown()
                        ->helperText('Detail lengkap produk: fitur, material, cara pakai, dan info penting lain.')
                        ->columnSpanFull(),
                ])->columns(2)->columnSpanFull(),

            // ================== BRAND & GARANSI ==================
            Section::make('Brand & Garansi')
                ->schema([
                    TextEntry::make('brand')
                        ->label('Merek/Brand')
                        ->default('—'),

                    TextEntry::make('warranty_months')
                        ->label('Garansi (bulan)')
                        ->state(fn ($record) => $record->warranty_months ?? 'Tidak ada'),
                ])->columns(2)->columnSpanFull(),

            // ================== STATUS & WAKTU ==================
            Section::make('Status')
                ->schema([
                    IconEntry::make('is_active')
                        ->label('Aktif')
                        ->boolean()
                        ->trueIcon('heroicon-o-check-circle')
                        ->falseIcon('heroicon-o-x-circle')
                        ->helperText('Jika nonaktif, produk tidak tampil di katalog/toko.'),
                ])->columnSpanFull(),

            Section::make('Waktu')
                ->schema([
                    TextEntry::make('created_at')
                        ->label('Dibuat')
                        ->dateTime('d M Y H:i'),

                    TextEntry::make('updated_at')
                        ->label('Terakhir Diubah')
                        ->dateTime('d M Y H:i'),
                ])->columns(2)->columnSpanFull(),

            // ================== RELASI: KATEGORI ==================
            Section::make('Kategori Terkait')
                ->schema([
                    TextEntry::make('categories_list')
                        ->label('Kategori')
                        ->state(fn ($record) => $record->productCategories->pluck('name')->implode(', ') ?: '—')
                        ->helperText('Produk dapat berada pada beberapa kategori sekaligus. Gunakan kategori yang relevan.'),
                ])->columnSpanFull(),

            // ================== RELASI: MEDIA PRODUK ==================
            Section::make('Media Produk')
                ->schema([
                    // Galeri media utama (jika ada)
                    ImageEntry::make('media_gallery')
                        ->label('Galeri')
                        ->state(fn ($record) => $record->productMedia->pluck('url')->all())
                        ->disk('public') // sesuaikan disk Anda
                        ->helperText('Kumpulan gambar/video untuk produk. Media bertanda “Utama” digunakan sebagai thumbnail.')
                        ->visible(fn ($record) => $record->productMedia->isNotEmpty()),

                    // Daftar media dengan detail singkat
                    RepeatableEntry::make('media_list')
                        ->label('Detail Media')
                        ->state(function ($record) {
                            return collect($record->media ?? [])
                                ->map(fn ($m) => [
                                    'preview'    => $m->url,
                                    'type'       => $m->type ?? 'image',
                                    'alt_text'   => $m->alt_text ?? '',
                                    'sort_order' => $m->sort_order,
                                    'is_primary' => (bool) $m->is_primary,
                                    'created_at' => optional($m->created_at)?->format('d M Y H:i'),
                                ])
                                ->values()
                                ->all(); // RepeatableEntry butuh array, bukan Collection
                        })
                        ->schema([
                            ImageEntry::make('preview')
                                ->label('Preview')
                                ->disk('public'), // sesuaikan dengan disk Anda

                            TextEntry::make('type')
                                ->label('Tipe'),

                            TextEntry::make('alt_text')
                                ->label('Alt Text')
                                ->default('—')
                                ->columnSpanFull(),

                            TextEntry::make('sort_order')
                                ->label('Urutan'),

                            IconEntry::make('is_primary')
                                ->label('Utama')
                                ->boolean(),

                            TextEntry::make('created_at')
                                ->label('Dibuat'),
                        ])
                        ->columns(4)
                        ->visible(fn ($record) => collect($record->media ?? [])->isNotEmpty())
                ])->columnSpanFull(),

            // ================== RELASI: VARIASI PRODUK ==================
            Section::make('Variasi Produk')
                ->schema([
                    TextEntry::make('variants_summary')
                        ->label('Ringkasan')
                        ->state(fn ($record) => $record->productVariants->isEmpty()
                            ? 'Tidak ada variasi.'
                            : $record->productVariants->count().' variasi')
                        ->helperText('Variasi menyimpan SKU khusus, harga, atribut (misal warna/ukuran), dan media masing-masing.'),

                    RepeatableEntry::make('variants_list')
                        ->label('Daftar Variasi')
                        ->state(function ($record) {
                            return collect($record->productVariants ?? [])
                                ->map(function ($v) {
                                    return [
                                        'variant_sku'  => $v->variant_sku,
                                        'name'         => $v->name ?? '—',
                                        'base_price'   => $v->base_price,
                                        'currency'     => $v->currency ?? 'IDR',
                                        'is_active'    => (bool) $v->is_active,
                                        'weight_gram'  => $v->weight_gram,
                                        'length_mm'    => $v->length_mm,
                                        'width_mm'     => $v->width_mm,
                                        'height_mm'    => $v->height_mm,
                                        'attributes'   => (array) ($v->attributes_json ?? []),
                                        'media'        => collect($v->media ?? [])
                                            ->sortBy('sort_order')
                                            ->map(fn ($m) => [
                                                'url'        => $m->url,
                                                'type'       => $m->type ?? 'image',
                                                'is_primary' => (bool) $m->is_primary,
                                                'sort_order' => $m->sort_order,
                                                'alt_text'   => $m->alt_text ?? '',
                                            ])
                                            ->values()
                                            ->all(),
                                    ];
                                })
                                ->values()
                                ->all(); // RepeatableEntry butuh array
                        })
                        ->schema([
                            // ==== Identitas & Harga ====
                            TextEntry::make('variant_sku')
                                ->label('SKU Variasi')
                                ->helperText('SKU unik untuk variasi ini.'),

                            TextEntry::make('name')
                                ->label('Nama Variasi')
                                ->default('—'),

                            TextEntry::make('base_price')
                                ->label('Harga Dasar')
                                ->state(fn ($state) => is_null($state) ? '—' : 'Rp ' . number_format((float) $state, 0, ',', '.')),

                            TextEntry::make('currency')
                                ->label('Mata Uang')
                                ->default('IDR'),

                            IconEntry::make('is_active')
                                ->label('Aktif')
                                ->boolean(),
                            // Kolom jadi 5 untuk baris ini
                        ])
                        ->columns(5)
                        ->schema([
                            // ==== Dimensi & Berat ====
                            TextEntry::make('weight_gram')->label('Berat (g)')->default('—'),
                            TextEntry::make('length_mm')->label('Panjang (mm)')->default('—'),
                            TextEntry::make('width_mm')->label('Lebar (mm)')->default('—'),
                            TextEntry::make('height_mm')->label('Tinggi (mm)')->default('—'),
                        ])
                        ->columns(4)
                        ->schema([
                            // ==== Atribut Variasi ====
                            KeyValueEntry::make('attributes')
                                ->label('Atribut Variasi')
                                ->helperText('Pasangan kunci–nilai, misal: color=Black, size=L.')
                                ->default([])
                                ->columnSpanFull(),
                        ])
                        ->schema([
                            // ==== Media Variasi ====
                            RepeatableEntry::make('media')
                                ->label('Media Variasi')
                                ->helperText('Gambar/video khusus untuk variasi ini. Tandai salah satu sebagai “Utama”.')
                                ->schema([
                                    ImageEntry::make('url')->label('Preview')->disk('public'), // sesuaikan disk
                                    TextEntry::make('type')->label('Tipe'),
                                    TextEntry::make('alt_text')->label('Alt Text')->default('—')->columnSpanFull(),
                                    TextEntry::make('sort_order')->label('Urutan'),
                                    IconEntry::make('is_primary')->label('Utama')->boolean(),
                                ])
                                ->columns(4)
                                ->columnSpanFull(),
                        ])
                        ->columnSpanFull()
                        ->visible(fn ($record) => collect($record->variants ?? [])->isNotEmpty())
                ])->columnSpanFull(),
        ];
    }
}
