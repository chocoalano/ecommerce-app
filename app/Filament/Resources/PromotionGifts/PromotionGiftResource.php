<?php

namespace App\Filament\Resources\PromotionGifts;

use App\Filament\Resources\PromotionGifts\Pages\ManagePromotionGifts;
use App\Models\PromotionGift;
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
class PromotionGiftResource extends Resource
{
    protected static ?string $model = PromotionGift::class;
    protected static string | UnitEnum | null $navigationGroup = 'Promosi';
    protected static ?string $recordTitleAttribute = 'PromotionGift';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('promotion_id')
                    ->label('Pilih promosi')
                    ->relationship('promotion', 'code')
                    ->searchable()->preload()
                    ->required()
                    ->helperText('Pilih promosi yang akan mendapatkan hadiah ini.'),
                Select::make('gift_variant_id')
                    ->label('Variant Hadiah')
                    ->relationship('giftVariant', 'variant_sku')
                    ->searchable()->preload()
                    ->required()
                    ->helperText('Pilih variant produk yang akan diberikan sebagai hadiah.'),
                TextInput::make('min_spend')
                    ->numeric()->step('0.01')->minValue(0)
                    ->default(0)
                    ->required()
                    ->helperText('Minimal belanja agar hadiah aktif.'),
                TextInput::make('min_qty')
                    ->numeric()->minValue(0)->default(0)
                    ->helperText('Opsional: minimal qty item tertentu bila diperlukan.'),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('promotion_id')
                    ->numeric(),
                TextEntry::make('gift_variant_id')
                    ->numeric(),
                TextEntry::make('min_spend')
                    ->numeric(),
                TextEntry::make('min_qty')
                    ->numeric(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('PromotionGift')
            ->columns([
                TextColumn::make('promotion_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('gift_variant_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('min_spend')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('min_qty')
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
            'index' => ManagePromotionGifts::route('/'),
        ];
    }
}
