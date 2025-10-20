<?php

namespace App\Filament\Resources\Pages\Schemas;

use Filament\Forms\Components\Builder;
use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->label('Judul Halaman')
                    ->required()
                    ->helperText('Masukkan judul utama halaman. Judul ini akan muncul di navigasi.'),

                TextInput::make('slug')
                    ->label('Slug (URL)')
                    ->required()
                    ->helperText('Slug adalah bagian dari URL yang unik. Gunakan huruf kecil, tanpa spasi, dan pisahkan kata dengan tanda hubung (-)'),

                Section::make('Konten Halaman')
                    ->columnSpanFull()
                    ->relationship('pageContents')
                    ->schema([
                        Builder::make('content')
                        ->label('Konten halaman')
                        ->blocks([
                            Block::make('heading')
                                ->schema([
                                    TextInput::make('content')
                                        ->label('Heading')
                                        ->required(),
                                    Select::make('level')
                                        ->options([
                                            'h1' => 'Heading 1',
                                            'h2' => 'Heading 2',
                                            'h3' => 'Heading 3',
                                            'h4' => 'Heading 4',
                                            'h5' => 'Heading 5',
                                            'h6' => 'Heading 6',
                                        ])
                                        ->required(),
                                ])
                                ->columns(2),
                            Block::make('paragraph')
                                ->schema([
                                    Textarea::make('content')
                                        ->label('Paragraph')
                                        ->rows(5)
                                        ->required(),
                                ]),
                            Block::make('image')
                                ->schema([
                                    FileUpload::make('url')
                                        ->label('Image')
                                        ->acceptedFileTypes(['image/svg+xml'])
                                        ->disk('public')
                                        ->directory('images/pages')
                                        ->image()
                                        ->required(),
                                    TextInput::make('alt')
                                        ->label('Alt text')
                                        ->required(),
                                ]),
                            ]),
                        ]),

                Textarea::make('excerpt')
                    ->label('Ringkasan Singkat')
                    ->rows(5) // Tambahkan tinggi baris untuk Textarea
                    ->columnSpanFull()
                    ->helperText('Masukkan ringkasan atau deskripsi singkat (max 160 karakter). Ini sering digunakan untuk tampilan ringkasan atau SEO.'),

                Select::make('category')
                    ->label('Kategori')
                    ->options(['company' => 'Company', 'help' => 'Help', 'legal' => 'Legal', 'other' => 'Other'])
                    ->default('other')
                    ->required()
                    ->helperText('Pilih kategori untuk pengelompokan halaman ini.'),

                TextInput::make('sort_order')
                    ->label('Urutan Tampilan')
                    ->required()
                    ->numeric()
                    ->default(0)
                    ->helperText('Tentukan angka untuk urutan tampilan. Angka yang lebih kecil akan muncul lebih dahulu.'),

                Toggle::make('is_active')
                    ->label('Status Aktif')
                    ->required()
                    ->helperText('Nonaktifkan untuk menyembunyikan halaman ini dari publik. Aktifkan agar halaman dapat diakses.'),

                Toggle::make('show_in_footer')
                    ->label('Tampilkan di Footer')
                    ->required()
                    ->helperText('Aktifkan untuk menampilkan tautan halaman ini di bagian footer situs.'),

                TextInput::make('meta_title')
                    ->label('Meta Title (SEO)')
                    ->helperText('Judul khusus untuk mesin pencari (SEO). Jika dikosongkan, akan menggunakan Judul Halaman.'),

                Textarea::make('meta_description')
                    ->label('Meta Description (SEO)')
                    ->rows(3) // Tambahkan tinggi baris untuk Textarea
                    ->columnSpanFull()
                    ->helperText('Deskripsi singkat dan menarik (maks. 160 karakter) yang akan ditampilkan di hasil pencarian Google.'),
            ]);
    }
}
