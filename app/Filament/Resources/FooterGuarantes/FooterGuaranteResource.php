<?php

namespace App\Filament\Resources\FooterGuarantes;

use App\Filament\Resources\FooterGuarantes\Pages\ManageFooterGuarantes;
use App\Models\FooterGuarantee;
use Filament\Forms\Components\FileUpload;
use Filament\Tables\Columns\ToggleColumn;
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

class FooterGuaranteResource extends Resource
{
    protected static ?string $model = FooterGuarantee::class;

    protected static string | UnitEnum | null $navigationGroup = 'Pengaturan Website';

    protected static ?string $recordTitleAttribute = 'label';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('label')
                    ->label('Judul')
                    ->required()
                    ->maxLength(255),
                FileUpload::make('icon')
                    ->label('Icon (SVG)')
                    ->acceptedFileTypes(['image/svg+xml'])
                    ->disk('public')
                    ->directory('images/icons')
                    ->required(),
                TextInput::make('order')
                    ->label('Urutan')
                    ->numeric()
                    ->default(0),
                TextInput::make('is_active')
                    ->label('Aktif')
                    ->default(true),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('label')->label('Judul'),
                TextEntry::make('icon')->label('Icon'),
                TextEntry::make('order')->label('Urutan'),
                TextEntry::make('is_active')->label('Aktif'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('label')
            ->columns([
                TextColumn::make('label')->label('Judul')->searchable(),
                TextColumn::make('icon')->label('Icon'),
                TextColumn::make('order')->label('Urutan'),
                ToggleColumn::make('is_active')->label('Aktif'),
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
            'index' => ManageFooterGuarantes::route('/'),
        ];
    }
}
