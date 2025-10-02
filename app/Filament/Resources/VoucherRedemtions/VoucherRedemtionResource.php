<?php

namespace App\Filament\Resources\VoucherRedemtions;

use App\Filament\Resources\VoucherRedemtions\Pages\ManageVoucherRedemtions;
use App\Models\VoucherRedemption;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;

class VoucherRedemtionResource extends Resource
{
    protected static ?string $model = VoucherRedemption::class;
    protected static string | UnitEnum | null $navigationGroup = 'Promosi';
    protected static ?string $recordTitleAttribute = 'VoucherRedemtion';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
            Select::make('voucher_id')
                ->label('Voucher')
                ->relationship('voucher', 'code')
                ->required()
                ->searchable()
                ->preload()
                ->helperText('Voucher yang ditebus. Wajib diisi dan terhubung ke tabel vouchers.'),

            Select::make('user_id')
                ->label('User (opsional)')
                ->relationship('user', 'name')
                ->searchable()
                ->preload()
                ->helperText('User yang menebus voucher. Bisa kosong jika guest / tidak ada user login.'),

            TextInput::make('order_id')
                ->label('Order ID (opsional)')
                ->numeric()
                ->helperText('ID pesanan terkait penebusan. Boleh kosong jika order belum tercatat.'),

            DateTimePicker::make('redeemed_at')
                ->label('Waktu Penebusan')
                ->default(now())
                ->seconds(false)
                ->required()
                ->helperText('Tanggal & jam voucher ditebus. Biasanya otomatis diisi saat create.'),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('id')->label('ID'),
                TextEntry::make('voucher.code')->label('Voucher'),
                TextEntry::make('user.name')->label('User')->placeholder('Guest'),
                TextEntry::make('order_id')->label('Order ID')->placeholder('-'),
                TextEntry::make('redeemed_at')->label('Redeemed At')->dateTime('Y-m-d H:i'),
                TextEntry::make('voucher.is_stackable')->label('Voucher Stackable?')
                    ->formatStateUsing(fn ($state) => $state ? 'Ya' : 'Tidak')
                    ->placeholder('-'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                TextColumn::make('voucher.code')
                    ->label('Voucher')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('user.name')
                    ->label('User')
                    ->toggleable()
                    ->searchable()
                    ->sortable(),

                TextColumn::make('order.order_no')
                    ->label('Pesanan')
                    ->toggleable()
                    ->sortable(),

                TextColumn::make('redeemed_at')
                    ->label('Redeemed At')
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),
            ])
            ->filters([
                Filter::make('tanggal')
                    ->form([
                        DateTimePicker::make('from')->label('Dari'),
                        DateTimePicker::make('until')->label('Sampai'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['from'] ?? null, fn ($q, $v) => $q->where('redeemed_at', '>=', $v))
                            ->when($data['until'] ?? null, fn ($q, $v) => $q->where('redeemed_at', '<=', $v));
                    }),
                SelectFilter::make('voucher_id')
                    ->label('Voucher')
                    ->relationship('voucher', 'code')
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                ViewAction::make(),
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
            'index' => ManageVoucherRedemtions::route('/'),
        ];
    }
}
