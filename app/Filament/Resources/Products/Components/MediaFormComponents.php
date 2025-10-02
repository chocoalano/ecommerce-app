<?php
namespace App\Filament\Resources\Products\Components;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;

class MediaFormComponents
{
    public static function form():array
    {
        return [
            FileUpload::make('url')
                ->label('File Media')
                ->helperText('Unggah file gambar/video. Sistem akan menyimpan path file.')
                ->directory('products/media')
                ->disk('public')
                ->visibility('public')
                ->preserveFilenames()
                ->required(),

            Select::make('type')
                ->label('Tipe Media')
                ->helperText('Pilih jenis media: gambar, video, atau 3d.')
                ->options([
                    'image' => 'image',
                    'video' => 'video',
                    '3d'    => '3d',
                ])
                ->native(false),

            TextInput::make('alt_text')
                ->label('Alt Text')
                ->helperText('Teks alternatif untuk SEO & aksesibilitas. Jelaskan isi gambar secara singkat.')
                ->maxLength(255),

            TextInput::make('sort_order')
                ->label('Urutan Tampil')
                ->helperText('Angka kecil tampil lebih dulu. Gunakan untuk mengatur urutan galeri.')
                ->numeric()
                ->default(0),

            Toggle::make('is_primary')
                ->label('Utama')
                ->helperText('Aktifkan untuk menjadikan media ini sebagai thumbnail utama.')
        ];
    }
}
