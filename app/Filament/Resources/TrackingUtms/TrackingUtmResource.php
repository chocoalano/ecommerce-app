<?php

namespace App\Filament\Resources\TrackingUtms;

use App\Filament\Resources\TrackingUtms\Pages\ManageTrackingUtms;
use App\Models\TrackingUtm;
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

class TrackingUtmResource extends Resource
{
    protected static ?string $model = TrackingUtm::class;

    protected static string|UnitEnum|null $navigationGroup = 'Konten Website';

    protected static ?string $recordTitleAttribute = 'TrackingUtm';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('landing_id')
                    ->numeric(),
                TextInput::make('utm_source'),
                TextInput::make('utm_medium'),
                TextInput::make('utm_campaign'),
                TextInput::make('utm_content'),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('landing_id')
                    ->numeric(),
                TextEntry::make('utm_source'),
                TextEntry::make('utm_medium'),
                TextEntry::make('utm_campaign'),
                TextEntry::make('utm_content'),
                TextEntry::make('created_at')
                    ->dateTime(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('TrackingUtm')
            ->columns([
                TextColumn::make('landing_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('utm_source')
                    ->searchable(),
                TextColumn::make('utm_medium')
                    ->searchable(),
                TextColumn::make('utm_campaign')
                    ->searchable(),
                TextColumn::make('utm_content')
                    ->searchable(),
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
            'index' => ManageTrackingUtms::route('/'),
        ];
    }
}
