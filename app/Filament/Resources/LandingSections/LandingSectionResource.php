<?php

namespace App\Filament\Resources\LandingSections;

use App\Filament\Resources\LandingSections\Pages\ManageLandingSections;
use App\Models\LandingSection;
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

class LandingSectionResource extends Resource
{
    protected static ?string $model = LandingSection::class;

    protected static string|UnitEnum|null $navigationGroup = 'Konten Website';

    protected static ?string $recordTitleAttribute = 'LandingSection';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('landing_id')
                    ->required()
                    ->numeric(),
                TextInput::make('type')
                    ->required(),
                TextInput::make('content_json'),
                TextInput::make('sort_order')
                    ->required()
                    ->numeric()
                    ->default(0),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('landing_id')
                    ->numeric(),
                TextEntry::make('type'),
                TextEntry::make('sort_order')
                    ->numeric(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('LandingSection')
            ->columns([
                TextColumn::make('landing_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('type')
                    ->searchable(),
                TextColumn::make('sort_order')
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
            'index' => ManageLandingSections::route('/'),
        ];
    }
}
