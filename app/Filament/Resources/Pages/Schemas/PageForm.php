<?php

namespace App\Filament\Resources\Pages\Schemas;

use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
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

                RichEditor::make('content')
                    ->label('Isi Konten')
                    ->fileAttachmentsAcceptedFileTypes(['image/png', 'image/jpeg'])
                    ->fileAttachmentsMaxSize(5120)
                    ->columnSpanFull()
                    ->helperText('Tulis konten utama halaman di sini. Anda dapat menggunakan format teks, daftar, dan menyisipkan gambar (maks. 5MB).')
                    ->extraInputAttributes(['style' => 'min-height: 50rem; max-height: 100vh; overflow-y: auto;']),

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
