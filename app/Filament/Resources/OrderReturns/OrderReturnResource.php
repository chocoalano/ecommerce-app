<?php

namespace App\Filament\Resources\OrderReturns;

use App\Filament\Resources\OrderReturns\Pages\ManageOrderReturns;
use App\Models\OrderProduct\OrderReturn;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Schemas\Components\Section;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class OrderReturnResource extends Resource
{
    protected static ?string $model = OrderReturn::class;

    protected static string | UnitEnum | null $navigationGroup = 'Penjualan';

    public static function form(Schema $schema): Schema
    {
        $returnEnum = ['REQUESTED', 'APPROVED', 'REJECTED', 'RECEIVED', 'REFUNDED'];
        return $schema
            ->components([
                Section::make('Informasi Order & Retur')
                    ->description('Detail pesanan yang item-nya diajukan untuk proses retur.')
                    ->schema([
                        // Field: order_id (FK)
                        Select::make('order_id')
                            ->label('Nomor Order')
                            // Pastikan relasi 'order' didefinisikan di model Return
                            ->relationship(name: 'order', titleAttribute: 'order_no')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->helperText('Pilih order yang terkait. Hanya item dari order ini yang dapat diretur. Anda mungkin perlu membatasi pilihan pada order yang sudah Selesai/Diterima.'),

                        // Field: status (ENUM)
                        Select::make('status')
                            ->label('Status Pengajuan Retur')
                            ->options(array_combine($returnEnum, [
                                '1. Diajukan (REQUESTED)',
                                '2. Disetujui (APPROVED)',
                                '3. Ditolak (REJECTED)',
                                '4. Diterima Gudang (RECEIVED)',
                                '5. Dana Dikembalikan (REFUNDED)',
                            ]))
                            ->default('REQUESTED')
                            ->required()
                            ->helperText('Perbarui status retur. **RECEIVED** berarti barang fisik telah kembali ke gudang dan siap diverifikasi. **REFUNDED** berarti dana sudah dikembalikan.'),

                        // Field: reason (String)
                        TextInput::make('reason')
                            ->label('Alasan Retur Pelanggan')
                            ->maxLength(255)
                            ->nullable()
                            ->helperText('Alasan spesifik yang diajukan oleh pelanggan atau tim CS. (Contoh: Produk cacat, salah ukuran/warna).'),

                        // Field: requested_at (Timestamp)
                        DateTimePicker::make('requested_at')
                            ->label('Waktu Permintaan')
                            ->readOnly()
                            ->default(now())
                            ->visibleOn('edit')
                            ->helperText('Waktu tepat permintaan retur ini dicatat (biasanya diisi otomatis).'),
                            
                        // Field: processed_at (Timestamp)
                        DateTimePicker::make('processed_at')
                            ->label('Waktu Proses Selesai')
                            ->nullable()
                            ->helperText('Waktu ketika retur diubah statusnya menjadi **APPROVED**, **REJECTED**, atau **RECEIVED**. Biarkan kosong jika masih REQUESTED.'),
                    ])
                    ->columns(2)
                    ->columnSpanFull()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // Kolom untuk Nomor Order (dari relasi)
                TextColumn::make('order.order_no')
                    ->label('No. Order')
                    ->searchable(isIndividual: true, isGlobal: false)
                    ->sortable(),

                // Kolom untuk Status Retur
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'REQUESTED' => 'info',
                        'APPROVED' => 'warning',
                        'REJECTED' => 'danger',
                        'RECEIVED' => 'success',
                        'REFUNDED' => 'primary',
                        default => 'secondary',
                    })
                    ->searchable(isIndividual: true, isGlobal: false)
                    ->sortable(),

                // Kolom untuk Alasan Retur
                TextColumn::make('reason')
                    ->label('Alasan')
                    ->words(10) // Tampilkan ringkasan alasan
                    ->searchable(isIndividual: true, isGlobal: false)
                    ->limit(50),

                // Kolom untuk Waktu Pengajuan
                TextColumn::make('requested_at')
                    ->label('Diajukan Pada')
                    ->dateTime()
                    ->sortable()
                    ->since() // Tampilkan seberapa lama sejak diajukan
                    ->toggleable(isToggledHiddenByDefault: false),
                
                // Kolom untuk Waktu Proses
                TextColumn::make('processed_at')
                    ->label('Selesai Diproses')
                    ->dateTime()
                    ->sortable()
                    ->since()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                // Filter berdasarkan Status
                SelectFilter::make('status')
                    ->label('Filter Status')
                    ->options([
                        'REQUESTED' => 'Diajukan',
                        'APPROVED' => 'Disetujui',
                        'REJECTED' => 'Ditolak',
                        'RECEIVED' => 'Diterima Gudang',
                        'REFUNDED' => 'Dana Dikembalikan',
                    ]),

                // Filter berdasarkan rentang waktu pengajuan
                Filter::make('requested_at')
                    ->form([
                        DatePicker::make('requested_from')
                            ->label('Dari Tanggal Pengajuan'),
                        DatePicker::make('requested_until')
                            ->label('Sampai Tanggal Pengajuan'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['requested_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('requested_at', '>=', $date),
                            )
                            ->when(
                                $data['requested_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('requested_at', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                EditAction::make(),
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
            'index' => ManageOrderReturns::route('/'),
        ];
    }
}
