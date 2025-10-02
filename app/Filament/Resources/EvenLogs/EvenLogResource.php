<?php

namespace App\Filament\Resources\EvenLogs;

use App\Filament\Resources\EvenLogs\Pages\ManageEvenLogs;
use App\Models\EventLog;
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

class EvenLogResource extends Resource
{
    protected static ?string $model = EventLog::class;

    protected static string | UnitEnum | null $navigationGroup = 'Pengaturan';

    protected static ?string $recordTitleAttribute = 'EvenLog';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('EvenLog')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('EvenLog'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('EvenLog')
            ->columns([
                TextColumn::make('EvenLog')
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
            'index' => ManageEvenLogs::route('/'),
        ];
    }
}
