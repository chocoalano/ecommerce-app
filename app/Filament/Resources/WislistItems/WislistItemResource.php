<?php

namespace App\Filament\Resources\WislistItems;

use App\Filament\Resources\WislistItems\Pages\ManageWislistItems;
use App\Models\WishlistItem;
use UnitEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class WislistItemResource extends Resource
{
    protected static ?string $model = WishlistItem::class;

    protected static string | UnitEnum | null $navigationGroup = 'Produk Incaran';

    protected static ?string $recordTitleAttribute = 'WislistItem';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('WislistItem')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('WislistItem'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('WislistItem')
            ->columns([
                TextColumn::make('WislistItem')
                    ->searchable(),
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
            'index' => ManageWislistItems::route('/'),
        ];
    }
}
