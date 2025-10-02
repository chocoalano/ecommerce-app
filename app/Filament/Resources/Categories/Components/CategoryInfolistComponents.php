<?php

namespace App\Filament\Resources\Categories\Components;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
class CategoryInfolistComponents // Nama kelas untuk mengelompokkan komponen infolist
{
    public static function infolist(): array // Atau bisa juga return Type 'array<Field>' jika menggunakan PHP 8+
    {
        return [
            Section::make('Detail Kategori')
                ->schema([
                    TextEntry::make('parent_id')
                        ->label('Parent ID')
                        ->helperText('ID kategori induk jika ini sub-kategori.'),

                    TextEntry::make('slug')
                        ->label('Slug')
                        ->copyable() // Menambahkan tombol salin untuk slug
                        ->copyMessage('Slug disalin!'),

                    TextEntry::make('name')
                        ->label('Nama Kategori'),

                    TextEntry::make('description')
                        ->label('Deskripsi')
                        ->columnSpanFull(),

                    TextEntry::make('sort_order')
                        ->label('Urutan Tampil'),

                    TextEntry::make('is_active')
                        ->label('Status')
                        ->badge() // Menampilkan status sebagai badge
                        ->color(fn ($state): string => match ($state) {
                            true => 'success',
                            false => 'danger',
                        })
                        ->formatStateUsing(fn ($state): string => $state ? 'Aktif' : 'Tidak Aktif'),
                ])
                ->columns(2), // Mengatur tampilan kolom untuk section ini
        ];
    }
}
