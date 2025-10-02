<?php

namespace App\Filament\Resources\Vouchers;

use App\Filament\Resources\Vouchers\Pages\ManageVouchers;
use App\Models\Voucher;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\CodeEditor;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class VoucherResource extends Resource
{
    protected static ?string $model = Voucher::class;
    protected static string | UnitEnum | null $navigationGroup = 'Promosi';
    protected static ?string $recordTitleAttribute = 'Voucher';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('promotion_id')
                        ->label('PIlih jenis promosi')
                        ->relationship('promotion', 'code')
                        ->searchable()->preload()
                        ->required()
                        ->helperText('Pilih kode promosi untuk vocer ini..'),
                TextInput::make('code')
                    ->required()->maxLength(100)
                    ->unique(ignoreRecord: true, table: 'vouchers', column: 'code')
                    ->helperText('Kode voucher unik. Jangan sama dengan promo code.'),
                Toggle::make('is_stackable')
                    ->inline(false)->default(false)
                    ->helperText('Jika aktif, voucher dapat ditumpuk dengan promo lain.'),
                DateTimePicker::make('start_at')
                    ->seconds(false)
                    ->helperText('Mulai berlaku (UTC di DB). Kosongkan untuk mengikuti periode promo.'),
                DateTimePicker::make('end_at')
                    ->seconds(false)
                    ->after('start_at')
                    ->helperText('Berakhir. Kosongkan untuk mengikuti periode promo.'),
                TextInput::make('max_redemption')
                    ->numeric()->minValue(0)
                    ->helperText('Kuota semua user. Kosongkan bila tanpa batas.'),
                TextInput::make('per_user_limit')
                    ->numeric()->minValue(0)
                    ->helperText('Batas per user. Kosongkan bila tanpa batas.'),
                CodeEditor::make('conditions_json')
                    ->columnSpanFull()
                    ->helperText('JSON syarat granular (mis. min_spend khusus voucher).'),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('promotion.code'),
                TextEntry::make('code'),
                IconEntry::make('is_stackable')
                    ->boolean(),
                TextEntry::make('start_at')
                    ->dateTime(),
                TextEntry::make('end_at')
                    ->dateTime(),
                TextEntry::make('max_redemption')
                    ->numeric(),
                TextEntry::make('per_user_limit')
                    ->numeric(),
                TextEntry::make('created_at')
                    ->dateTime(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('Voucher')
            ->columns([
                TextColumn::make('promotion.code'),
                TextColumn::make('code')
                    ->searchable(),
                IconColumn::make('is_stackable')
                    ->boolean(),
                TextColumn::make('start_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('end_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('max_redemption')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('per_user_limit')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
            'index' => ManageVouchers::route('/'),
        ];
    }
}
