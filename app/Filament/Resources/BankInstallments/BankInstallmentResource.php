<?php

namespace App\Filament\Resources\BankInstallments;

use App\Filament\Resources\BankInstallments\Pages\ManageBankInstallments;
use App\Models\BankInstallment;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class BankInstallmentResource extends Resource
{
    protected static ?string $model = BankInstallment::class;
    protected static string | UnitEnum | null $navigationGroup = 'Promosi';
    protected static ?string $recordTitleAttribute = 'BankInstallment';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('promotion_id')
                    ->label('Kode Promo')
                    ->relationship('promotion', 'code')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->helperText('Pilih kode promo yang sudah terdaftar. Jika tidak ada, buat promo terlebih dahulu.'),

                TextInput::make('bank_code')
                    ->label('Kode Bank / Channel')
                    ->placeholder('Contoh: BCA / 014 / BNIVA')
                    ->required()
                    // Huruf kapital/angka/tanda hubung, 2–10 karakter, tanpa spasi
                    ->rule('regex:/^[A-Z0-9-]{2,10}$/')
                    ->helperText('Gunakan huruf kapital/angka tanpa spasi, 2–10 karakter. Contoh: BCA, 014, BNIVA.')
                    ->validationMessages([
                        'required' => 'Kode bank wajib diisi.',
                        'regex'    => 'Format kode bank tidak valid. Gunakan huruf kapital/angka tanpa spasi (2–10 karakter).',
                    ]),

                TextInput::make('tenor_months')
                    ->label('Tenor (bulan)')
                    ->placeholder('Contoh: 12')
                    ->required()
                    ->numeric()
                    ->minValue(1)
                    ->maxValue(60)
                    ->helperText('Lama cicilan dalam bulan. Isi angka antara 1 sampai 60.')
                    ->validationMessages([
                        'required' => 'Tenor wajib diisi.',
                        'numeric'  => 'Tenor harus berupa angka.',
                        'min'      => 'Tenor minimal 1 bulan.',
                        'max'      => 'Tenor maksimal 60 bulan.',
                    ]),

                TextInput::make('interest_rate_pa')
                    ->label('Bunga per Tahun (%)')
                    ->placeholder('Contoh: 12.5')
                    ->suffix('%')
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(100)
                    ->rule('decimal:0,2') // maksimal 2 desimal
                    ->helperText('Persentase bunga tahunan. Nilai 0–100, maksimal dua angka di belakang koma.')
                    ->validationMessages([
                        'numeric' => 'Bunga harus berupa angka.',
                        'min'     => 'Bunga minimal 0%.',
                        'max'     => 'Bunga maksimal 100%.',
                        'decimal' => 'Bunga maksimal dua angka di belakang koma.',
                    ]),

                TextInput::make('admin_fee')
                    ->label('Biaya Admin')
                    ->placeholder('Contoh: 10000')
                    ->prefix('Rp')
                    ->required()
                    ->numeric()
                    ->minValue(0)
                    ->rule('decimal:0,2')
                    ->default(0.0)
                    ->helperText('Biaya admin dalam rupiah. Isi 0 jika tidak ada. Boleh menggunakan dua angka desimal.')
                    ->validationMessages([
                        'required' => 'Biaya admin wajib diisi.',
                        'numeric'  => 'Biaya admin harus berupa angka.',
                        'min'      => 'Biaya admin tidak boleh negatif.',
                        'decimal'  => 'Biaya admin maksimal dua angka di belakang koma.',
                    ]),

                TextInput::make('min_spend')
                    ->label('Transaksi Minimum')
                    ->placeholder('Contoh: 250000')
                    ->prefix('Rp')
                    ->required()
                    ->numeric()
                    ->minValue(0)
                    ->rule('decimal:0,2')
                    ->default(0.0)
                    ->helperText('Nominal minimal transaksi agar promo berlaku. Boleh 0 jika tanpa batas minimal.')
                    ->validationMessages([
                        'required' => 'Transaksi minimum wajib diisi.',
                        'numeric'  => 'Transaksi minimum harus berupa angka.',
                        'min'      => 'Transaksi minimum tidak boleh negatif.',
                        'decimal'  => 'Transaksi minimum maksimal dua angka di belakang koma.',
                    ]),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('promotion_id')
                    ->numeric(),
                TextEntry::make('bank_code'),
                TextEntry::make('tenor_months')
                    ->numeric(),
                TextEntry::make('interest_rate_pa')
                    ->numeric(),
                TextEntry::make('admin_fee')
                    ->numeric(),
                TextEntry::make('min_spend')
                    ->numeric(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('BankInstallment')
            ->columns([
                TextColumn::make('promotion_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('bank_code')
                    ->searchable(),
                TextColumn::make('tenor_months')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('interest_rate_pa')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('admin_fee')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('min_spend')
                    ->numeric()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageBankInstallments::route('/'),
        ];
    }
}
