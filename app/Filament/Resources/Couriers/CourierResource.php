<?php

namespace App\Filament\Resources\Couriers;

use App\Filament\Resources\Couriers\Pages\ManageCouriers;
use App\Models\OrderProduct\Courier;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use UnitEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class CourierResource extends Resource
{
    protected static ?string $model = Courier::class;

    protected static string | UnitEnum | null $navigationGroup = 'Penjualan';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Dasar Kurir')
                    ->description('Detail unik dan status operasional kurir pengiriman.')
                    ->schema([
                        
                        // Field: code
                        TextInput::make('code')
                            ->label('Kode Kurir')
                            ->required()
                            ->maxLength(10)
                            ->unique(ignoreRecord: true) // Pastikan kode unik, kecuali saat mengedit data itu sendiri
                            ->columnSpan(1)
                            ->helperText('Gunakan kode yang singkat, unik, dan dikenal secara internal (contoh: JNE, POS, TIKI). Maksimal 10 karakter.'),

                        // Field: name
                        TextInput::make('name')
                            ->label('Nama Kurir')
                            ->required()
                            ->maxLength(50)
                            ->columnSpan(2)
                            ->helperText('Nama lengkap dari jasa kurir pengiriman (contoh: JNE Express, Pos Indonesia). Maksimal 50 karakter.'),

                        // Field: is_active
                        Toggle::make('is_active')
                            ->label('Status Aktif')
                            ->inline(false) // Toggle diletakkan di bawah label
                            ->default(true)
                            ->helperText('Aktifkan untuk menampilkan kurir ini sebagai opsi pengiriman yang tersedia untuk pengguna. Nonaktifkan jika layanan kurir ini sedang tidak beroperasi.'),
                    ])
                    ->columns(3)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // Kolom untuk Kode Kurir
                TextColumn::make('code')
                    ->label('Kode')
                    ->searchable()
                    ->sortable(),

                // Kolom untuk Nama Kurir
                TextColumn::make('name')
                    ->label('Nama Kurir')
                    ->searchable()
                    ->sortable(),

                // Kolom untuk Status Aktif (Boolean/Toggle)
                IconColumn::make('is_active')
                    ->label('Aktif')
                    // Menggunakan ikon atau badge
                    ->boolean()
                    ->sortable(),

                // Kolom Opsional: Tanggal Dibuat (meskipun model tidak menggunakan timestamps)
                // Jika Anda menambahkan timestamps di masa depan, kolom ini akan berguna.
                // TextColumn::make('created_at')
                //     ->label('Dibuat Pada')
                //     ->dateTime()
                //     ->sortable()
                //     ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                // Filter berdasarkan status aktif/nonaktif
                TernaryFilter::make('is_active')
                    ->label('Status Kurir')
                    ->trueLabel('Aktif')
                    ->falseLabel('Nonaktif')
                    ->indicator('Status: Aktif/Nonaktif'),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageCouriers::route('/'),
        ];
    }
}
