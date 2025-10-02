<?php

namespace App\Filament\Resources\Wislists;

use App\Filament\Resources\Wislists\Pages\ManageWislists;
use App\Models\Wishlist;
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

class WislistResource extends Resource
{
    protected static ?string $model = Wishlist::class;

    protected static string | UnitEnum | null $navigationGroup = 'Produk Incaran';

    protected static ?string $recordTitleAttribute = 'Wislist';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('Wislist')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('Wislist'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('Wislist')
            ->columns([
                TextColumn::make('Wislist')
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
            'index' => ManageWislists::route('/'),
        ];
    }
}
