<?php

namespace App\Filament\Resources\Categories\Components;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;

class CategoryFormComponents // Mengganti nama class agar lebih deskriptif
{
    public static function form(): array
    {
        return [
            TextInput::make('parent_id')
                ->label('Parent ID')
                ->numeric()
                ->helperText('Opsional. Isi dengan ID kategori induk jika kategori ini merupakan sub-kategori (nested). Biarkan kosong jika ini kategori utama.'),

            TextInput::make('slug')
                ->label('Slug')
                ->required()
                ->maxLength(255)
                ->unique(ignoreRecord: true)
                ->helperText('Slug unik untuk URL kategori. Gunakan huruf kecil, angka, dan tanda minus (-). Contoh: aksesoris-komputer'),

            TextInput::make('name')
                ->label('Nama Kategori')
                ->required()
                ->maxLength(255)
                ->helperText('Nama kategori yang tampil ke pelanggan. Gunakan nama jelas dan mudah dipahami.'),

            TextInput::make('sort_order')
                ->label('Urutan Tampil')
                ->required()
                ->numeric()
                ->default(0)
                ->helperText('Angka untuk mengatur urutan tampil kategori. Angka kecil ditampilkan lebih dulu.'),

            Toggle::make('is_active')
                ->label('Aktif')
                ->required()
                ->default(true)
                ->helperText('Jika nonaktif, kategori tidak akan ditampilkan di katalog.'),

            Textarea::make('description')
                ->label('Deskripsi')
                ->columnSpanFull()
                ->helperText('Opsional. Tambahkan deskripsi detail tentang kategori ini (misal: jenis produk yang termasuk).'),

        ];
    }
}
